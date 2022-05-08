<?php

namespace EdcomsCMS\ContentBundle\Helpers;
        
use Firebase\JWT\JWT;
use GuzzleHttp\Client;

use EdcomsCMS\ContentBundle\Controller\DisplayController;
use EdcomsCMS\ContentBundle\Helpers\ContentHelper;
use EdcomsCMS\ContentBundle\Entity\ContentType;
use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\LinkBuilder;
use EdcomsCMS\ContentBundle\Entity\Structure;
use EdcomsCMS\ContentBundle\Entity\TemplateFiles;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use EdcomsCMS\AuthBundle\Entity\cmsUsers;

/**
 * This helper class is used to handle the API methods and actions - it allows the website to interact directly without needing to call HTTP methods
 *
 * @author richard
 */
class APIHelper {
    private $doctrine;
    private $container;
    private $tokenStorage;
    private $filterOptions = null;
    private $connector;
        
    private $token;
    private $client;
    /**
     *
     * @var ContentHelper
     */
    private $contentHelper;
    /**
     *
     * @var array Containing all Structure objects 
     */
    private $path = [];
    /**
     *
     * @var array Containing all path strings 
     */
    private $rawpath;
    public function __construct($doctrine, $container, $tokenStorage) {
        $this->doctrine = $doctrine;
        $this->container = $container;
        $this->tokenStorage = $tokenStorage;
        $this->initialiseContentHelper();
    }
    /**
     * Fire and forget to setup the ContentHelper class
     */
    public function initialiseContentHelper()
    {
        $user = null;
        $userObj = (!is_null($this->tokenStorage->getToken())) ? $this->tokenStorage->getToken()->getUser(): null;
        if (is_object($userObj)) {
            $user = $userObj;
        }
        $this->contentHelper = new ContentHelper(
            $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Content'),
            $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure'),
            $this->doctrine,
            $user,
            $this->container->get('edcoms.content.service.configuration')
        );
        $this->contentHelper->setFilterOptionsHelper($this->container->get('EdcomsCMSFilterOptions'));
    }
    /**
     * Save an item, of type, the data provided
     * @param string $type
     * @param integer $id
     * @param array $data
     * @return boolean $status
     */
    public function save($type, $data)
    {
        switch ($type) {
            case 'content':
                // this handles both structure and content in one object \\
                $this->updateStatus($data->getStructure());
                $this->contentHelper->saveContent($data);
                $this->doctrine->getManager('edcoms_cms')->flush();
                break;
            case 'user':
                
                break;
        }
    }
    
    /**
     * 
     * @param type $type
     * @param type $data
     */
    public function saveMultiple($type, $data)
    {
        switch ($type) {
            case 'content':
                foreach ($data as $content) {
                    $this->updateStatus($content->getStructure());
                    $this->contentHelper->saveContent($content);
                }
                // I'm finished so flush everything now \\
                $this->doctrine->getManager('edcoms_cms')->flush();
                break;
        }
    }
    
    /**
     * Get an item of type based on the ID provided
     * @param string $type
     * @param int $id
     * @return object
     */
    public function get($type, $id)
    {
        $data = null;
        switch ($type) {
            case 'content':
                $structuresRepository = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure');
                $structure = $structuresRepository->find($id);
                $paginationInfo = null;
                $this->contentHelper->createContent($structure, $paginationInfo, $this->filterOptions, $content, $contentArr);

                $data = $content;
                break;
        }
        return $data;
    }

    /**
     * Get the ID of a piece of content based on the slug
     * @param string $slug
     * @return integer
     */
    public function getContentIdBySlug($slug)
    {
        $structuresRepository = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure');
        $path = explode('/', $slug);
        $this->path = [];
        $this->rawpath = $path;
        $i = 0;
        foreach ($this->rawpath as $item) {
            if (empty($this->path)) {
                $tmp = $structuresRepository->findBy(['link' => $item]);
                if (!empty($tmp)) {
                    $this->path[] = $tmp[0];
                }
            } else if (!empty($item)) {
                $tmp = $structuresRepository->findBy(['link' => $item, 'parent' => $this->path[$i]]);
                if (!empty($tmp[0])) {
                    $this->path[] = $tmp[0];
                }
                $i++;
            }
        }
        return (!empty($this->path) && end($path) === end($this->path)->getLink()) ? end($this->path)->getId() : false;
    }
    
