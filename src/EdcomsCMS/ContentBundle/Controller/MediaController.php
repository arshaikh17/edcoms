<?php

namespace EdcomsCMS\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use EdcomsCMS\ContentBundle\Helpers\MediaUploader;
use EdcomsCMS\ContentBundle\Helpers\MediaBrowser;
use EdcomsCMS\ContentBundle\Helpers\FileManager;

use EdcomsCMS\ContentBundle\Entity\Media;
use EdcomsCMS\ContentBundle\Entity\MediaFiles;
use EdcomsCMS\ContentBundle\Entity\MediaLinks;
use EdcomsCMS\ContentBundle\Entity\MediaTypes;


class MediaController extends Controller
{
    private $FileManager;
    private $root_dir;
    private $items = [];
    private $targets = [];
    /**
     *
     * @var Request 
     */
    private $request;
    
    public function __construct()
    {
        $this->FileManager = new FileManager();
        $this->root_dir = $this->FileManager->GetRoot().'/';
    }
    /**
     * @Route("/cms/media")
     * @return TWIG
     */
    public function indexAction()
    {
        return $this->render(
            'EdcomsCMSTemplatesBundle:Configuration:index.html.twig',
            [
                'title'=> 'Configuration'
            ]
        );
    }
    /**
     * @Route("/cms/media/check/{folder}", requirements={"folder"=".+"})
     * @Route("/public/upload/check/{folder}", requirements={"folder"=".+"})
     * @param string $folder
     */
    public function checkAction($folder, Request $request)
    {
        $file = $folder.'/'.$request->get('filename');
        $curURL = explode('/', $request->getPathInfo());
        $fullPath = $this->container->get('kernel')->getRootDir().$this->root_dir.($curURL[1] === 'public') ? 'public/'.$folder.'/' : '';
        $response = new Response();
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $response->setContent(($this->FileManager->checkExists($em,$fullPath, $file)) ? 1 : 0);
        return $response;
    }
    
    /**
     * @Route("/cms/media/targets")
     */
    public function listTargets()
    {
        $targets = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:MediaTypes');
        $available = $targets->findAll();
        array_walk($available, array(&$this, 'PrepTargets'));
        return new JsonResponse($this->targets);
    }
    
    private function PrepTargets(MediaTypes $target)
    {
        if (!isset($this->targets[$target->getTarget()])) {
            $this->targets[$target->getTarget()] = [];
        }
        $this->targets[$target->getTarget()][] = ['mimetype'=>$target->getFiletype(), 'compression'=>$target->getCompression(), 'width'=>$target->getWidth(), 'height'=>$target->getHeight()];
    }


    /**
     * @Route("/cms/media/upload/{target}/{folder}", requirements={"folder"=".+"})
     * @Route("/public/upload/{target}/{folder}", name="public_media_upload", requirements={"folder"=".+"})
     * @param string $folder
     */
    public function uploadAction($target, $folder, Request $request)
    {
        $resp = ['status'=>0, 'error'=>'no_file'];
        $status = 200;
        if (!is_null($request->files)) {
            $fileManager = new \EdcomsCMS\ContentBundle\Helpers\FileManager();
            $root = $this->container->get('kernel')->getRootDir() . '/' . ltrim($fileManager->GetRoot(), '/') . '/';
            unset($fileManager);
            $file = $request->files->get('Filedata');
            
            $curURL = explode('/', $request->getPathInfo());
            if (!is_null($file) && $file->isValid()) {
                $fullPath = ($curURL[1] === 'public' ? "public/$folder/" : $folder);
                
                if ($curURL[1] === 'public' || !$this->FileManager->checkExists($this->container->get('kernel')->getRootDir() . $this->root_dir . $fullPath, $file->getClientOriginalName())) {
                    $em = $this->getDoctrine()->getManager();
                    $user = $this->get('security.token_storage')->getToken()->getUser();
                    $mediaUploader = $this->get('edcoms.content.helper.media.uploader');
                    $resp = $mediaUploader->UploadFile($em, $root, $file, $target, $user, $fullPath, ($curURL[1] === 'public') ? true : false);
                    
                    // get the status and response objects from the value returned by the upload helper.
                    $status = $resp['status'];
                    $resp = $resp['resp'];
                } else {
                    $resp = [
                        'error'=>'file_exists',
                        'status'=>0
                    ];
                    $status = 500;
                }
            } else {
                $resp = [
                    'error'=>$file->getError(),
                    'status'=>0
                ];
                $status = 500;
            }
        }

        return new JsonResponse($resp, $status);
    }

