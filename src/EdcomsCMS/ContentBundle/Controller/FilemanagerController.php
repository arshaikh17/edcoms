<?php

namespace EdcomsCMS\ContentBundle\Controller;

use EdcomsCMS\ContentBundle\Helpers\VideoHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;

use EdcomsCMS\ContentBundle\Entity\Media;
use EdcomsCMS\ContentBundle\Entity\MediaFiles;
use EdcomsCMS\ContentBundle\Entity\MediaLinks;
use EdcomsCMS\ContentBundle\Entity\MediaTypes;

use EdcomsCMS\ContentBundle\Helpers\MediaUploader;
use EdcomsCMS\ContentBundle\Helpers\MediaBrowser;
use EdcomsCMS\ContentBundle\Helpers\FileManager;
use EdcomsCMS\ContentBundle\Helpers\APIHelper;

class FilemanagerController extends Controller
{
    private $FileManager;
    private $videoAPI;

    var $Response;
    var $path;
    var $thumb;
    var $cur_dir;
    var $cur_path;
    var $thumbs_path;
    var $parent;
    var $base_url;
    var $root_dir;
    var $subdir;
    
    var $files;
    var $n_files = 0;
    var $current_folder = ['file'=>'.'];
    var $prev_folder = ['file'=>'..'];
    var $sorted = [];
    var $current_files_number = 0;
    var $current_folders_number = 0;
    
    var $responsive_icons = ['w'=>122, 'h'=>91, 'tw'=>45, 'th'=>38];
    
    
    var $default_view;
    //active or deactive the transliteration (mean convert all strange characters in A..Za..z0..9 characters)
    var $transliteration = true;
    //convert all spaces on files name and folders name with $replace_with variable
    var $convert_spaces = false;
    //convert all spaces on files name and folders name this value
    var $replace_with = '_';
    // -1: There is no lazy loading at all, 0: Always lazy-load images, 0+: The minimum number of the files in a directory
	// when lazy loading should be turned on.
    var $lazy_loading_file_number_threshold = 0;
    
    //The filter and sorter are managed through both javascript and php scripts because if you have a lot of
    //file in a folder the javascript script can't sort all or filter all, so the filemanager switch to php script.
    //The plugin automatic swich javascript to php when the current folder exceeds the below limit of files number
    var $file_number_limit_js = 500;
    