    /**
     * Check for a content type and that it has the appropriate custom fields
     * @param string $name
     * @param boolean $create
     * @param array $fields
     */
    public function configureContentType($name, $create=false, $fields=[], $templates=[])
    {
        $contentTypeRepo = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:ContentType');
        $contentType = $contentTypeRepo->findOneBy(['name'=>$name]);
        if ($create) {
            $em = $this->doctrine->getManager('edcoms_cms');
            if (!$contentType) {
                $contentType = new ContentType();
            }
            $customFields = $contentType->getCustomFields();
            foreach ($fields as $field) {
                $exists = $customFields->filter(function($cfield) use ($field) {
                    return ($cfield->getName() === $field->getName());
                });
                if ($exists->count() === 0) {
                    $contentType->addCustomField($field);
                    if (count($field->getChildren()) > 0) {
                        foreach ($field->getChildren() as $child) {
                            $contentType->addCustomField($child);
                        }
                    }
                }
            }
            foreach ($templates as $template) {
                // filter by templateFile and see if exists \\
                $existing = $contentType->getTemplateFiles()->filter(function($item) use ($template) {
                    if ($item->getTemplateFile() === $template) {
                        return true;
                    }
                    return false;
                });
                if (empty($existing)) {
                    $templateFile = new TemplateFiles();
                    $templateFile->setTemplateFile($template);
                    $contentType->addTemplateFile($templateFile);
                }
            }
            $em->persist($contentType);
            $em->flush();
        }
        return $contentType;
    }
    
    /**
     * Search for an item in the ContentCache - links through to ContentHelper method
     * @param string $type
     * @param string $UUID
     * @return ContentCache
     */
    public function findInCache($type, $UUID)
    {
        return $this->contentHelper->findInCache($type, $UUID);
    }
    /**
     * Save an item in the ContentCache - links through to ContentHelper method
     * @param string $type
     * @param string $UUID
     * @param string $value
     * @return ContentCache
     */
    public function putInCache($type, $UUID, $value)
    {
        return $this->contentHelper->putInCache($type, $UUID, $value);
    }
    
    /**
     * Creates a new Content instance with a Structure associated
     * @return Content
     */
    public function createContent()
    {
        $content = new Content();
        $structure = new Structure();
        $content->setStructure($structure);
        return $this->handleContent($content);
    }
    
    public function handleContent(Content $content)
    {
        return $this->contentHelper->handleContent($content);
    }
    
    /**
     * 
     * @param Content $content
     * @param string $name
     * @return CustomFields
     */
    public function getCustomField(Content $content, $name)
    {
        $customFields = $content->getContentType()->getCustomFields();
        $customField = $customFields->filter(function($field) use ($name) {
            return ($field->getName() === $name);
        });
        return $customField->first();
    }
    
    public function getAdminUser()
    {
        // API should always use the primary admin user \\
        $users = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSAuthBundle:cmsUsers');
        try {
            $user = $users->loadUserByUsername('admin');
        } catch(UsernameNotFoundException $e) {
            $user = false;
        }
        return $user;
    }
    public function updateStatus(Structure $structure)
    {
        $contents = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Content');
        $contentList = $contents->findBy(['structure'=>$structure, 'status'=>'published']);
        foreach ($contentList as $content) {
            $content->setStatus('previous');
        }
    }

    /**
     * Get mailer mail and username
     * @return mixed
     */
    public function mailerParameters(){
        $sender_mail = $this->container->getParameter('mailer_user');
        if($this->container->hasParameter('mailer_username')){
            $sender_name = $this->container->getParameter('mailer_username');
            $maileruser[$sender_mail] = $sender_name;
        }else{
            $maileruser = $sender_mail;
        }
        return $maileruser;
    }


