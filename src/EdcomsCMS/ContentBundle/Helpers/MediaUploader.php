<?php
namespace EdcomsCMS\ContentBundle\Helpers;

use Doctrine\ORM\EntityManager;
use EdcomsCMS\ContentBundle\Entity\Media;
use EdcomsCMS\ContentBundle\Entity\MediaFiles;
use EdcomsCMS\ContentBundle\Entity\MediaLinks;
use EdcomsCMS\ContentBundle\Entity\MediaTypes;
use EdcomsCMS\ContentBundle\Service\MediaUrlGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Router;
use Symfony\Component\Filesystem\Filesystem;

class MediaUploader {

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var MediaUrlGenerator
     */
    private $mediaUrlGenerator;

    /**
     * @var VideoHelper
     */
    private $videoHelper;

    public function __construct(EntityManager $entityManager, $rootDir,
                                    MediaUrlGenerator $mediaUrlGenerator, VideoHelper $videoHelper)
    {
        $filemanager = new FileManager();
        $this->em = $entityManager;
        $this->rootDir = sprintf('%s/%s',$rootDir,$filemanager->GetRoot());
        $this->mediaUrlGenerator = $mediaUrlGenerator;
        $this->videoHelper = $videoHelper;
    }

    public function uploadMedia(Media $media){
        $resp = $this->UploadFile($this->em, $this->rootDir, $media->getAttachment(), $media->getTarget(), $media->getAddedBy(), $media->getPath(), false, $media);
        if(isset($resp['status']) && $resp['status']==500){
            throw new \Exception(sprintf('There was an error during upload with message: %s - %s', $resp['resp']['error'], $resp['resp']['message']));
        }
        if(isset($resp['status']) && $resp['status'] === 200) {
            $file = $media->getAttachment();
            $mimeType = $file instanceof UploadedFile ? $file->getClientMimeType() : $file->getMimeType();
            $videoFile = stripos($mimeType, 'video');
            if($videoFile !== false) {
                if (isset($resp['resp']['id']) && !empty($resp['resp']['id'])) {
                    $extension = $file instanceof UploadedFile ? $file->getClientOriginalExtension() : $file->guessExtension();
                    $fileLocation = sprintf('%s.%s', tempnam(sys_get_temp_dir(), 'video_upload'), $extension);
                    $fs = new Filesystem();
                    $fs->copy(sprintf('%s/%s',$this->rootDir,$media->getMediaFiles()->first()->getFullPath()), $fileLocation);

                    $filename = $file instanceof UploadedFile ? $file->getClientOriginalName() : $file->getFilename();
                    $inputformats = $this->videoHelper->getInputFormats();
                    $data = $this->videoHelper->uploadVideo($inputformats, $filename, "Description $filename", $fileLocation);
                    if (property_exists($data->data, 'success') && $data->data->success) {
                        $uploadVideoID = $data->data->video_id;
                        $media = $this->em->getRepository(Media::class)->findOneBy(['id' => $resp['resp']['id'], 'deleted' => 0]);
                        $media->setVideoId($uploadVideoID);
                        $this->em->persist($media);

                        $media->setAttachment(null);
                        $this->em->flush();
                    } else {
                        throw new \Exception($data->data->error);
                    }
                }
            }
        }
    }