    var $permissions = [
        //*************************
	//Permissions configuration
	//******************
	'delete_files'                            => true,
	'create_folders'                          => true,
	'delete_folders'                          => true,
	'upload_files'                            => true,
	'rename_files'                            => true,
	'rename_folders'                          => true,
	'duplicate_files'                         => true,
	'copy_cut_files'                          => true, // for copy/cut files
	'copy_cut_dirs'                           => true, // for copy/cut directories
	'chmod_files'                             => false, // change file permissions
	'chmod_dirs'                              => false, // change folder permissions
	'preview_text_files'                      => true, // eg.: txt, log etc.
	'edit_text_files'                         => true, // eg.: txt, log etc.
	'create_text_files'                       => true, // only create files with exts. defined in $editable_text_file_exts

	// you can preview these type of files if $preview_text_files is true
	'previewable_text_file_exts'              => array( 'txt', 'log', 'xml', 'html', 'css', 'htm', 'js' ),
	'previewable_text_file_exts_no_prettify'  => array( 'txt', 'log' ),

	// you can edit these type of files if $edit_text_files is true (only text based files)
	// you can create these type of files if $create_text_files is true (only text based files)
	// if you want you can add html,css etc.
	// but for security reasons it's NOT RECOMMENDED!
	'editable_text_file_exts'                 => array( 'txt', 'log', 'xml', 'html', 'css', 'htm', 'js' ),

	// Preview with Google Documents
	'googledoc_enabled'                       => true,
	'googledoc_file_exts'                     => array( 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' ),

	// Preview with Viewer.js
	'viewerjs_enabled'                        => true,
	'viewerjs_file_exts'                      => array( 'pdf', 'odt', 'odp', 'ods' ),
        
        // defines size limit for paste in MB / operation
	// set 'FALSE' for no limit
	'copy_cut_max_size'                       => 100,
	// defines file count limit for paste / operation
	// set 'FALSE' for no limit
	'copy_cut_max_count'                      => 200,
	//IF any of these limits reached, operation won't start and generate warning
        /*******************
	 * JAVA upload
	 *******************/
	'java_upload'                             => false,
	'JAVAMaxSizeUpload'                       => 200, //Gb
        
        //Show or not show folder size in list view feature in filemanager (is possible, if there is a large folder, to greatly increase the calculations)
        'show_folder_size'                        => true,
        //Show or not show sorting feature in filemanager
        'show_sorting_bar'                        => true,
        //active or deactive the transliteration (mean convert all strange characters in A..Za..z0..9 characters)
        'transliteration'                         => false,
        //convert all spaces on files name and folders name with $replace_with variable
        'convert_spaces'                          => false,
        //convert all spaces on files name and folders name this value
        'replace_with'                            => "_",

        // -1: There is no lazy loading at all, 0: Always lazy-load images, 0+: The minimum number of the files in a directory
        // when lazy loading should be turned on.
        'lazy_loading_file_number_threshold'      => 0,
        //**********************
	// Hidden files and folders
	//**********************
	// set the names of any folders you want hidden (eg "hidden_folder1", "hidden_folder2" ) Remember all folders with these names will be hidden (you can set any exceptions in config.php files on folders)
	'hidden_folders'                          => array(),
	// set the names of any files you want hidden. Remember these names will be hidden in all folders (eg "this_document.pdf", "that_image.jpg" )
	'hidden_files'                            => array( 'config.php' ),
    ];
    
    public function __construct()
    {
        $this->FileManager = new FileManager();
        $this->root_dir = $this->FileManager->GetRoot();
    }

    /**
     * Helper function to set the object paths
     */
    private function setPaths() {
        $this->path = $this->container->get('kernel')->getRootDir().$this->root_dir;
        $this->thumb = $this->container->get('kernel')->getCacheDir().'/thumbs';
    }
    
    public function dialogAction(Request $request)
    {
        $this->base_url = ((!is_null($request->server->get('HTTPS')) && $request->server->get('HTTPS') && ! in_array(strtolower($request->server->get('HTTPS')), array( 'off', 'no' ))) ? 'https' : 'http') . '://' . $request->server->get('HTTP_HOST');
        $akey = $request->query->get('akey');
        $fldr = $request->query->get('fldr');
        $popup = $request->query->get('popup');
        $crossdomain = $request->query->get('crossdomain');
        $view = is_null($request->query->get('view')) ? 0 : $request->query->get('view');
        $filter = $request->query->get('filter');
        $sort_by = $request->query->get('sort_by');
        $descending = $request->query->get('descending');
        $relativeurl = $request->query->get('relative_url');
        $type_param = (int)$request->query->get('type', 0);
        $editor = $request->query->get('editor', false);
        if ($editor === 0) {
            $editor = 'tinymce';
        }
        $field_id = $request->query->get('field_id', '');
        
        if ($type_param === 1) {
            $apply = 'apply_img';
        } else if ($type_param === 2) {
            $apply = 'apply_link';
        } else if ($type_param === 0 && $field_id === '') {
            $apply = 'apply_none';
        } else if ($type_param === 3) {
            $apply = 'apply_video';
        } else {
            $apply = 'apply';
        }
        $return_relative_url = $this->ShowRelative($relativeurl);
        $get_params = array(
            'editor' => $editor,
            'type' => $type_param,
            'popup' => $popup,
            'crossdomain' => $crossdomain,
            'field_id' => $field_id,
            'relative_url' => $return_relative_url,
            'akey' => ($akey != '' ? $akey : 'key')
        );
        
        if (!is_null($request->query->get('CKEditorFuncNum'))) {
            $get_params['CKEditorFuncNum'] = $request->query->get('CKEditorFuncNum');
            $get_params['CKEditor'] = (!is_null($request->query->get('CKEditor')) ? $request->query->get('CKEditor') : '');
        }
        $get_params['fldr'] = '';
        $get_params = http_build_query($get_params);
        
        $session = $request->getSession();
        $rf = $session->get('RF');
        if (!is_array($rf)) {
            $rf = [];
        }
        $cookie = $request->cookies;
        $this->Response = new Response();
        $this->setPaths();
        $subdir = $this->PrepFolders($fldr, $rf, $cookie);
        $this->PrepSubFolders($subdir, $rf);
        $popup = $this->DetectPopup($popup);
        $crossdomain = $this->DetectCrossDomain($crossdomain);
        $view = $this->DetectView($rf, $view);
        $filter = $this->DetectFilter($rf, $filter);
        extract($this->Sorting($rf, $sort_by, $descending));
        $session->set('RF', $rf);
        // the extensions need work! \\
        $exts = $this->GetExtensions();
        $extjoin = array_merge($exts['ext_img'], $exts['ext_file'], $exts['ext_video'], $exts['ext_music'], $exts['ext_misc']);
        $config = [
            /******************
            * AVIARY config
            *******************/
            'aviary_active'                           => true,
            'aviary_apiKey'                           => "2444282ef4344e3dacdedc7a78f8877d",
            'aviary_language'                         => "en",
            'aviary_theme'                            => "light",
            'aviary_tools'                            => "all",
            'aviary_maxSize'                          => "1400",
            'icon_theme' => "ico",
            //*******************************************
            //Images limit and resizing configuration
            //*******************************************

            // set maximum pixel width and/or maximum pixel height for all images
            // If you set a maximum width or height, oversized images are converted to those limits. Images smaller than the limit(s) are unaffected
            // if you don't need a limit set both to 0
            'image_max_width'                         => 0,
            'image_max_height'                        => 0,
            'image_max_mode'                          => 'auto',
            /*
            #  $option:  0 / exact = defined size;
            #            1 / portrait = keep aspect set height;
            #            2 / landscape = keep aspect set width;
            #            3 / auto = auto;
            #            4 / crop= resize and crop;
             */

            //Automatic resizing //
            // If you set $image_resizing to TRUE the script converts all uploaded images exactly to image_resizing_width x image_resizing_height dimension
            // If you set width or height to 0 the script automatically calculates the other dimension
            // Is possible that if you upload very big images the script not work to overcome this increase the php configuration of memory and time limit
            'image_resizing'                          => false,
            'image_resizing_width'                    => 0,
            'image_resizing_height'                   => 0,
            'image_resizing_mode'                     => 'auto', // same as $image_max_mode
            'image_resizing_override'                 => false,
            // If set to TRUE then you can specify bigger images than $image_max_width & height otherwise if image_resizing is
            // bigger than $image_max_width or height then it will be converted to those values

            //******************
            // Default layout setting
            //
            // 0 => boxes
            // 1 => detailed list (1 column)
            // 2 => columns list (multiple columns depending on the width of the page)
            // YOU CAN ALSO PASS THIS PARAMETERS USING SESSION VAR => $_SESSION['RF']["VIEW"]=
            //
            //******************
            'default_view'                            => 0,

            //set if the filename is truncated when overflow first row
            'ellipsis_title_after_first_row'          => true,
        ];
        
        $this->PrepDirs($sort_by, $descending, $config);
        $directory = [
            'files'=>$this->files,
            'n_files'=>$this->n_files,
            'current_folder'=>$this->current_folder,
            'prev_folder'=>$this->prev_folder,
            'sorted'=>$this->sorted,
            'current_files_number'=>$this->current_files_number,
            'current_folders_number'=>$this->current_folders_number
        ];

        return $this->render('EdcomsCMSTemplatesBundle:Filemanager:dialog.html.twig',
        array_merge([
            'current_uri'=>$request->getUri(),
            'get_params'=>$get_params,
            'apply'=>$apply,
            'field_id'=>$field_id,
            
            /*
            |--------------------------------------------------------------------------
            | Maximum upload size
            |--------------------------------------------------------------------------
            |
            | in Megabytes
            |
            */
            'MaxSizeUpload' => 400,
            // For a list of options see: https://developers.aviary.com/docs/web/setup-guide#constructor-config
            'aviary_defaults_config' => array(
                'apiKey'     => $config['aviary_apiKey'],
                'language'   => $config['aviary_language'],
                'theme'      => $config['aviary_theme'],
                'tools'      => $config['aviary_tools'],
                'maxSize'    => $config['aviary_maxSize']
            ),
            
            'popup'=>$popup,
            'crossdomain'=>$crossdomain,
            'editor'=>$editor,
            'view'=>$view,
            'subdir'=>$subdir,
            'field_id'=>$field_id,
            'type_param'=>$type_param,
            'type'=>$type_param,
            'cur_dir'=>'',
            'thumbs_path'=>'',
            'base_url'=>'',
            'cur_path'=>$this->cur_path,
            'base_url_true'=>$this->BaseURL($request),
            'dirname'=>str_replace('.','',dirname($subdir)),
            'thumb_dirname'=>dirname($this->thumbs_path.$subdir).'/',
            'return_relative_url'=>($return_relative_url) ? 1 : 0,
            'lazy_loading_file_number_threshold'=>$this->lazy_loading_file_number_threshold,
            'file_number_limit_js'=>$this->file_number_limit_js,
            'sort_by'=>$sort_by,
            'descending'=>($descending) ? 1 : 0,
            'current_url'=>$request->getPathInfo(),
            'clipboard'=>$this->GetClipboard($request),
            'filter'=>$filter
        ], $config, $exts, ['ext_join'=>$extjoin], $this->permissions, $directory));
    }
    public function uploadAction(Request $request)
    {
        $submit = $request->get('submit');
        if (!is_null($request->files)) {
            $root = $this->container->get('kernel')->getRootDir().$this->root_dir . '/';//TODO - update code to remove the trailing slash
            $file = $request->files->get('file');
            $isAdmin = true;
            if (!is_null($file) && $file->isValid()) {
                
                $fullPath = $request->get('path').$request->get('fldr');
                $em = $this->getDoctrine()->getManager();
                if (!$this->FileManager->checkExists($em, $fullPath, $file->getClientOriginalName())) {
                    $user = $this->get('security.token_storage')->getToken()->getUser();
                    // TODO - allow for different targets based on a POST variable from the filemanager \\
                    $mediaUploader = $this->get('edcoms.content.helper.media.uploader');
                    $upload = $mediaUploader->UploadFile($em, $root, $file, 'admin', $user, $fullPath, $isAdmin);
                    extract($upload);
                    // TODO implement correct exception
                    if($status === 200) {
                        $videoFile = stripos($file->getClientMimeType(), 'video');
                        if($videoFile !== false) {
                            //VIdeo API upload
                            if (isset($resp['id']) && !empty($resp['id'])) {
                                //Use URL of the file for uploading to API
                                $this->base_url = $this->BaseURL($request);
                                $fileLocation = $this->base_url . "/media/view/" . $fullPath . $file->getClientOriginalName();
                                $fileOrigName = $file->getClientOriginalName();
                                $this->videoAPI = $this->get('videohelper');
                                $inputformats = $this->videoAPI->getInputFormats();
                                $data = $this->videoAPI->uploadVideo($inputformats, $fileOrigName, "Description $fileOrigName", $fileLocation);
                                if ($data->data->success) {
                                    $uploadVideoID = $data->data->video_id;
                                    $em = $this->getDoctrine()->getManager('edcoms_cms');
                                    $media = $em->getRepository('EdcomsCMSContentBundle:Media')->findOneBy(['id' => $upload['resp']['id'], 'deleted' => 0]);
                                    $media->setVideoId($uploadVideoID);
                                    $em->persist($media);
                                    $em->flush();
                                } else {
                                    throw new \Exception($data->data->error);
                                }
                            }
                        }
                    }
                    else {
                        throw new \Exception($resp['error'], $status);
                    }
                } else {
                    throw new \Exception("File exists", 406);
                }
            } else {
                throw new \Exception($file->getError(), 500);
            }
        }
        $params = [
            'type'=>$request->get('type'),
            'lang'=>$request->get('lang'),
            'popup'=>$request->get('popup'),
            'field_id'=>$request->get('field_id'),
            'fldr'=>$request->get('fldr')
        ];
        $query = http_build_query($params);
        return $this->redirect("/cms/filemanager/dialog.php?$query");
    }
    private function PrepFolders($fldr, &$rf, $cookie)
    {
        if (isset($fldr)
            && !empty($fldr)
            && strpos($fldr,'../') === FALSE
            && strpos($fldr,'./') === FALSE)
        {
            $subdir = urldecode(trim(strip_tags($fldr),"/") ."/");
            $rf['filter'] = '';
        }
        else { $subdir = ''; }

        if ($subdir == "")
        {
           if (!empty(['last_position'])
                && strpos($cookie->get('last_position'),'.') === FALSE) {
                $subdir= trim($cookie->get('last_position'));
            }
        }
        //remember last position
        $newCookie = new Cookie('last_position',$subdir,time() + (86400 * 7));
        $this->Response->headers->setCookie($newCookie);

        if ($subdir == "/") { $subdir = ""; }
        return $subdir;
    }
    
    public function ajax_save_imgAction(Request $request)
    {
        $info = pathinfo($request->get('name'));
        if (
                strpos($request->get('path'), '/') === 0
                || strpos($request->get('path'), '../') !== false
                || strpos($request->get('path'), './') === 0
                || (strpos($request->get('path'), 'http://s3.amazonaws.com/feather') !== 0 
                && strpos($request->get('path'), 'https://s3.amazonaws.com/feather') !== 0)
                || $request->get('name') != fix_filename($request->get('path'), $this->transliteration, $this->convert_spaces, $this->replace_with)
                || ! in_array(strtolower($info['extension']), array( 'jpg', 'jpeg', 'png' ))
        ) {
            return new Response('Wrong data', 400);
        }
        $mediaUploader = $this->get('edcoms.content.helper.media.uploader');
        $image_data = $mediaUploader->GetFileByURL($request->get('url'));
        if ($image_data === false)
        {
                return new Response('Could not save image', 500);
        }
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $path = '/' . ltrim($request->get('path'), '/');//deal with paths with or without a leading slash
        if (!$this->FileManager->checkExists($em, $path, $request->get('name'))) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            // TODO - allow for different targets based on a POST variable from the filemanager \\
            $f = finfo_open(FILEINFO_MIME);
            $filemime = explode(';', finfo_buffer($f, $image_data))[0];
            list($resp, $status) = $mediaUploader->PutFile($em, $this->container->get('kernel')->getRootDir().$this->root_dir.'/', $image_data, $request->get('name'), $filemime, 'admin', $user, $request->get('path'), true);//TODO - update code to remove the trailing slash
        } else {
            throw new \Exception("File exists", 406);
        }
        return new JsonResponse($resp, $status);
    }