    /**
     * Creates a new LinkBuilder object with the URL set from the value of '$link'.
     *
     * If the options has a key of 'internal' with the value set as 'true',
     * A Structure object is retrieved using '$link' as the search criteria,
     * then automatically associated to the LinkBuilder object.
     *
     * @param   string  $link       URL to set the new LinkBuilder object with.
     * @param   array   $options    Customised options used to set more specific characteristics of the LinkBuilder object.
     *
     * @return  Linkbuilder         The newly created LinkBuilder entity. This will have been added into the database automatically if not null.
     */
    public function createFriendlyLink($link, array $options = [])
    {
        $linkBuilder = new LinkBuilder();

        // get the current logged in user.
        // if user is not logged in, the option 'anonymous' must be set to true.
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        // if the user is a string,
        // this is a anonymous user.
        if (is_string($user)) {
            $user = null;
        }

        if ($user === null && !(isset($options['anonymous']) && $options['anonymous'])) {
            throw new \Exeption('Cannot create a friendly link as a anonymous user without the option \'anonymous\' set to \'true\'.');
        }

        // set the friendly link with a generated code.
        // format before hashed: '{time}:{user id}:{url}'.
        $linkBuilder->setFriendlyLink(strtoupper(substr(sha1(
            microtime() . 
            ':' . 
            ($user === null ? 0 : $user->getId()) .
            ':' .
            $link
        ), 0, 8)));

        // set the target if included in the '$options' array.
        if (isset($options['target'])) {
            $linkBuilder->setTarget($options['target']);
        }

        // if it's an internal link, fetch the relevent Structure object.
        if (isset($options['internal']) && $options['internal']) {
            // convert '$link' into an exploded array.
            // required by DisplayController for the 'rawpath'.
            $link = explode('/', $link);

            // initalise the DisplayController,
            // then set the URI path as it is a stateful object.
            $displayController = new DisplayController();
            $displayController->setContainer($this->container);
            $displayController->setRawPath($link);

            // call the DisplayController to fetch the Structure object.
            // the method will throw an exception if a Structure could not be found.
            try { $displayController->createStructure(); } catch (NotFoundHttpException $e) { }

            $structure = $displayController->getStructure();

            // don't need the DisplayController object anymore, so dispose of it.
            unset($displayController);

            // if '$structure' returned from the DisplayController is null,
            // return null as the Structure object could not be found with the URI of '$link'.
            if ($structure === null || empty($structure)) {
                return null;
            }

            // grab the last structure object.
            $structure = end($structure);

            // add the new LinkBuilder to the Structure object.
            // this should automatically associate the structure of '$linkBuilder'.
            $structure->addLinkBuilder($linkBuilder);
        } else {
            // TODO: consider checking connection to '$link' via CURL request?

            // this isn't an internal URL, so set this in the LinkBuilder object.
            $linkBuilder->setLink($link);
        }

        // add the LinkBuilder into the database.
        $em = $this->container->get('doctrine')->getManager('edcoms_cms');
        $em->persist($linkBuilder);
        $em->flush();

        // return the newly created LinkBuilder.
        return $linkBuilder;
    }

    /**
     * Returns the URL from the LinkBuilder entity found using '$friendlyLink' as the search criteria.
     * If the found LinkBuilder has an associated Structure,
     * the URL is constructed using it and it's parent's links recursively.
     *
     * @param   string  $friendlyLink   The shortened URL to search with.
     *
     * @return  string                  The found or constructed URL. 'null' if the LinkBuilder entity cannot be found.
     */
    public function getURLFromFriendlyLink($friendlyLink)
    {
        $em = $this
            ->container
            ->get('doctrine')
            ->getManager('edcoms_cms');

        // fetch the link builder.
        $linkBuilder = $em
            ->getRepository('EdcomsCMSContentBundle:LinkBuilder')
            ->findByFriendlyLink($friendlyLink);

        // if the LinkBuilder cannot be found, return null immediately.
        if ($linkBuilder === null) {
            return null;
        }

        // if the found LinkBuilder has an associated Structure,
        // find it's ancestors and build up the URL using all of their 'links'.
        if ($linkBuilder->hasStructure()) {
            $structure = $em
                ->getRepository('EdcomsCMSContentBundle:Structure')
                ->findWithAncestors($linkBuilder->getStructure()->getId());

            // start building up URL.
            $url = '';

            // iterate through all ancestors.
            // continue until the current Structure does not have a parent.
            while ($structure !== null) {
                $link = $structure->getLink();

                // don't add the link if the current Structure is 'home'.
                if ($link !== 'home') {
                    $url = "$link/$url";
                }

                $structure = $structure->getParent();
            }

            // add the forward slash at the beginning.
            $url = "/$url";

            // return the constructed URL.
            return $url;
        }

        // if not an internal link,
        // return the URL straight from the LinkBuilder entity,
        return $linkBuilder->getLink();
    }
    