    public function UploadFile($em, $root, $file, $target, $user, $fullPath, $isAdmin, Media $media=null)
    {
        $uploadWithAttachment = true;
        if($file instanceof UploadedFile){
            $newName = time().md5($fullPath.$file->getClientOriginalName());
            $filesize = $file->getClientSize();
            $mediaTitle = ((!$isAdmin) ? time() : '') . $file->getClientOriginalName();
            $filemime = $file->getClientMimeType();
            $uploadWithAttachment = false;
        }elseif ($file instanceof File){
            $newName = time().md5($fullPath.$file->getFilename());
            $filesize = $file->getSize();
            $mediaTitle = ((!$isAdmin) ? time() : '') . $media->getTitle() ?: $file->getFilename();
            $filemime = $file->getMimeType();
        }else{
            throw new \Exception('Media attachment must be of type (%s,%s)',UploadedFile::class, File::class);
        }

        $Media = $media ?: new Media();
        $Media->setUploadedWithAttachment($uploadWithAttachment);
        $Media->setPath(rtrim($fullPath, '/'));
        $Media->setTitle($mediaTitle);

        $mediaFile = new MediaFiles();
        if (!is_null($user)) {
            $mediaFile->setAddedBy($user);
            $Media->setAddedBy($user);
        }
        $mediaTypes = $em->getRepository('EdcomsCMSContentBundle:MediaTypes');

        if ($filemime === 'application/octet-stream') {
            // try detecting it from the server \\
            $f = finfo_open(FILEINFO_MIME);
            $filemime = explode(';', finfo_file($f, $file->getRealPath()))[0];
        }
        $mediaType = $mediaTypes->findBy(['target'=>$target, 'filetype'=>$filemime]);
        // if we are admin, we can search for wildcard too if they've been added \\
        if ($isAdmin && !$mediaType) {
            $wildcardMime = preg_replace('/[^\/]*$/', '*', $filemime);
            $mediaType = $mediaTypes->findBy(['target'=>$target, 'filetype'=>$wildcardMime]);
            if (!$mediaType) {
                $mediaType = $mediaTypes->findBy(['target'=>$target, 'filetype'=>'*/*']);
            }
        }
        if (!$mediaType) {
            $resp = [
                'error'=>'invalid_filetype',
                'message'=>$filemime,
                'status'=>0
            ];
            $status = 500;
        } else {
            $mediaFile->setAddedOn(new \DateTime());
            $mediaFile->setFilename($newName);
            $mediaFile->setFilesize($filesize);
            $mediaFile->setType($mediaType[0]);
            $Media->addMediaFile($mediaFile);
            $Media->setAddedOn(new \DateTime());

            $asda = rtrim($root, '/') . '/' . ltrim($fullPath, '/');
            // path sanitised to avoid extra slashes.
            $loc = $file->move(rtrim($root, '/') . '/' . ltrim($fullPath, '/'), $newName);
            if ($loc) {
                if (!$Media->getId()) {
                    $em->persist($Media);
                }
                $Media->setAttachment($loc);
                $em->flush();
                $resp = [
                    'id'=>$Media->getId(),
                    'status'=>1
                ];
                $status = 200;
            } else {
                $resp = [
                    'error'=>$file->getError(),
                    'status'=>0
                ];
                $status = 500;
            }
        }
        return ['resp'=>$resp, 'status'=>$status];
    }


    public function PutFile($em, $root, $file_string, $filename, $filemime, $target, $user, $fullPath, $isAdmin)
    {
        
        $newName = time().md5($fullPath.$filename);
        if (function_exists('mb_strlen')) {
            $filesize = mb_strlen($file_string, '8bit');
        } else {
            $filesize = strlen($file_string);
        }
        $Media = new Media();
        $Media->setPath($fullPath);
        $Media->setTitle(((!$isAdmin) ? time() : '') . $filename);

        $mediaFile = new MediaFiles();
        if (!is_null($user)) {
            $mediaFile->setAddedBy($user);
            $Media->setAddedBy($user);
        }
        $mediaTypes = $em->getRepository('EdcomsCMSContentBundle:MediaTypes');
        $mediaType = $mediaTypes->findBy(['target'=>$target, 'filetype'=>$filemime]);
        // if we are admin, we can search for wildcard too if they've been added \\
        if ($isAdmin && !$mediaType) {
            $wildcardMime = preg_replace('/[^\/]*$/', '*', $filemime);
            $mediaType = $mediaTypes->findBy(['target'=>$target, 'filetype'=>$wildcardMime]);
            if (!$mediaType) {
                $mediaType = $mediaTypes->findBy(['target'=>$target, 'filetype'=>'*/*']);
            }
        }
        if (!$mediaType) {
            $resp = [
                'error'=>'invalid_filetype',
                'message'=>$filemime,
                'status'=>0
            ];
            $status = 500;
        } else {
            $mediaFile->setAddedOn(new \DateTime());
            $mediaFile->setFilename($newName);
            $mediaFile->setFilesize($filesize);
            $mediaFile->setType($mediaType[0]);
            $Media->addMediaFile($mediaFile);
            $Media->setAddedOn(new \DateTime());
            $loc = file_put_contents($root.'/Resources/files/'.$fullPath.$newName, $file_string);
            if ($loc !== false) {
                $em = $this->getDoctrine()->getManager('edcoms_cms');
                $em->persist($Media);
                $em->flush();
                $resp = [
                    'id'=>$Media->getId(),
                    'status'=>1
                ];
            } else {
                $resp = [
                    'error'=>'Could not save',
                    'status'=>0
                ];
                $status = 500;
            }
        }
        return ['resp'=>$resp, 'status'=>$status];
    }
    public function GetFileByURL($url)
    {
        if (ini_get('allow_url_fopen')) {
            return file_get_contents($url);
        }
        if ( ! function_exists('curl_version')) {
            return false;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