    public function ajax_callsAction(Request $request)
    {
        $action = $request->get('action');
        $ret = new Response();
        switch ($action) {
            case 'view':
                $type = $request->get('type');
                $this->SwitchView($request, $type);
                break;
            case 'copy_cut':
                $subaction = $request->get('sub_action');
                $path = $request->get('path');
                $rf = $request->getSession()->get('RF');
                $ret = $this->Copy($subaction, $path, $rf);
                $request->getSession()->set('RF', $rf);
                break;
        }
        return $ret;
    }
    public function executeAction(Request $request)
    {
        $action = $request->get('action');
        $em = $this->getDoctrine()->getManager();
        $ret = new Response();
        switch ($action) {
            case 'create_folder':
                $path = $request->get('path');
                $name = $request->get('name');
                $name = $this->FileManager->sanitizeFilename($name);
                if (!isset($path)) {//path not supplied
                    $ret = new Response('path not supplied', 400);
                    break;
                }
                //if name not supplied
                if (!isset($name)) {//name not supplied
                    $ret = new Response('name not supplied', 400);
                    break;
                }

                //make sure path is slashed up correctly, leading slash only unless
                //path is empty (root) in which case leave it empty
                if ($path !== '') {
                    $path = '/'.ltrim(rtrim($path, '/'), '/');
                }
                $name = '/'.ltrim(rtrim($name, '/'), '/');//make sure name has leading slash only

                //set paths ready for use
                $this->setPaths();
                //Check parent folder exists
                if (!is_dir($this->path.$path)) {
                    $ret = new Response('path directory does not exist', 404);
                } else {
        //thumbs are served from the thumb directory and do not follow the same folder structure as the main media area
//                    $this->FileManager->CreateFolder($this->path.$path.$name, $this->thumb.$path.$name);
                    $this->FileManager->CreateFolder($this->path.$path.$name);
                    $ret = new Response('Success', 200);
                }
                break;

            case 'rename_folder':
                $path = $request->get('path');//old folder name included after last slash
                $name = $request->get('name');//new name
                $name = $this->FileManager->sanitizeFilename($name);
                //if path not supplied
                if (!isset($path)) {
                    $ret = new Response('path not supplied', 400);
                    break;
                }
                //if name not supplied
                if (!isset($name)) {
                    $ret = new Response('name not supplied', 400);
                    break;
                }
                //new name cannot contain slashes
                if (strpos($name, '/')) {
                    $ret = new Response('new folder name cannot contain the / character', 400);
                }
                $path = '/'.ltrim(rtrim($path,'/'), '/');//deal with path with or without a leading slash
                //set paths ready for use
                $this->setPaths();

                //check if old directory exists
                if (!is_dir($this->path.$path)) {
                    $ret = new Response('directory does not exist', 404);
                    break;
                }
                //extract old name from path
                $position = strrpos($path, '/');
                if ($position === false) {
                    $oldName = $path;
                } else {
                    $oldName = substr($path, $position+1);
                    $path = substr($path, 0, $position);
                }
                //check if old name and new name same
                if ($oldName === $name) {
                    $ret = new Response('success', 200);
                    break;
                }
                //check to see if new directory already exists
                $name = '/'.$name;//add leading slash to name
                if (is_dir($this->path.$path.$name)) {
                    $ret = new Response('a directory already exists at that location', 400);
                    break;
                }

                //attempt rename
                $oldName = '/'.$oldName;
                if ($this->FileManager->renameFolder($em, $this->path, $path, $oldName, $name)) {
                    $ret = new Response('success', 200);
                    break;
                }

                $ret = new Response('nothing updated', 500);
                break;

            case 'delete_folder':
                $path = $request->get('path');

                //if path not supplied
                if ($path === 'undefined') {
                    $ret = new Response('Not Found', 404);
                }
                $path = '/'.ltrim($path,'/');//deal with path with or without a leading slash
                //check folder exists
                $this->setPaths();
                if (!is_dir($this->path.$path)) {
                    $ret = new Response('Not Found', 404);
                } else {
                    //Delete folder and contents
                    $return = $this->FileManager->DeleteFolder($em, $path, $this->path, $this->thumb);
                    if(isset($return['linkedContent'])) {
                        foreach ($return['linkedContent'] as $media) {
                            if (isset($media['videoId']) && !is_null($media['videoId'])) {
                                $this->videoAPI = $this->get('videohelper');
                                $data = $this->videoAPI->deleteVideo($media['videoId']);
                            }
                        }
                    }
                    $ret = new Response(json_encode($return), 200);

                }
                break;

            case 'delete_file':
                //get path and name from post vars
                $path = $request->get('path');
                //if path not supplied
                if (!isset($path)) {
                    $ret = new Response('path not supplied', 400);
                    break;
                }

                //Dissect the path variable to get the filename
                if (strpos($path, '/') !== false) {
                    //path has directories
                    $name = substr($path, strripos($path, '/')+1);
                    $path = substr($path, 0, strripos($path, '/'));
                } else {
                    //path has not directories
                    $name = $path;
                    $path = '';
                }
                $this->setPaths();
                $resp = $this->FileManager->deleteFile($em, $name, $path, $this->path, $this->thumb);
                if (is_array($resp)) {
                    if(!empty($resp['videoId'])) {
                        $this->videoAPI = $this->get('videohelper');
                        $data = $this->videoAPI->deleteVideo($resp['videoId']);
                        if ($data->data->success) {
                            $ret = new Response(json_encode($resp), 200);
                        }else{
                            $ret = new Response($data->data->error);
                        }
                    } else {
                        $ret = new Response(json_encode($resp), 200);
                    }

                } else {
                    $ret = new Response('Not found', 404);
                }

                break;

            case 'rename_file':
                //get path and name from post vars
                $path = $request->get('path');
                $newName = $request->get('name');
                //if path not supplied
                if (!isset($path)) {
                    $ret = new Response('path not supplied', 400);
                    break;
                }
                //if name not supplied
                if (!isset($newName)) {
                    $ret = new Response('name not supplied', 400);
                    break;
                }
                $this->setPaths();
                $user = $this->get('security.token_storage')->getToken()->getUser();
                $resp = $this->FileManager->renameFile($em, $newName, $path, $this->path, $user);
                if ($resp['status'])
                {
                    if(isset($resp['video_id']) && !empty($resp['video_id'])) {
                        $this->videoAPI = $this->get('videohelper');
                        $data = $this->videoAPI->renameVideo($resp['video_id'], $newName);
                        if ($data->data->success) {
                            $ret = new Response('success', 200);
                        } else {
                            $ret = new Response($data->data->error);
                        }
                    } else {
                        $ret = new Response('success', 200);
                    }

                } else {
                    $ret = new Response('file not found', 404);
                }
                break;
        }
        return $ret;
    }
    public function ajax_media_previewAction($filename) {
        $info = pathinfo($filename);
        $exts = $this->GetExtensions();
        $mediaType = " ";
        if (in_array(strtolower($info['extension']), $exts['ext_music'])) {
            $mediaType = "ext_music";
        } else if (in_array(strtolower($info['extension']), $exts['ext_video'])) {
            $mediaType = "ext_video";
        }
        return $this->render('EdcomsCMSTemplatesBundle:Filemanager:player.html.twig', array('filename' => $filename, 'media_type' => $mediaType));
    }