    public function generateToken($library) {

        if ($this->container->hasParameter($library)) {
            $lib_api = $this->container->getParameter($library);
            $server_sig = $this->container->getParameter('server_signature');
            $apiurl = $lib_api['api_url'];
            $clienturl = $server_sig['server_url'];
            $clientname = $lib_api['client_name'];
            $privatekey = file_get_contents($this->container->get('kernel')->getRootDir().'/../var/'.$server_sig['private_key']);
            $issuedAt = time();
            $notBefore = $issuedAt + 0;
            $expire = $notBefore + 500;
            $token = array(
                "iss" => $apiurl,
                "aud" => $clienturl,
                "iat" => $issuedAt,
                "site" => $clientname,
                "nbf" => $notBefore,
                'exp' => $expire
            );
            $jwt = JWT::encode($token, $privatekey, 'RS256');
            $this->token = $jwt;
            $this->client = new Client([
                'base_uri' => $apiurl,
                'headers' => ['Authorization' => "Bearer $jwt"],
            ]);
            return $this->getToken();
        }
    }
    
    public function generateAuthQueryString($privateKey, cmsUsers $user, ...$params)
    {
        $query = [];
        foreach ($params as $param) {
            switch ($param) {
                case 'signature':
                    $query['signature'] = base64_encode($this->signData([$user->getUsername()], $privateKey));
                    break;
                default:
                    $method = 'get'.$param;
                    $query['user_'.strtolower($param)] = (method_exists($user, $method)) ? $user->{$method}() : 'no_value';
                    break;
            }
        }
        return http_build_query($query);
    }
    
    public function signData($data=[], $key='')
    {
        $signature = null;
        
        if (empty($key)) {
            $server_sig = $this->container->getParameter('server_signature');
            $privatekey = file_get_contents($this->container->get('kernel')->getRootDir().'/../var/'.$server_sig['private_key']);
        } else {
            $privatekey = base64_decode($key);
        }
        if (openssl_sign(implode('|', $data), $signature, $privatekey)) {
            return $signature;
        }
        return false;
    }
    
    public function decodeData($signature, $data=[], $key='')
    {
        if (empty($key)) {
            $server_sig = $this->container->getParameter('server_signature');
            $publickey = file_get_contents($this->container->get('kernel')->getRootDir().'/../var/'.$server_sig['public_key']);
        } else {
            $publickey = base64_decode($key);
        }
        $vSignature = base64_decode($signature);
        return openssl_verify(implode('|', $data), $vSignature, $publickey);
    }
    
    public function getToken() {
        return $this->token;
    }

    public function getClient() {
        return $this->client;
    }

    public function getSources($library)
    {
        if ($this->container->hasParameter($library)) {
            $lib_api = $this->container->getParameter($library);
            if (isset($lib_api['lib_sources'])) {
                return $lib_api['lib_sources'];
            }
        }
        return null;

    }
    
    /**
     * 
     * @return \Symfony\Component\DependencyInjection\Container
     */
    protected function getContainer()
    {
        return $this->container;
    }
    
    protected function getDoctrine()
    {
        return $this->doctrine;
    }
    
    public function setConnector($connector)
    {
        $this->connector = $connector;
    }
    
    public function getConnector()
    {
        return $this->connector;
    }
}
