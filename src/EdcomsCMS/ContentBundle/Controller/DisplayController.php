<?php

namespace EdcomsCMS\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Doctrine\Common\Collections\Criteria;
use EdcomsCMS\ContentBundle\Entity\Structure;
use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\CustomFields;
use EdcomsCMS\ContentBundle\Entity\ActivityLog;
use EdcomsCMS\ContentBundle\Helpers\ContentHelper;
use EdcomsCMS\ContentBundle\Helpers\Navigation;
use EdcomsCMS\ContentBundle\Helpers\RatingHelper;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DisplayController extends Controller
{
    /*
     * Stock list - can be overridden in the main site AppBundle Model via service SpecialURLs
     */
    private $specials = [
        'myaccount' => ['class' => 'AppBundle\Controller\UserController', 'method' => 'MyAccount']
    ];
    private $content;
    private $contentarr;
    private $contentHelper;
    /**
     *
     * @var \EdcomsCMS\ContentBundle\Helpers\FilterOptionsHelper
     */
    private $filterOptionsHelper;
    private $filterOptions;
    public $allContent;
    private $section;
    private $path = [];
    private $rawpath;
    private $repositories = [];
    private $status = 'published';
    public $defaultLang = 'en'; // default language \\
    public $langName = '';
    public $lang = 'en'; // default language \\
    private $Navigation;
    
    public function getTargetStatus()
    {
        return $this->status;
    }
    public function setTargetStatus($status)
    {
        $this->status = $status;
        return $this;
    }
    
    public function homeAction(Request $request)
    {
        $structuresRepository = $this->getRepository('EdcomsCMSContentBundle:Structure');
        $structure = $structuresRepository->findBy([], ['id' => 'ASC'], 1)[0];
        return $this->indexAction($structure->getLink(), $request);
    }
    
    /**
     * Fetches the content for the page with '$path'.
     * Returns JSON string if 'json' has been declared in the GET parameters.
     * Supported GET parameters:
     *   'components' - returns each component (where the value string is separated by ',').
     *   'json' - function returns all data as a JSON string.
     *   'limit=(int)' - limits the fetched results by the amount.
     *   'page=(int)' - paginates the results. An offset is calulcated by this value * the limit value.
     * @param   string      $path       Path of the page to display.
     * @param   Request     $request    The object detailing the calling request.
     * @return  mixed                   Either JSON string of the data requested or renders a twig template of the found page.
     * @throws  NotFoundHttpException
     */
    public function indexAction($path, Request $request)
    {

        // Check if URL Redirect exist for the given path and redirect
        $urlRedirectService = $this->get('edcoms.content.service.url_redirect');
        $urlRedirect = $urlRedirectService->redirectExist($path);
        if($urlRedirect){
            $redirectPath = $urlRedirect->getRedirectPath();
            // check to avoid infinite redirect loop
            if($redirectPath!=$path){
                if($urlRedirect->getTrackUsage()){
                    $urlRedirectService->trackURLRedirect($urlRedirect, $request);
                }
                return $this->redirect($redirectPath, $urlRedirect->getRedirectStatusCode());
            }
        }


        // data to be sent to the view.
        $viewData = [];
                
        // set up GET parameters and booleans to determine specific components to sent to the view.
        $requestQuery = $request->query;
        $getParameters = $requestQuery->all();
        $viewDataAsJSON = array_key_exists('json', $getParameters);
        $requiredComponents = array_key_exists('components', $getParameters) ? explode(',', $getParameters['components']) : null;
        $downloadFormat = null;

        //check for nav setting in site config, setting off by default
//        $cmsConfiguration = $this->container->getParameter('connect_cms');
//        $nav = !is_null($cmsConfiguration['nav'])? $cmsConfiguration['nav']: false;

        $nav = false;
        // available components below represented as the keys in '$components'.
        // if the 'components' GET parameter is not set, we set everything by default.
        $components = [
            'breadcrumb' => true,
            'children' => true,
            'content' => true,
            'filter_options' => true,
            'nav' => $nav,
            'pagination_info' => true,
            'section' => true,
            'specials' => true,
            'subnav' => true,
            'ugc' => true,
            'user' => true,
            'rating' => true
        ];
        
        if ($requiredComponents !== null) {
            // iterate through the 'display '$components' array and set the value as 'true' if declared in the 'components' GET parameter.
            $componentsNames = array_keys($components);
            foreach ($componentsNames as $componentName) {
                $components[$componentName] = in_array($componentName, $requiredComponents);
            }
        }
                
        // set up navigation.
        $this->Navigation = new Navigation(
            $this,
            $this->getRepository('EdcomsCMSContentBundle:Content'),
            $this->getRepository('EdcomsCMSContentBundle:Structure')
        );

        // get the session \\
        $session = $request->getSession();
        
        // see if the download_file has been set \\
        $download_requested = $session->get('download_file');
        $authorizationChecker = $this->container->get('security.authorization_checker');
        if (!is_null($download_requested) && !$authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') && !$authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            // if it is set but the user isn't logged in then clear the request \\
            $session->remove('download_file');
        }
        
        // set the $uri object, but also detect if user is requesting a ZIP download.
        $pathObj = explode('/', $path);
        $pathObjCount = count($pathObj);
        
        // check if see if URI is requesting an archive download.
        // remove last two paths if it is a valid download URI path.
        if ($pathObjCount > 2 && $pathObj[$pathObjCount - 2] === 'download') {
            $downloadFormat = $pathObj[$pathObjCount - 1];
            array_splice($pathObj, $pathObjCount - 2, 2);
        }
        
        $this->rawpath = $pathObj;

        $this->DetectLanguage();
        $structure = $this->createStructure($request);
        if ($structure !== true) {
            return $structure;
        }
        
        // get the URI.
        $uri = implode('/', $this->rawpath);
        
        // RW - refactored the setting of filter parameters into the FilterOptions methods
        
        // set up FilterOptions and store in controller to be used later.
        $filterOptionsHelper = $this->get('EdcomsCMSFilterOptions');
        $filterOptionsHelper->setRequest($request);
        $this->filterOptionsHelper = $filterOptionsHelper;
        $this->filterOptions = $filterOptionsHelper->getFilterOptions($uri, end($this->path)->getId());
        
        // need to send the request to see if we searched for anything specific \\
        
        
        // should filter return sub children - default false \\
        $subChildren = $requestQuery->get('sub_children', false);
        
        /* Change by RW to use an initContent method */
        $this->initContent($request, $paginationInfo);
        
        
        $rateable = end($this->path)->getRateable();

        // check permissions here \\
        // need to search the array collection for all objects called permission \\
        $perms = $this->content->getCustomFieldData()->filter(function ($custom_field) {
            return ($custom_field->getCustomFields()->getName() === 'permission');
        });
        $authenticated = true;
        if (count($perms) > 0) {
            $authenticated = false;
            // need to loop through the permissions and see if its a group or a checkbox and if the current user has permission \\
            foreach ($perms as $perm) {
                if ($perm->getValue() === '1') {
                    // just has to be authenticated \\
                    if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') || $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
                        $authenticated = true;
                    }
                } else {
                    $authenticated = true;
                }
            }
        }
        
        if ($authenticated) {
            if ($this->content->getContentType()->getShowChildren()) {
                // fetching content relies on class variables being set by this next action:
                $children = $this->content->getStructure()->getChildren();
                $childrenEntity = $this->contentHelper->displayChildren($children, $getParameters, $paginationInfo, $this->filterOptions, $this->getTargetStatus(), $subChildren);
                
                if ($components['children']) {
                    $viewData['children'] = $childrenEntity;
                }
                
                if ($components['pagination_info']) {
                    $viewData['pagination_info'] = $paginationInfo;
                }
            }

            if ($downloadFormat === null && $this->content->getTemplateFile()
                && $this->content->getTemplateFile()->getTemplateFile() !== 'download') {
                // see if we have an associated form \\
                $ugc = $this->get('EdcomsCMSUGC');
                $ugc->loadForm($this->content, $this->get('kernel')->getRootDir(), $this->getUser());
                $ugcInfo = $ugc->getInfo();
                if ($request->isMethod('POST')) {
                    $formValidator = false;

                    // retrieve the custom form validation definition if both the FormValidators model and the definition itself exists.
                    if ($this->container->has('FormValidators')) {
                        $formValidatorError = null;
                        $formValidators = $this->container->get('FormValidators');
                        $formValidator = $formValidators->get($uri);
                    }

                    $formResp = $ugc->handleForm($request, $this->getDoctrine()->getManager('edcoms_cms'), $formValidator);
                    $ugcInfo['ugc_response'] = $formResp;
                    $ugcInfo['ugc_data'] = $request->request->all();

                    // set the response in the request so that other objects can detect that the form has been handled.
                    $request->request->set('ugc_response', $formResp);
                }

                // see if there is an entryID on the content, if so, get the associated UGC \\
                $entryIDs = $this->content->getCustomFieldData()->filter(function ($customField) {
                    return ($customField->getCustomFields()->getName() === 'entryID');
                });
                if ($entryIDs->count() > 0) {
                    $entryID = $entryIDs->first()->getValue();
                    $entryData = $ugc->loadData($entryID);
                    $spirit = null;
                    $entry = $entryData->toJSON(['title', 'user']);
                    if ($this->container->has('SPIRITRegistration')) {
                        $spirit = $this->get('SPIRITRegistration');
                    }
                    if (!is_null($spirit)) {
                        $spiritID = $entryData->getUser()->getPerson()->getContacts()->filter(function ($type) {
                            return ($type->getType() === 'spirit_id');
                        });
                        if (count($spiritID) === 1 && $spiritID->first()->getValue() !== '0') {
                            $spiritUser = $spirit->getRegisteredUser($spiritID->first()->getValue());
                            if ($spiritUser) {
                                $entry['user']['spirit'] = $spiritUser;
                            }
                        } else {
                            $spiritEstID = $entryData->getUser()->getPerson()->getContacts()->filter(function ($type) {
                                return ($type->getType() === 'educationNumber');
                            });
                            if (count($spiritEstID) === 1) {
                                $spiritEst = $spirit->getRegisteredOrganisation($spiritEstID->first()->getValue());
                                if ($spiritEst) {
                                    $entry['user']['spirit'] = $spiritEst;
                                }
                            }
                        }
                    } else if ($this->container->has('RTRRegistration')) {
                        $rtr = $this->get('RTRRegistration');
                        if (!is_null($rtr)) {
                            $edcoID = $entryData->getUser()->getPerson()->getContacts()->filter(function ($type) {
                                return ($type->getType() === 'educationNumber');
                            });
                            if (count($edcoID) === 1 && $edcoID->first()->getValue() !== '0') {
                                $establishment = $rtr->getEstablishmentDetails($edcoID->first()->getValue());
                                if (!is_null($establishment)) {
                                    $entry['user']['spirit'] = $establishment;
                                }
                            }
                        }
                    }
                    $ugcInfo = ['ugc_data' => $entryData->getUserGeneratedContentValues(true), 'ugc_entry' => $entry];
                }
                
                // check if authenticated, return the user object \\
                $user = null;
                $userObj = $this->get('security.token_storage')->getToken()->getUser();

                /** @TODO handle notifications for CMS Ecco version*/
//                if (is_object($userObj)) {
//                    $user = $userObj->toJSON();
//                    //add unread notifications to the user array if any
//                    $notificationHelper = $this->get('NotificationHelper');
//                    $user['notifications']['unreadCount'] = $notificationHelper->getUnreadCountByUser($userObj);
//                }

                $specials = [];
                // we have some key pages that need to trigger work from the site itself \\
                $specialURL = false;
                if ($this->container->has('SpecialURLs')) {
                    $specialURLs = $this->get('SpecialURLs');
                    $specialURL = $specialURLs->get($uri);
                    if (!$specialURL && isset($this->specials[$uri])) {
                        $specialURL = $this->specials[$uri];
                    }
                } else if (isset($this->specials[$uri])) {
                    $specialURL = $this->specials[$uri];
                }
                if ($specialURL) {
                    // currently on a special route, find it in the cusotm methods \\
                    if (isset($specialURL['class']) && class_exists($specialURL['class']) && method_exists($specialURL['class'], $specialURL['method'])) {
                        $userController = new $specialURL['class']();
                        $userController->setContainer($this->container);
                        $specialsKey = (!isset($specialURL['variable'])) ? str_replace('/', '_', str_replace('-', '_', $uri)) : $specialURL['variable'];
                        $specials[$specialsKey] = $userController->{$specialURL['method']}($request, $userObj);
                        if ($specials[$specialsKey] instanceof RedirectResponse) {
                            // return the redirect \\
                            return $specials[$specialsKey];
                        }
                    }
                }

                // add extra data to special content types
                $specialContentType = false;
                if ($this->container->has('SpecialContentTypes')) {
                    //get content type
                    $type = $this->content->getContentType()->getName();
                    //get the special content types
                    $specialContentTypes = $this->get('SpecialContentTypes');
                    $specialContentType = $specialContentTypes->get($type);
                }
                //If this content type is special
                if ($specialContentType) {
                    //Get it's injected function and special variable
                    if (isset($specialContentType['class']) && class_exists($specialContentType['class']) && method_exists($specialContentType['class'], $specialContentType['method'])) {
                        $userController = new $specialContentType['class']();
                        $userController->setContainer($this->container);
                        //If special variable not set create from content type name
                        $specialsKey = (!isset($specialContentType['variable'])) ? str_replace(' ', '_', str_replace('-', '_', $type)) : $specialContentType['variable'];
                        $result = $userController->{$specialContentType['method']}($request, $this->content, $userObj);
                        if ($result instanceof RedirectResponse) {
                            // return the redirect \\
                            return $result;
                        }
                        if (isset($specials[$specialsKey])) {
                            //special variable already used
                            $specials['special_content_type_'.$specialsKey] = $result;
                        } else {
                            $specials[$specialsKey] = $result;
                        }
                    }
                }

                $subNav = null;
                // if set as a custom field, need to find the level for the subnav \\
                $subNavInfo = $this->content->getCustomFieldData()->filter(function ($custom_field) {
                    return ($custom_field->getCustomFields()->getName() === 'sub_nav');
                });
                
                if (count($subNavInfo) === 1) {
                    $subNavInfo = $subNavInfo->first()->getValue();
                    $subNav = $this->Navigation->createNav(((int)$subNavInfo !== -1) ? end($this->path)->getLink() : end($this->path)->getParent()->getLink(), (int)$subNavInfo);

                    if (empty($subNav) && (int)$subNavInfo !== -1) {
                        // show siblings \\
                        $subNav = $this->Navigation->createNav(end($this->path)->getParent()->getLink(), -1);
                    }
                }

                if ($request->isMethod('POST') && $request->get('json')) {
                    // is a JSON POST and will be guaranteed a special URL so only return the special response \\
                    return new JsonResponse($specials);
                }

                //add structure id tot he template context
                if (isset($this->content)) {
                    $viewData['structure_id'] = $this->content->getStructure()->getid();
                }
                
                // send components to the '$viewData'.
                // only send if the boolean value for a component in '$components' is true.
                if ($components['breadcrumb']) {
                    $viewData['breadcrumb'] = $this->displayBreadcrumb();
                }
                
                if ($components['content']) {
                    $viewData['content'] = $this->displayContent();
                }
                
                if ($components['filter_options'] && $this->filterOptions !== null && !empty($this->filterOptions)) {
                    $viewData['filter_options'] = $this->filterOptions['filters'];
                }
                
                if ($components['nav']) {
                    $viewData['nav'] = $this->Navigation->createNav();
                }
                                
                if ($components['section']) {
                    $viewData['section'] = $this->section->toJSON(['id', 'title', 'link']);
                }
                
                if ($components['specials']) {
                    $viewData = array_merge(
                        $viewData,
                        $specials
                    );
                }
                
                if ($components['subnav']) {
                    $viewData['subnav'] = $subNav;
                }
                
                if ($components['ugc']) {
                    $viewData = array_merge($viewData, $ugcInfo);
                }
                
                if ($components['user'] && $user !== null) {
                    $viewData['user'] = $user;
                }
                
                if ($components['rating'] && $rateable) {
                    //Init the ratings helper
                    $rating = new RatingHelper($this->getDoctrine(), end($this->path));

                    //Check if the number of ratings is greater then the limit set in the site config
                    $numberOfRatings = $rating->getNumberOfRatings();
                    $average = $numberOfRatings >= RatingHelper::RATINGS_MINIMUM_LIMIT ? $rating->GetAverage(): 0;

                    //If logged in add the users own rating
                    $myRating = ($user !== null) ? $rating->GetMyRatings($userObj) : null;

                    $viewData['rating'] = array(
                        'average' => $average,
                        'my-rating' => $myRating
                    );
                }

                //user requires a download
                if ($download_requested) {
                    //add to twig
                    $viewData['download_requested'] = $download_requested;
                    //remove download request from session
                    $session->remove('download_file');
                }

                if ($viewDataAsJSON) {
                    return new JsonResponse($viewData);
                }
                                
                // present the view.
                $templateFile = $this->content->getTemplateFile()->getTemplateFile();
                if($this->get('templating')->exists($templateFile)) {
                    return $this->render($templateFile, $viewData);
                } else {
                    return $this->redirect('/');
                }
            } else if ($downloadFormat !== null) {
                // if $downloadFormat is not null,
                // the request URI has matched the "download as an archive" path.
                return $this->downloadFilesArchivedFromStructure($this->path[count($this->path) - 1], $downloadFormat);
            } else {
                $response = null;

                foreach ($this->content->getCustomFieldData() as $custom_field) {
                    if ($custom_field->getCustomFields()->getName() === 'file') {
                        // this is downloadable \\
                        $file = $custom_field->getValue();
                        $response = $this->downloadFile($file, $this->content->getTitle(), $this->content->getId(), $request);
                        break;
                    }
                }

                if ($response !== null) {
                    return $response;
                }
            }
        } else {
            // access is denied \\
            if ($this->content->getTemplateFile()
                && $this->content->getTemplateFile()->getTemplateFile() === 'download') {
                // get the parent for downloads \\
                if (!is_null($request->get('referral'))) {
                    $link = $request->get('referral');
                } else {
                    $link = '/' . $this->content->getStructure()->getParent()->getFullLink(true);
                }
                $session->set('download_file', '/' . $this->content->getStructure()->getFullLink(true));
            } else {
                $link = '/' . $this->content->getStructure()->getFullLink(true);
            }
            $session->set('_security.registered_area.target_path', $link);
            return $this->redirect('/login');
        }

        return $this->redirect('/');
    }

    private function DetectLanguage()
    {
        // detect the language plugin and if it is there then detect what language being viewed \\
        if ($this->container->has('LangSelect')) {
            $lang = $this->get('LangSelect');
            $this->lang = $lang->current();
            $this->langName = $lang->name();
        }
    }

    // TODO: evaluate performance.

    /**
     * Helpful method to set the path from an external point
     * @param array $path
     */
    public function setRawPath($path)
    {
        $this->rawpath = $path;
    }

    public function setStructures()
    {
        $this->structures = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure');
    }
    
    /**
     * RW - this method adds a layer to allow external sources to create content
     */
    public function initContent(Request $request, &$paginationInfo = null)
    {
        $user = null;
        $userObj = $this->get('security.token_storage')->getToken()->getUser();
        if (is_object($userObj)) {
            $user = $userObj;
        }
        // set up content helper.
        $this->contentHelper = new ContentHelper(
            $this->getRepository('EdcomsCMSContentBundle:Content'),
            $this->getRepository('EdcomsCMSContentBundle:Structure'),
            $this->getDoctrine(),
            $user,
            $this->get('edcoms.content.service.configuration')
        );
        $this->contentHelper->setFilterOptionsHelper($this->filterOptionsHelper);
        $requestQuery = $request->query;
        $getParameters = $requestQuery->all();
        $viewDataAsJSON = array_key_exists('json', $getParameters);
        // exception is thrown if no content has been found.
        // we'll catch this, so that we can send back an empty JSON string if this occurs and the user has requested a JSON response.
        try {
            $structure = end($this->path);
            
            if ($structure->getMaster() !== null) {
                // is a symlink \\
                $symlink = $this->get('EdcomsCMSSymlinks');
                $symlinkCT = $symlink->GetContentType();
                $structure = $this->getRepository('EdcomsCMSContentBundle:Structure')->find($structure->getMaster());
            }
            
            $paginationInfo = $this->contentHelper->getPaginationInfo($request, $structure);
            $this->contentHelper->createContent($structure, $paginationInfo, $this->filterOptions, $content, $contentArr);
            
            $this->content = $content;
            $this->contentarr = $contentArr;
        } catch (NotFoundHttpException $e) {
            if ($viewDataAsJSON) {
                return new JsonResponse(null, 404);
            }
            
            // continue to throw the exception if we have not requested to return a JSON response.
            throw $e;
        }
    }

    /**
     * Helper method to return a path array of structure entities to external points
     * @return array
     */
    public function getStructure()
    {
        return $this->path;
    }

    /**
     * Helper method to return content, this relies on createStructure and createContent having been called first
     * @return Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * This method sets up the path and the corresponding structure object for each item
     *
     * @param   Request     $request    
     *
     * @return  mixed                   
     * @throws  NotFoundHttpException   
     */
    public function createStructure(Request $request = null)
    {
        $i = 0;
        $structuresRepository = $this->getRepository('EdcomsCMSContentBundle:Structure');
        
        foreach ($this->rawpath as $item) {
            if (empty($this->path)) {
                $tmp = $structuresRepository->findBy(['link' => $item, 'deleted' => false]);
                if (!empty($tmp)) {
                    $this->path[] = $tmp[0];
                }
            } else if (!empty($item)) {
                $tmp = $structuresRepository->findBy(['link' => $item, 'parent' => $this->path[$i], 'deleted' => false]);
                if (!empty($tmp[0])) {
                    $this->path[] = $tmp[0];
                }
                $i++;
            }
        }
        $this->section = reset($this->path);
        if (empty($this->path)) {
            // means we have no structure found so throw an exception \\
            if ($request !== null && $request->query->has('json')) {
                // if the passed request object contains the GET parameter 'json',
                // return a JSON response with a status of 404 instead of throwing the exception.
                return new JsonResponse(null, 404);
            }

            throw new NotFoundHttpException();
        }
        return true;
    }
    /*
     * This method returns the content ready to use
     */
    private function displayContent()
    {
        return $this->contentarr;
    }

    private function displayBreadcrumb()
    {
        $outputJSON = [];
        $lang = $this->langName;
        foreach ($this->path as $struct) {
            $content = $this->allContent[$struct->getId()];
            /* language detection for titles */
            $title = ($this->lang === $this->defaultLang) ? $struct->getTitle() : $content->getCustomFieldData()->filter(function ($customField) use ($lang) {
                return ($customField->getCustomFields()->getName() === $lang . '_title');
            });
            if (empty($title)) {
                $title = $struct->getTitle();
            }
            $outputJSON[] = ['id' => $struct->getId(), 'title' => $title, 'link' => $struct->getLink()];
        }
        return $outputJSON;
    }

    private function downloadFile($file, $title, $id, Request $request)
    {
        //try for hashed file first as this is more likely
        $extractedPath = '';
        if (strrpos($file, '/') > 0) {//extract the filename
            $extractedPath = urldecode(ltrim(dirname($file), '/')).'/';//with trailing slash
            $extractedFilename = urldecode(basename($file));

            //TODO - is this the right place to remove this?
            //the file manager returns files with '/media/view/' prepended which will cause downloads to fail
            $needle = 'media/view/';
            if ( strpos($extractedPath, $needle) === 0 ) { //string is at start of path
                $extractedPath = substr($extractedPath, strlen($needle));
            }

        } else {
            $extractedFilename = urldecode($file);
        }

        //get the media file form the repo
        $mediaRepo = $this->getRepository('EdcomsCMSContentBundle:Media');
        //files and paths are stored in the with no leading or trailing slashes
        $media = $mediaRepo->findOneBy([
            'title' => ltrim($extractedFilename, '/'),
            'path' => rtrim(ltrim($extractedPath, '/'), '/'),
            'deleted' => false
        ]);
        $absfile = '';
        if ($media) {//only if file returned from db

            //get the first media file (we're only using one file per media object atm)
            $mediaFile = $media->getMediaFiles()->first();
            if ($mediaFile) {//if media file retrieved from object
                //get the hashed filename
                $hashedFileName = $mediaFile->getFilename();
                $absfile = $this->container->getParameter('kernel.root_dir') . '/Resources/files/' . $extractedPath . $hashedFileName;
            } else {
                throw new NotFoundHttpException('File doesn\'t exist!');
            }
        } else {
            throw new NotFoundHttpException('File doesn\'t exist!');
        }

        if (!file_exists($absfile)) {//if file doesn't exist try for human readable file name
            $absfile = $this->container->getParameter('kernel.root_dir') . '/Resources/files/' . ltrim($file, '/');
        }

        if (!file_exists($absfile)) {
            throw new NotFoundHttpException('File doesn\'t exist!');
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        try {
            if (is_object($user)) {
                // need to detect if SPIRIT is installed, if so load it \\
                $spirit = null;

                if ($this->container->has('SPIRITRegistration')) {
                    $spirit = $this->get('SPIRITRegistration');
                }

                if (!is_null($spirit)) {
                    $spiritID = $user->getPerson()->getContacts()->filter(function ($type) {
                        return ($type->getType() === 'spirit_id');
                    });

                    if (count($spiritID) === 1) {
                        $finfo = new \finfo(FILEINFO_MIME);
                        $mime = $finfo->file($absfile);
                        $date = \DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
                        $spirit->registerActivity($spiritID->first()->getValue(), $file, $mime, 'download', $title, $date);
                    }
                }
            }
        } catch (\Exception $e) {
            // if exception, do nothing as it's not crucial to submit an activity log to SPIRIT.
        }

        // add logging \\
        $logging = new ActivityLog();
        $logging->setAction('download');
        $logging->setDate(new \DateTime());
        $logging->setDetail($title);
        $logging->setId($id);
        if (is_object($user)) {
            $logging->setUser($user);
        }
        $logging->setReferenceType('content');
        $logging->setReferenceID($id);

        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $em->persist($logging);
        $em->flush();

        $session = $request->getSession();
        $session->remove('download_file');
        set_time_limit(0);
        $size = filesize($absfile);

        // old code commented out and left for archive.

        // header("Content-type: application/octet-stream");
        // header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
        // header('Content-Transfer-Encoding: binary');
        // header("Content-length: $size");
        // // fixed for IE \\
        // header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        // header('Pragma: public');
        // $fh = fopen($absfile, 'r');
        // while (!feof($fh)) {
        //     $buffer = fread($fh, 2048);
        //     echo $buffer;
        // }
        // fclose($fh);
        // exit();

        // send the file back to the client as a response.
        $response = new BinaryFileResponse($absfile);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($file));
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Pragma', 'public');
        
        return $response;
    }
    
    /**
     * This method can be bound as a special URL to CMS pages
     * @return array
     */
    public function Sitemap()
    {
        $navigation = new Navigation(
            $this,
            $this->getRepository('EdcomsCMSContentBundle:Content'),
            $this->getRepository('EdcomsCMSContentBundle:Structure')
        );
        
        $sitemap = $navigation->createNav('',0,true);
        
        return $sitemap;
    }

    /**
     * 
     * @param string $entityName
     */
    private function getRepository($entityName)
    {        
        if (isset($this->repositories[$entityName])) {            
            return $this->repositories[$entityName];
        }
        
        $this->repositories[$entityName] = $this->getDoctrine()
            ->getManager()
            ->getRepository($entityName);
        
        return $this->getRepository($entityName);
    }
    
    /**
     * Recursively searches for files specified in CustomFields named 'file'.
     * Those files are then packaged up into an archive file in the format specified by '$format'.
     * If the format is implemented and the archive is successful, the archive file is sent to the client as a response.
     *
     * @param   Structure           $structure  Parent of the child structures to search through.
     * @param   string              $format     The format of the archive to produce.
     *
     * @return  BinaryFileResponse              The archived file in a downloadable format.
     */
    private function downloadFilesArchivedFromStructure(Structure $structure, $format)
    {
        $found = false;
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', $this->status))
            ->orderBy(['addedOn' => Criteria::ASC])
            ->setMaxResults(1);
        
        // get the latest {status} content from '$structure'.
        $content = $structure
            ->getContent()
            ->matching($criteria)
            ->first();
        
        if ($content !== false) {
            // if the content exists, fetch the CustomField with the 'allow_archive' name.
            $customField = $content->getCustomFieldData()->filter(function ($cfd) {
                return $cfd->getCustomFields()->getName() === CustomFields::NAME_ALLOW_ARCHIVE;
            })->first();
            
            if ($customField !== false) {
                // get the value of the customField.
                $found = boolval($customField->getValue());
            }
        }
        
        // if not found or the CustomField hasn't been checked by the CMS user,
        // throw a 404 error.
        if (!$found) {
            throw new NotFoundHttpException();
        }
        
        $link = null;
        $name = $content->getTitle();
        
        // TODO: get a list of archive formats to support.
        switch (strtolower($format)) {
            case 'rar':
                // TODO: implement.
                break;
            case 'tar':
                // TODO: implement.
                break;
            case 'zip':
                $link = $this->downloadFilesZipArchivedFromStructure($structure);
                break;
            default:
                break;
        }
        
        if ($link === false) {
            throw new NotFoundHttpException('No downloadable files found.');
        } else if ($link === null) {
            // 501 - not implemented.
            throw new HttpException(501, 'Archive format not implemented.');
        }

        //add spirit logging
        try {
            $spirit = null;

            if ($this->container->has('SPIRITRegistration')) {
                $spirit = $this->get('SPIRITRegistration');
            }

            if (!is_null($spirit)) {
                $user = $this->get('security.token_storage')->getToken()->getUser();

                if (is_object($user)) {
                    $spiritID = $user->getPerson()->getContacts()->filter(function ($type) {
                        return ($type->getType() === 'spirit_id');
                    });

                    if (count($spiritID) === 1) {
                        $date = \DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
                        $spirit->registerActivity(
                            $spiritID->first()->getValue(),
                            'download pack',
                            $format,
                            'download',
                            $name,
                            $date);
                    }
                }
            }
        } catch (\Exception $e) {
            // if exception, do nothing as it's not crucial to submit an activity log to SPIRIT.
        }
        
        // send the file back to the client as a response.
        $response = new BinaryFileResponse($link);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, "$name.$format");
        
        return $response;
    }
    
    /**
     * Recursively searches for files specified in CustomFields named 'file'.
     * Those files are then packaged up into a ZIP file which is then sent to the requesting client.
     *
     * @param   Structure   $structure  Parent of the child structures to search through.
     *
     * @return  string                  Path of the created ZIP file, or null if an error has occured.
     */
    private function downloadFilesZipArchivedFromStructure(Structure $structure)
    {
        $zipHelper = $this->container->get('EdcomsCMSZip');
        
        $doctrine = $this->get('doctrine');
        $em = $doctrine->getManager('edcoms_cms');
        $mediaRepository = $em->getRepository('EdcomsCMSContentBundle:Media');
        $files = $mediaRepository->findDownloadFilesFromStructure($structure, $this->get('kernel')->getRootDir() . '/Resources/files');
        
        if (empty($files)) {
            return false;
        }
        
        $link = $zipHelper->createZip($files);
        
        return $link;
    }

}