    public function ajax_text_previewAction(Request $request,$filename) {
         $info = pathinfo($filename);
         $this->base_url = $this->BaseURL($request);
        $data = stripslashes(htmlspecialchars(file_get_contents($this->base_url.$filename)));
        $ret = '';
        if (!in_array($info['extension'], $this->permissions['previewable_text_file_exts_no_prettify'])) {
            $ret .= '<script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js?lang=' . $info['extension'] . '&skin=sunburst"></script>';
            $ret .= '<div class="text-center"><strong>' . $info['basename'] . '</strong></div><pre class="prettyprint">' . $data . '</pre>';
        } else {
            $ret .= '<div class="text-center"><strong>' . $info['basename'] . '</strong></div><pre class="no-prettify">' . $data . '</pre>';
        }
        return new Response($ret, 200);
    }

    public function ajax_google_previewAction(Request $request, $filename)
    {
        $info = pathinfo($filename);
        $this->base_url = $this->BaseURL($request);
        $url_file = $this->base_url.$filename;
        $googledoc_url = urlencode($url_file);
        $ret = "<div class='text-center'><strong>" . $info['basename'] . "</strong></div> <iframe src='http://docs.google.com/viewer?url=" . $googledoc_url . "&embedded=true' class='google-iframe'></iframe>" ;
        return new Response($ret , 200);
    }
    
