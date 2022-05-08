<?php
namespace EdcomsCMS\ContentBundle\Helpers;

use EdcomsCMS\ContentBundle\Entity\Media;
use EdcomsCMS\ContentBundle\Entity\MediaFiles;
use EdcomsCMS\ContentBundle\Entity\MediaLinks;
use EdcomsCMS\ContentBundle\Entity\MediaTypes;
use Symfony\Component\Filesystem\Filesystem;

use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class FileManager {

    // TODO - these functions should be configured via CONFIG
    public function GetRoot()
    {
         return '/Resources/files';
    }

    public function getThumb() {
        return '/thumbs';
    }

    /**
     * Check if a given directory is empty on the filesystem.
     * Function takes in to account . & .. so will return empty if
     * these are the only contents of the directory
     *
     * @param $path - full path to directory
     * @return bool
     */
    public function isEmptyDirectory($path) {
        return (($files = scandir($path)) && count($files) <= 2);
    }

    /**
     * Check if media exists in DB.
     *
     * @param $em - Entity Manager
     * @param $path - path to file
     * @param $file - filename
     * @param $hashedFilename - bool, set to true if looking for hashed filename
     * @return bool
     */
    public function checkExists($em, $path, $file, $hashedFilename=false)
    {
        if ($hashedFilename) {
            $mediaFilesRepo = $em->getRepository('EdcomsCMSContentBundle:MediaFiles');
            $resp = $mediaFilesRepo->findOneBy(['filename' => $file]);
        } else {
            $mediasRepo = $em->getRepository('EdcomsCMSContentBundle:Media');
            $resp = $mediasRepo->findOneBy([
                'title'=>$file,
                'path'=>$path,
                'deleted'=>false
            ]);
        }

        if ($resp) {// means it exists \\
            return true;
        }
        return false;
    }

    /**
     * Check if given media has any links to content
     *
     * @param $em - Entity Manager
     * @param $media - media entity to check
     * @return bool
     */
    public function hasLinkedContent($em, $media) {
        return empty($this->getMediaLinks($em, $media)) ? false: true;
    }

    /**
     * Get a list of content a media item is linked to.
     *
     * @param $mediaLinks
     * @return array of linked content items, empty array if no links found
     */
    public function getLinkedContent($em, $mediaLinks) {

        //if media links translate into content items
        $contentItems = [];
            if (!empty($mediaLinks)) {
                $contentRepository = $em->getRepository('EdcomsCMSContentBundle:Content');
                foreach ($mediaLinks as $mediaLink) {
                    $content = $contentRepository->findOneById($mediaLink->getContentID());
                    $contentItems[] = $content;
            }
        }

        return $contentItems;
    }

    /**
     * Get a list of mediaLink objects for a given media object
     *
     * @param $em - Entity Manager
     * @param $media - media entity to get links for
     * @return mixed - array, empty if no links
     */
    private function getMediaLinks($em, $media) {

        if (!is_a($media, 'EdcomsCMS\ContentBundle\Entity\Media')) {
            throw new \InvalidArgumentException('parameter 2 must be of type Media. Type '
                .get_class($media). ' given');
        }

        //Get media links
        $mediaLinksRepository = $em->getRepository('EdcomsCMSContentBundle:MediaLinks');
        return $mediaLinksRepository->findBy(['mediaID' => $media->getId()]);
    }

    /**
     * Delete a media item from the DB and filesystem
     *
     * @param $em - Entity Manager
     * @param $file - filename
     * @param $path - path to file relative to the base path
     * @param $basePath - path in project to storage location
     * @param $thumbPath - path in project to store thumbs
     * @param $hashedFilename - bool, set to true if looking for hashed filename
     * @return array - If successful the message will contain an array of linked content information
     * or an empty array if no links exist. Returns false if file not found in DB.
     */
    public function deleteFile($em, $file, $path, $basePath, $thumbPath, $hashedFilename=false) {

        //check file exists
        if (!$this->checkExists($em, $path, $file, $hashedFilename)) {
            return false;

        } else {//delete file

            //Get entities
            if ($hashedFilename) {
                $mediaFilesRepo = $em->getRepository('EdcomsCMSContentBundle:MediaFiles');
                $mediaFile = $mediaFilesRepo->findOneBy(['filename' => $file]);
                $media = $mediaFile->getMedia();
            } else {
                $medias = $em->getRepository('EdcomsCMSContentBundle:Media');
                $media = $medias->findOneBy([
                    'title'=>$file,
                    'path'=>$path,
                    'deleted'=>false
                ]);
                $mediaFilesRepo = $em->getRepository('EdcomsCMSContentBundle:MediaFiles');
                $mediaFile = $mediaFilesRepo->findOneBy(['media'=>$media->getId()]);
            }
            $linkedContentResp['videoId'] = $media->getVideoId();
            //check for content links
            //Get media links
            $mediaLinks = $this->getMediaLinks($em, $media);
            $linkedContentResp['linkedContent'] = []; //
            if (!empty($mediaLinks)) {
                $linkedContent = $this->getLinkedContent($em, $mediaLinks);//returns empty array if no links found
                foreach($linkedContent as $content) {
                    $linkedContentResp['linkedContent'][] = ['id'=>$content->getId(), 'title'=>$content->getTitle() ];
                }

                foreach($mediaLinks as $mediaLink) {
                    $em->remove($mediaLink);
                }
            }

            //delete file on filesystem
            $fileHash = '/'.$mediaFile->getFilename();//add leading slash
            $filePath = '/'.ltrim(rtrim($media->getPath(), '/'),'/');//deal with path with dirty slashes, should have leading slash only
            if (file_exists($basePath.$filePath.$fileHash)) {
                unlink($basePath.$filePath.$fileHash);
            }
            //delete thumb
            if (file_exists($thumbPath.$fileHash)) {
                unlink($thumbPath.$fileHash);
            }

            //delete form DB
            $em->remove($mediaFile);
            //mark media as deleted
            $media->setDeleted(true);
            $em->flush();

            return $linkedContentResp;
        }
    }

    /**
     * Rename a file in the DB and on the file system
     *
     * @param $em - Entity Manager
     * @param $newName - name to change file to
     * @param $path - path to file
     * @param $basePath - path in project to storage location
     * @param $user - User changing filename
     * @return bool - true on success, false if file not found in DB
     */
    public function renameFile($em, $newName, $path, $basePath, $user) {

        //check file exists
        $position = strrpos($path, '/');
        //check if the file is in root or not
        if ($position) {
            $file = substr($path, $position + 1);
            $path = substr($path, 0, $position);
        } else {
            $file = $path;
            $path = "";
        }
        //avoid renaming the extension
        $extensionPos = strrpos($file, ".");
        $extension = substr($file, $extensionPos+1);
        //force new name to have same extension as the old file
        $newName = substr($newName, 0 , (strrpos($newName, ".")));
        $newName = $this->sanitizeFilename($newName);
        $newName .=".$extension";
        if (!$this->checkExists($em, $path, $file, false)) {
            $resp = ['status' => false];
        } elseif ($newName === $file) {//new filename same as old filename
            $resp = ['status' => true];
        } else {//rename file
            //Get entities
            $medias = $em->getRepository('EdcomsCMSContentBundle:Media');
            /** @var Media $media */
            $media = $medias->findOneBy([
                'title'=>$file,
                'path'=>$path,
                'deleted'=>false
            ]);
            //rename in db
            $media->setModifiedBy($user);
            $media->setModifiedOn(new \DateTime());
            $media->setTitle($newName);
            $em->flush();
            $resp = [
                'id'=>$media->getId(),
                'video_id'=>$media->getVideoId(),
                'status'=>true
            ];
        }

        return $resp;
    }

    /**
     * Create a new folder on the file system
     *
     * @param bool|false $path
     * @param bool|false $path_thumbs
     * @return bool - true if created or false if not created
     */
    public function CreateFolder($path=false, $path_thumbs=false)
    {
        $oldumask = umask(0);
        $success = false;
        if ($path && ! file_exists($path))
        {
            $success = mkdir($path, 0755, true);
        } // or even 01777 so you get the sticky bit set
        if ($path_thumbs && ! file_exists($path_thumbs))
        {
            $success = mkdir($path_thumbs, 0755, true) or die("$path_thumbs cannot be found");
        } // or even 01777 so you get the sticky bit set
        umask($oldumask);

        return $success;
    }

    /**
     * Delete a folder and all contents.
     * Any files not managed through the file manager will not be deleted,
     * but these will be included as entries in the returned errors array keyed by filename.
     * If any files were linked to content these content links will be returned in the
     * linkedContent array keyed by filename.
     *
     * @param $em - Entity Manager
     * @param $path - path of folder (including folder)
     * @param $basePath - path in project to storage location
     * @param $thumbPath - path in project to store thumbs
     * @return array
     */
    public function DeleteFolder($em, $path, $basePath, $thumbPath)
    {
        //Get files and folders on filesystem
        $path = '/'.ltrim($path,'/');//deal with path with or without a leading slash
        //get iterator
        $containingFolder = $basePath.$path;
        $it = new RecursiveDirectoryIterator($containingFolder, RecursiveDirectoryIterator::SKIP_DOTS);
        //get all sub files
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

        //init return arrays
        $errors = [];
        $contentLinks = [];

        foreach($files as $file) {
            if (!$file->isDir()) {//is a file
                $hashedFileName = $file->getFileName();
                $mediaFilesRepo = $em->getRepository('EdcomsCMSContentBundle:MediaFiles');
                $mediaFile = $mediaFilesRepo->findOneBy(['filename' => $hashedFileName]);
                if ($mediaFile) {
                    $media = $mediaFile->getMedia();
                    $fileName = $media->getTitle();
                } else {
                    $regex = '#^'.rtrim($basePath, '/').'#';
                    $filePathName = preg_replace($regex, '', $file->getPath());//create filename with relative path
                    $errors[$filePathName] = 'File not managed through file manager';
                }
                //Delete files from DB
                $delete = $this->deleteFile($em, $hashedFileName, $path, $basePath, $thumbPath, $thumbPath, true);
                if (!$delete) {//delete function returned false
                    $regex = '#^'.rtrim($basePath, '/').'#';
                    $filePathName = preg_replace($regex, '', $file->getPath());//create filename with relative path
                    $errors[$filePathName] = 'File not managed through file manager';
                } else {//if delete worked get return any content links
                    if (isset($fileName)) {
                        $contentLinks[$fileName] = $delete;
                    }
                }
            } elseif ($file->isDir() && $this->isEmptyDirectory($file->getRealPath())) {//check is a directory and is empty
                rmdir($file->getRealPath());
            }
        }

        //delete the containing folder
        if (is_dir($containingFolder) && $this->isEmptyDirectory($containingFolder)) {
            rmdir($containingFolder);
        }

        return [ 'linkedContent' => $contentLinks, 'errors' => $errors];
    }

    /**
     * Rename a directory in the DB and on the filesystem
     *
     * @param $em - Entity Manager
     * @param $rootPath - string
     * @param $path - string
     * @param $oldName - string
     * @param $name - string
     * @return bool
     */
    public function renameFolder($em, $rootPath, $path, $oldName, $name) {
        $mediaRepo = $em->getRepository('EdcomsCMSContentBundle:Media');
        $mediaRepo->renamePath(ltrim($path.$oldName, '/'), ltrim($path.$name,'/'));

        //rename the folder on the filesystem
        if (rename($rootPath.$path.$oldName, $rootPath.$path.$name)) {
            return true;
        }

        return false;
    }

    public function sanitizeFilename($fileName, $separator = '_') {
        // Removes accents
        $fileName = @iconv('UTF-8', 'us-ascii//TRANSLIT', $fileName);
        // Removes all characters that are not separators, letters, numbers, dots or whitespaces
        $fileName = preg_replace("/[^ a-zA-Z" . preg_quote($separator) . "\d\.\s]/", '', $fileName);
        // Replaces all successive separators into a single one
        $fileName = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $fileName);
        // Trim beginning and ending seperators
        $fileName = trim($fileName, $separator);
        return $fileName;
    }

}