    /**
     * @Route("/media/view/{file}", requirements={"file"=".+"}, name="media_view")
     * @param   string      $file
     * @param   Request     $request
     * @return  Response
     */
    public function displayAction($file, Request $request)
    {
        $fileOrig = $file;
        // get the session \\
        $session = $request->getSession();
        
        $this->request = $request;
        
        // see if the download_file has been set \\
        $download_requested = $session->get('download_file');
        $authorizationChecker = $this->container->get('security.authorization_checker');
        if (!is_null($download_requested) && !$authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') && !$authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            // if it is set but the user isn't logged in then clear the request \\
            $session->remove('download_file');
        }
        $fileArr = explode('/', $file);
        $authenticated = true;
        if ($fileArr[count($fileArr)-1] === 'login') {
            $authenticated = false;
            if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') || $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
                $authenticated = true;
            }
            array_pop($fileArr);
            $file = implode('/', $fileArr);
        }
        if ($authenticated) {
            $title = basename($file);
            $path = str_replace(basename($file), '', $file);
            $medias = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Media');
            $media = $medias->findOneBy([
                'path'=>rtrim($path, '/'),
                'title'=>$title,
                'deleted'=>false
            ]);
            if ($media && file_exists($this->container->get('kernel')->getRootDir().$this->root_dir.$media->getPath().'/'.$media->getMediaFiles()->first()->getFilename())) {
                // Generate response
                $headers = [
                    'Content-type'=>$media->getMediaFiles()->first()->getType()->getFiletype()
                ];
                //init response
                $response = new BinaryFileResponse(
                    $this->container->get('kernel')->getRootDir().$this->root_dir.$media->getPath().'/'.$media->getMediaFiles()->first()->getFilename(),//content
                    200,//http status code
                    $headers//headers array
                );

//                // Set headers
//                $response->headers->set('Cache-Control', 'private');
//                if  (strstr($media->getMediaFiles()->first()->getType()->getFiletype(), 'image') === false && strstr($media->getMediaFiles()->first()->getType()->getFiletype(), 'video') === false) {
//                    $response->headers->set('Content-Disposition', 'attachment; filename="' . $title . '";');
//                }
//
//                $response->headers->set('Content-length', $media->getMediaFiles()->first()->getFilesize());
//
//                $response->setContent(file_get_contents($this->container->get('kernel')->getRootDir().$this->root_dir.$media->getPath().'/'.$media->getMediaFiles()->first()->getFilename()));

                //set disposition, this should download if accessed directly in the browser or display if inline
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $media->getTitle()
                );

                return $response;
            } else {
                // not found \\
                throw $this->createNotFoundException('File Not Found');
            }
        } else {
            $session->set('download_file', '/media/view/'.$fileOrig);
            $referrer = '/media/view/'.$fileOrig;
            if (!empty($this->getRequest()->headers->get('referer'))) {
                $referrer = $this->getRequest()->headers->get('referer');
            }
            $session->set('_security.registered_area.target_path', $referrer);
            return $this->redirect('/login');
        }
    }
    /**
     * @Route("/media/file/view/{file}", requirements={"file"=".+"})
     * @param string $file
     * @param Request $request
     * @return Response
     */
    public function displayFileAction($file, Request $request)
    {
        $fileOrig = $file;
        // get the session \\
        $session = $request->getSession();
        
        $this->request = $request;
        
        // see if the download_file has been set \\
        $download_requested = $session->get('download_file');
        $authorizationChecker = $this->container->get('security.authorization_checker');
        if (!is_null($download_requested) && !$authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') && !$authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            // if it is set but the user isn't logged in then clear the request \\
            $session->remove('download_file');
        }
        $fileArr = explode('/', $file);
        $authenticated = true;
        if ($fileArr[count($fileArr)-1] === 'login') {
            $authenticated = false;
            if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') || $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
                $authenticated = true;
            }
            array_pop($fileArr);
            $file = implode('/', $fileArr);
        }
        if ($authenticated) {
            $absfile = $this->container->get('kernel')->getRootDir().$this->root_dir.$file;
            $title = basename($file);
            
            if (file_exists($absfile)) {
                $f = finfo_open(FILEINFO_MIME);
                $filemime = explode(';', finfo_file($f, $absfile))[0];
                $filesize = filesize($absfile);
                
                $options = [
                    'inline'=>(strstr($filemime, 'image') === false && strstr($filemime, 'video') === false) ? false : true,
                    'serve_filename'=>$title
                ];
                
                // Generate response
                $response = new Response();

                // Set headers
                $response->headers->set('Cache-Control', 'public');
                $response->headers->set('Content-type', $filemime);
                $response->headers->set('Content-Disposition', $this->resolveDispositionHeader($options));
                
                if ($this->get('kernel')->getEnvironment() === 'dev' && (in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', 'fe80::1', '::1']))) {
                    $response->setContent(file_get_contents($absfile));
                } else {
                    $response->headers->set('X-Accel-Redirect', '/files/'.$file);
                }
                
                return $response;
            } else {
                // not found \\
                throw $this->createNotFoundException('File Not Found');
            }
        } else {
            $session->set('download_file', '/media/file/view/'.$fileOrig);
            $referrer = '/media/file/view/'.$fileOrig;
            if (!empty($this->getRequest()->headers->get('referer'))) {
                $referrer = $this->getRequest()->headers->get('referer');
            }
            $session->set('_security.registered_area.target_path', $referrer);
            return $this->redirect('/login');
        }
    }
    /**
     * @Route("/media/file/thumb/{file}", requirements={"file"=".+"})
     * @param string $file
     * @return Response
     */
    public function thumbFileAction($file)
    {
        $filename = basename($file);
        $filehash = md5($filename);
        $path = str_replace($filename, '', $file);
        $absfile = $this->container->get('kernel')->getCacheDir().'/thumbs/'.$path.$filehash;
        if (file_exists($absfile)) {
            $f = finfo_open(FILEINFO_MIME);
            $filemime = explode(';', finfo_file($f, $absfile))[0];
            $filesize = filesize($absfile);
            // Generate response
            $response = new Response();

            // Set headers
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', $filemime);
            if  (strstr($filemime, 'image') === false && strstr($filemime, 'video') === false) {
                $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '";');
            }

            $response->headers->set('Content-length', $filesize);

            $response->setContent(file_get_contents($absfile));
            return $response;
        } else {
            // not found \\
            throw $this->createNotFoundException('File Not Found');
        }
    }
    /**
     * @Route("/media/thumb/{file}", requirements={"file"=".+"})
     * @param string $file
     * @param Request $request
     * @return Response
     */
    public function thumbAction($file, Request $request)
    {
        $fileOrig = $file;
        // get the session \\
        $session = $request->getSession();
        // see if the download_file has been set \\
        $download_requested = $session->get('download_file');
        $authorizationChecker = $this->container->get('security.authorization_checker');
        if (!is_null($download_requested) && !$authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') && !$authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            // if it is set but the user isn't logged in then clear the request \\
            $session->remove('download_file');
        }
        $fileArr = explode('/', $file);
        $authenticated = true;
        if ($fileArr[count($fileArr)-1] === 'login') {
            $authenticated = false;
            if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') || $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
                $authenticated = true;
            }
            array_pop($fileArr);
            $file = implode('/', $fileArr);
        }
        if ($authenticated) {
            $title = basename($file);
            $path = $this->FixPath(str_replace(basename($file), '', $file));
            $medias = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Media');
            $media = $medias->findOneBy([
                'path'=>rtrim($path, '/'),
                'title'=>$title,
                'deleted'=>false
            ]);

            if ($media && file_exists($this->container->get('kernel')->getCacheDir().'/thumbs/'.$media->getMediaFiles()->first()->getFilename())) {
                // Generate response
                $response = new BinaryFileResponse($this->container->get('kernel')->getCacheDir().'/thumbs/'.$media->getMediaFiles()->first()->getFilename());

                $d = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $title
                   );

                $response->headers->set('Content-Disposition', $d);

                return $response;
            } else {
                // not found \\
                throw $this->createNotFoundException('File Not Found');
            }
        } else {
            $session->set('download_file', '/media/view/thumb/'.$fileOrig);
            $referrer = '/media/view/thumb/'.$fileOrig;
            if (!empty($this->getRequest()->headers->get('referer'))) {
                $referrer = $this->getRequest()->headers->get('referer');
            }
            $session->set('_security.registered_area.target_path', $referrer);
            return $this->redirect('/login');
        }
    }
    /**
     * @Route("/cms/media/list/{path}", requirements={"path"=".+"}, defaults={"path"=""})
     * @param string path
     * List all files and folders in a specified path
     */
    public function listAction($path)
    {
        $path = ltrim($this->FixPath($path), '/');
        $media = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Media');
        $tgtPath = $this->container->get('kernel')->getRootDir().$this->root_dir.$path;
        $MediaBrowser = new MediaBrowser();
        $this->items = $MediaBrowser->ListDirectory($tgtPath, $media, $path);
        return new JsonResponse($this->items);
    }
    /**
     * 
     * @param string $path
     * @return string
     * Use this method to ensure there is a / on the end of folder paths
     */
    private function FixPath($path)
    {
        $p=str_replace('\\','/',trim($path));
        return (substr($p,-1)!='/') ? $p.='/' : $p;
    }
    
    /**
     * @Route("/cms/media/create/folder")
     */
    public function createFolderAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            // means it has been post'd so lets look for the folder name and it's parent structure \\
            $name = $request->get('name');
            $directory = $request->get('directory');
            // if directory is null then it is in the root, otherwise create folder called NAME inside DIRECTORY \\
            $absdir = $this->container->get('kernel')->getRootDir().$this->root_dir.$this->FixPath($directory);
            $status = true;
            if (!is_dir($absdir)) {
                $status = false;
            }
            if ($status) {
                $status = mkdir($absdir.'/'.$name, 0777, true);
            }
            return new JsonResponse(['status'=>$status]);
        }
    }
    
    /**
     * @Route("/cms/media/copy")
     * @Method({"POST"})
     * @param string $path
     * @param Request $request
     * @return JsonResponse Response Object
     */
    public function copyAction(Request $request)
    {
        $session = $request->getSession();
        $path = $request->get('path');
        if (!is_array($path)) {
            $path = [$path];
        }
        $absfiles = [];
        $medias = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Media');
        foreach ($path as $file) {
            $absfile = $this->container->get('kernel')->getRootDir().$this->root_dir.$file;
            if (file_exists($absfile)) {
                $title = basename($file);
                $path = str_replace(basename($file), '', $file);
                $media = $medias->findOneBy([
                    'path'=>rtrim($path, '/'),
                    'title'=>$title,
                    'deleted'=>false
                ]);
                if ($media) {
                    $absfiles[] = ['file'=>$absfile, 'id'=>$media->getId()];
                } else {
                    $absfiles[] = ['file'=>$absfile];
                }
            }
        }
        $session->set('_media.copy.paths', $absfiles);
        if (!empty($absfiles)) {
            return new JsonResponse(['status'=>true]);
        }
        return new JsonResponse(['status'=>false, 'error'=>'not_found']);
    }
    
    /**
     * @Route("/cms/media/paste/{path}", requirements={"path"=".+"})
     * @param type $path
     * @param Request $request
     * @return JsonResponse Response Object
     */
    public function pasteAction($path, Request $request)
    {
        $session = $request->getSession();
        $absfiles = $session->get('_media.copy.paths');
        $status  = false;
        if (is_array($absfiles)) {
            $status = true;
            $em = $this->getDoctrine()->getManager('edcoms_cms');
            $medias = $em->getRepository('EdcomsCMSContentBundle:Media');
            foreach ($absfiles as $media) {
                $absfile = $media['file'];
                if (!file_exists($absfile)) {
                    $status = false;
                    continue;
                }
                $filename = basename($absfile);
                $curstatus = copy($absfile, $this->FixPath($this->container->get('kernel')->getRootDir().$this->root_dir.$path).$filename);
                if (!$curstatus) {
                    $status = false;
                } else if (isset($media['id'])) {
                    $oldmedia = $medias->find($media['id']);
                    if (!$oldmedia) {
                        continue;
                    }
                    $newmedia = clone $oldmedia;
                    $newmedia->setPath($this->FixPath($path));
                    $em->persist($newmedia);
                }
            }
            $em->flush();
        }
        
        return new JsonResponse(['status'=>$status]);
    }
    protected function resolveDispositionHeader(array $options)
    {
        $disposition = $options['inline'] ? 'inline' : 'attachment';
        $filename = $options['serve_filename'];
        return "$disposition; ".$this->resolveDispositionHeaderFilename($filename);
    }
    protected function resolveDispositionHeaderFilename($filename)
    {
        $userAgent = $this->request->headers->get('User-Agent');
        if (preg_match('#MSIE|Safari|Konqueror#', $userAgent)) {
            return "filename=".rawurlencode($filename);
        }
        return "filename*=UTF-8''".rawurlencode($filename);
    }
    /**
     * Return the absolute file path of a media URL and an md5 checksum
     * @param string $file
     * @return array
     */
    public function getFileInfo($file)
    {
        $pathInfo = explode('/', ltrim(urldecode($file), '/'));
        $media = true;
        if ($pathInfo[0] === 'media') {
            if ($pathInfo[1] === 'file') {
                // its a physical file \\
                $pathInfo = array_slice($pathInfo, 3);
                $media = false;
            } else {
                // it's an internal one \\
                $pathInfo = array_slice($pathInfo, 2);
            }
        }
        if ($media) {
            $file = implode('/', $pathInfo);
            $title = basename($file);
            $path = str_replace(basename($file), '', $file);
            $medias = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Media');
            $media = $medias->findOneBy(['path'=>rtrim($path, '/'), 'title'=>$title, 'deleted'=>false]);
        }
        if ($media) {
            $absfile = $this->container->get('kernel')->getRootDir().$this->root_dir.$media->getPath().'/'.$media->getMediaFiles()->first()->getFilename();
            $added = $media->getMediaFiles()->first()->getAddedOn();
            if (!file_exists($absfile)) {
                return false;
            }
        } else {
            $absfile = $this->container->get('kernel')->getRootDir().$this->root_dir.implode('/', $pathInfo);
            if (!file_exists($absfile)) {
                return false;
            }
            $added = \DateTime::createFromFormat('U', filemtime($absfile));
            $title = basename($file);
        }
        $id = md5_file($absfile);
        $f = finfo_open(FILEINFO_MIME);
        $filemime = explode(';', finfo_file($f, $absfile))[0];
        $filesize = filesize($absfile);
        
        return ['file'=>$absfile, 'id'=>$id, 'type'=>$filemime, 'size'=>$filesize, 'added_on'=>$added, 'title'=>$title];
    }
}