    public function ajax_viewerjs_previewAction($filename)
    {
        // TODO - implement the ajax_calls.php viewerjs_preview method \\
        $imgsrc = "/bundles/edcomscmstemplates/filemanager/js/ViewerJS/index.html#".$filename;
        $ret = '<iframe id="viewer" src="'.$imgsrc.'" allowfullscreen="" webkitallowfullscreen="" class="viewer-iframe"></iframe>';
//        $ret = '<iframe id="viewer" src="/bundles/edcomscmstemplates/filemanager/js/ViewerJS/index.html/../../../../../..'.$filename.'" allowfullscreen="" webkitallowfullscreen="" class="viewer-iframe"></iframe>';

        return new Response($ret , 200);
    }
    
    private function PrepSubFolders($subdir, &$rf)
    {
        if (!isset($rf['subfolder']))
        {
            $rf['subfolder'] = '';
        }
        if (!file_exists($this->path.'/'.$subdir)) {
            $subdir = '';
        }

        $this->cur_dir = $subdir;
        $this->cur_path = $subdir;
        $this->thumbs_path = $this->thumb;
        $this->parent = $subdir;

        $cycle = TRUE;
        $max_cycles = 50;
        $i = 0;
        while ($cycle && $i < $max_cycles){
            $i++;
            if ($this->parent==="./") {
                $this->parent="";
            }

            if ($this->parent === "") {
                $cycle = FALSE;
            } else {
                $this->parent = $this->FixDirname($this->parent)."/";
            }
        }
        if (!is_dir($this->thumbs_path)) {
            $this->FileManager->CreateFolder(FALSE, $this->thumbs_path);
        }
        $this->subdir = $subdir;
        $rf['subfolder'] = $this->subdir;
    }
    private function FixDirname($str)
    {
        return str_replace('~', ' ', dirname(str_replace(' ', '~', $str)));
    }
        private function DetectPopup($popup)
    {
        if (!is_null($popup)) {
                $popup = strip_tags($popup);
        } else {
            $popup=0;
        }
        //Sanitize popup
         return !!$popup;
    }
    private function DetectCrossDomain($crossdomain)
    {
        if (!is_null($crossdomain))
        {
           $crossdomain = strip_tags($crossdomain);
        } else {
            $crossdomain=0;
        }

        //Sanitize crossdomain
        return !!$crossdomain;
    }
    private function DetectView(&$rf, $gview)
    {
        //view type
        if(!isset($rf['view_type']))
        {
                $view = $this->default_view;
                $rf['view_type'] = $view;
        }

        if (!is_null($gview))
        {
                $view = $gview;
                $rf['view_type'] = $view;
        }

        return $rf['view_type'];
    }
    
    private function SwitchView(Request $request, $type)
    {
        $RF = $request->getSession()->get('RF');
        $RF['view_type'] = $type;
        $request->getSession()->set('RF', $RF);
    }
    
    private function DetectFilter(&$rf, $gfilter)
    {
        //filter
        $filter = '';
        if(isset($rf['filter']))
        {
                $filter = $rf['filter'];
        }

        if(isset($gfilter))
        {
                $filter = $gfilter;
        }
        return $filter;
    }
    private function Sorting(&$rf, $gsort, $gdescending)
    {
        if (!isset($rf['sort_by'])) {
            $rf['sort_by'] = 'name';
        }

        if (isset($gsort)) {
            $sort_by = $rf['sort_by'] = $gsort;
        } else {
            $sort_by = $rf['sort_by'];
        }

        if (!isset($rf['descending'])) {
                $rf['descending'] = TRUE;
        }

        if (isset($gdescending)) {
                $descending = $rf['descending'] = $gdescending===1;
        } else{
                $descending = $rf['descending'];	
        }
        return ['sort_by'=>$sort_by, 'descending'=>$descending];
    }
    public function ShowRelative($relative)
    {
        return isset($relative) && $relative == "1" ? true : false;
    }
    private function GetExtensions()
    {
        //**********************
        //Allowed extensions (lowercase insert)
        //**********************
        $ext = [
            'ext_img' => array( 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'svg' ), //Images
            'ext_file' => array( 'doc', 'docx', 'rtf', 'pdf', 'xls', 'xlsx', 'txt', 'csv', 'html', 'xhtml', 'psd', 'sql', 'log', 'fla', 'xml', 'ade', 'adp', 'mdb', 'accdb', 'ppt', 'pptx','pps', 'ppsx', 'odt', 'ots', 'ott', 'odb', 'odg', 'otp', 'otg', 'odf', 'ods', 'odp', 'css', 'ai' ), //Files
            'ext_video' => array( 'mov', 'mpeg', 'm4v', 'mp4', 'avi', 'mpg', 'wma', "flv", "webm" ), //Video
            'ext_music' => array( 'mp3', 'm4a', 'ac3', 'aiff', 'mid', 'ogg', 'wav' ), //Audio
            'ext_misc' => array( 'zip', 'rar', 'gz', 'tar', 'iso', 'dmg', 'vtt' ) //Archives
        ];
        return $ext;
    }
    private function BaseURL(Request $request)
    {
        return sprintf(
            "%s://%s",
            !is_null($request->server->get('HTTPS')) && $request->server->get('HTTPS') != 'off' ? 'https' : 'http',
            $request->server->get('HTTP_HOST')
	);
    }
    public function GetClipboard(Request $request)
    {
        $session = $request->getSession();
        $clipboard = $session->get('clipboard');
        return $clipboard;
    }
    
    public function PrepDirs($sort_by, $descending, $config)
    {
        $MediaBrowser = new MediaBrowser();
        $MediaBrowser->SetRoot($this->container->get('kernel')->getRootDir().$this->root_dir.'/');//TODO - update code to remove the trailing slash
        $MediaBrowser->SetCache($this->container->get('kernel')->getCacheDir());
        $path = ltrim($MediaBrowser->FixPath($this->subdir), '/');
        $media = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Media');
        $tgtPath = $this->container->get('kernel')->getRootDir().$this->root_dir.'/'.$path;//TODO - update code to remove the trailing slash

        $this->items = $MediaBrowser->ListDirectory($tgtPath, $media, $path);

        $this->n_files = count($this->items);
        $this->current_folder = ['file'=>'.'];
        $this->prev_folder = ['file'=>'..'];
        $this->sorted = [];
        $this->current_files_number = 0;
        $this->current_folders_number = 0;
        if (is_array($this->items)) {
            foreach ($this->items as $k=>$file) {
                if ($file->type === 'directory') {
                    $date = $file->modified;
                    if ($this->permissions['show_folder_size']){
                        $size = $file->size;
                        $nfiles = $file->num_files;
                        $nfolders = $file->num_folders;
                        $this->current_folders_number++;
                    } else {
                        $size = 0;
                    }
                    $file_ext = 'dir';
                    $this->sorted[$k] = array(
                        'file'=>$file->title,
                        'path'=>$file->path.'/',
                        'file_lcase'=>strtolower($file->title),
                        'date'=>$date,
                        'size'=>$size,
                        'nfiles'=>$nfiles,
                        'nfolders'=>$nfolders,
                        'extension'=>$file_ext,
                        'extension_lcase'=>strtolower($file_ext),
                        'friendly_size'=>$file->friendly_size,
                        'thumb'=>(file_exists($this->container->get('kernel')->getRootDir().'/../web/bundles/edcomscmstemplates/filemanager/img/'.$config['icon_theme'].'/'.strtolower($file_ext).'.jpg')) ? '/bundles/edcomscmstemplates/filemanager/img/'.$config['icon_theme'].'/'.strtolower($file_ext).'.jpg' : '/bundles/edcomscmstemplates/filemanager/img/'.$config['icon_theme'].'/default.jpg'
                    );
                } else {
                    $this->current_files_number++;
                    $date = $file->modified;
                    $size = $file->size;
                    $file_ext = substr(strrchr($file->title,'.'),1);

                    if(stripos($file->type,"video") === 0) {
                        $videoId = $file->video_id;
                        $this->sorted[$k] = array('path'=>$file->path, 'thumb_path'=>$file->thumb, 'file'=>$file->title,'file_lcase'=>strtolower($file->title),'date'=>$date,'size'=>$size,'extension'=>$file_ext,'extension_lcase'=>strtolower($file_ext), 'friendly_size'=>$file->friendly_size, 'thumb'=>(file_exists($this->container->get('kernel')->getRootDir().'/../web/bundles/edcomscmstemplates/filemanager/img/'.$config['icon_theme'].'/'.strtolower($file_ext).'.jpg')) ? '/bundles/edcomscmstemplates/filemanager/img/'.$config['icon_theme'].'/'.strtolower($file_ext).'.jpg' : '/bundles/edcomscmstemplates/filemanager/img/'.$config['icon_theme'].'/default.jpg','video_id'=>$videoId);
                    } else {
                        $this->sorted[$k] = array('path'=>$file->path, 'thumb_path'=>$file->thumb, 'file'=>$file->title,'file_lcase'=>strtolower($file->title),'date'=>$date,'size'=>$size,'extension'=>$file_ext,'extension_lcase'=>strtolower($file_ext), 'friendly_size'=>$file->friendly_size, 'thumb'=>(file_exists($this->container->get('kernel')->getRootDir().'/../web/bundles/edcomscmstemplates/filemanager/img/'.$config['icon_theme'].'/'.strtolower($file_ext).'.jpg')) ? '/bundles/edcomscmstemplates/filemanager/img/'.$config['icon_theme'].'/'.strtolower($file_ext).'.jpg' : '/bundles/edcomscmstemplates/filemanager/img/'.$config['icon_theme'].'/default.jpg');
                    }
                    $exts = $this->GetExtensions();
                    $this->sorted[$k]['image_info'] = ['width'=>null, 'height'=>null];
                    if (in_array(strtolower($file_ext), $exts['ext_img'])) {
                        list($img_width, $img_height, $img_type, $attr)=@getimagesize($file->absfile);
                        $this->sorted[$k]['image_info'] = ['width'=>$img_width, 'height'=>$img_height];
                    } else if (in_array(strtolower($file_ext), $exts['ext_video'])) {
                        $this->sorted[$k]['path'] = $file->video_id.'.'.$file_ext;
                    }
                }
            }
        }
        // Should lazy loading be enabled
        $this->permissions['lazy_loading_enabled'] = ($this->permissions['lazy_loading_file_number_threshold'] === 0 || $this->permissions['lazy_loading_file_number_threshold'] != -1 && $this->n_files > $this->permissions['lazy_loading_file_number_threshold']) ? true : false;

        switch($sort_by){
            case 'date':
                usort($this->sorted, array(&$this, 'dateSort'));
                break;
            case 'size':
                usort($this->sorted, array(&$this, 'sizeSort'));
                break;
            case 'extension':
                usort($this->sorted, array(&$this, 'extensionSort'));
                break;
            default:
                usort($this->sorted, array(&$this, 'filenameSort'));
                break;
        }

        if (!$descending) {
            $this->sorted = array_reverse($this->sorted);
        }

        $this->files=array_merge(array($this->prev_folder),array($this->current_folder),$this->sorted);
        
    }
    private function filenameSort($x, $y) {
        return $x['file_lcase'] <  $y['file_lcase'];
    }
    private function dateSort($x, $y) {
        return $x['date'] <  $y['date'];
    }
    private function sizeSort($x, $y) {
        return $x['size'] <  $y['size'];
    }
    private function extensionSort($x, $y) {
        return $x['extension_lcase'] <  $y['extension_lcase'];
    }
}
