<?php

namespace EdcomsCMS\ContentBundle\Helpers;
use EdcomsCMS\ContentBundle\Entity\Media;

/**
 * Description of MediaBrowser
 *
 * @author richard
 */
class MediaBrowser {
    protected $items;
    protected $filenames = [];
    protected $CacheDir;
    protected $RootDir;
    private $MAXW = 500;
    private $MAXH = 500;
    public function SetRoot($root)
    {
        $this->RootDir = $root;
    }
    public function SetCache($cache)
    {
        $this->CacheDir = $cache;
    }
    public function ListDirectory($tgtPath, $media, $path)
    {
        if (!is_dir($tgtPath) && $tgtPath === $this->RootDir) {
            mkdir($tgtPath, 0777, true);
        }
        if (is_dir($tgtPath)) {
            if ($dh = opendir($tgtPath)) {
                $files = $media->findBy([
                    'path'=>rtrim($path, '/'),
                    'deleted'=>false
                ]);
                array_walk($files, array(&$this, 'AddFile'));
                while (false !== ($file = readdir($dh))) {
                    $absfile = $tgtPath.$file;
                    if ($file !== '.' && $file !== '..' && is_dir($absfile)) {
                        // this is a directory \\
                        list($size,$nfiles,$nfolders) = $this->FolderInfo($absfile);
                        $this->items[] = (object)['title'=>$file, 'path'=>$path.$file, 'type'=>'directory', 'modified'=>filemtime($absfile), 'children'=>$this->DirHasChildren($absfile), 'size'=>$size, 'num_files'=>$nfiles, 'num_folders'=>$nfolders, 'friendly_size'=>$this->MakeSize($size)];
                    } else if (strpos($file, '.') > 0 && $file !== '.' && $file !== '..' && is_file($absfile) && array_search($file, $this->filenames) === false) {
                        // this is a physical file that exists but isn't in the DB \\
                        $f = finfo_open(FILEINFO_MIME);
                        $filemime = explode(';', finfo_file($f, $absfile))[0];
                        $size = filesize($absfile);
                        $this->items[] = (object)['path'=>'/media/file/view/'.ltrim($path.$file, '/'), 'thumb'=>$this->GenerateFileThumb($absfile, $filemime, $path), 'title'=>$file, 'type'=>$filemime, 'modified'=>filemtime($absfile), 'size'=>$size, 'friendly_size'=>$this->MakeSize($size), 'absfile'=>$absfile];
                    }
                }
            }
        }
        return $this->items;
    }
    /**
     * 
     * @param Media $media
     * @return void
     * This method is used to add a Media item to the list of items in the current directory view
     */
    private function AddFile(Media $media)
    {
        $file = $media->getMediaFiles()->first();
        $fileinfo = [];
        $media->getMediaFiles()->forAll(function($i, $item) use (&$fileinfo) {
            $this->filenames[] = $item->getFilename();
            $fileinfo[$item->getId()] = (object)['file'=>$item->getFilename(), 'type'=>$item->getType()->getFiletype(), 'modified'=>$item->getAddedOn()->getTimestamp(), 'size'=>$item->getFilesize(), 'friendly_size'=>$this->MakeSize($item->getFilesize())];
        });
        if (file_exists($this->RootDir.$media->getPath().'/'.$file->getFilename())) {
            $f = finfo_open(FILEINFO_MIME);
            $filemime = explode(';', finfo_file($f, $this->RootDir.$media->getPath().'/'.$file->getFilename()))[0];
            $this->items[] = (object)['path'=>'/media/view/'.ltrim($media->getPath().'/'.$media->getTitle(), '/'), 'thumb'=>$this->GenerateMediaThumb($media, $filemime), 'title'=>$media->getTitle(), 'type'=>$filemime, 'modified'=>$file->getAddedOn()->getTimestamp(), 'size'=>$file->getFilesize(), 'friendly_size'=>$this->MakeSize($file->getFilesize()), 'previous'=>$fileinfo, 'absfile'=>$this->RootDir.'/Resources/files/'.$media->getPath().'/'.$file->getFilename(), 'video_id'=>$media->getVideoId()];
        }
    }
    /**
     * @param Media $media
     * @return string
     * Get the thumbnail URL of the associated file - if its not an image, we should return a file type icon
     */
    private function GenerateMediaThumb(Media $media, $filemime)
    {
        if (strstr($filemime, 'image') !== false) {
            // this is an image so we can get a thumbnail \\
            if (!file_exists($this->CacheDir.'/thumbs/'.$media->getMediaFiles()->first()->getFilename())) {
                if (!is_dir($this->CacheDir.'/thumbs')) {
                    mkdir($this->CacheDir.'/thumbs');
                }
                $this->GenerateImageThumbnail($this->RootDir.$this->FixPath($media->getPath()).$media->getMediaFiles()->first()->getFilename(), $this->CacheDir.'/thumbs/'.$media->getMediaFiles()->first()->getFilename());
            }
            return '/media/thumb/'.ltrim($this->FixPath($media->getPath()).$media->getTitle(), '/');
        } else {
            // need to show thumbs here for non image files \\
            
        }
    }
    private function GenerateFileThumb($absfile, $filemime, $path)
    {
        $file = basename($absfile);
        $fileHash = md5($file);
        if (strstr($filemime, 'image') !== false) {
            // this is an image so we can get a thumbnail \\
            if (!file_exists($this->CacheDir.'/thumbs/'.$path.'/'.$fileHash)) {
                if (!is_dir($this->CacheDir.'/thumbs/'.$path)) {
                    mkdir($this->CacheDir.'/thumbs/'.$path, 0777, true);
                }
                $this->GenerateImageThumbnail($absfile, $this->CacheDir.'/thumbs/'.$path.'/'.$fileHash);
            }
            return '/media/file/thumb/'.$path.$file;
        } else {
            // need to show thumbs here for non image files \\
            
        }
    }
    private function GenerateImageThumbnail($source_image_path, $thumbnail_image_path)
    {
        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                $source_gd_image = \imagecreatefromgif($source_image_path);
                break;
            case IMAGETYPE_JPEG:
                $source_gd_image = \imagecreatefromjpeg($source_image_path);
                break;
            case IMAGETYPE_PNG:
                $source_gd_image = \imagecreatefrompng($source_image_path);
                break;
        }
        if ($source_gd_image === false) {
            return false;
        }
        $source_aspect_ratio = $source_image_width / $source_image_height;
        $thumbnail_aspect_ratio = $this->MAXW / $this->MAXH;
        if ($source_image_width <= $this->MAXW && $source_image_height <= $this->MAXH) {
            $thumbnail_image_width = $source_image_width;
            $thumbnail_image_height = $source_image_height;
        } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
            $thumbnail_image_width = (int) ($this->MAXH * $source_aspect_ratio);
            $thumbnail_image_height = $this->MAXH;
        } else {
            $thumbnail_image_width = $this->MAXW;
            $thumbnail_image_height = (int) ($this->MAXW / $source_aspect_ratio);
        }
        $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
        imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
        imagedestroy($source_gd_image);
        imagedestroy($thumbnail_gd_image);
        return true;
    }
    private function DirHasChildren($dir)
    {
        if (!is_readable($dir)) {
            return null;
        }
        $dh = opendir($dir);
        while (false !== ($entry = readdir($dh))) {
            if ($entry !== '.' && $entry !== '..') {
                return true;
            }
        }
        return false;
    }
    private function FolderInfo($path)
    {
        $total_size = 0;
	$files = scandir($path);
	$cleanPath = rtrim($path, '/') . '/';
	$files_count = 0;
	$folders_count = 0;
	foreach ($files as $t) {
            if ($t != "." && $t != "..") {
                $currentFile = $cleanPath . $t;
                if (is_dir($currentFile)) {
                    list($size,$tmp,$tmp1) = $this->FolderInfo($currentFile);
                    $total_size += $size;
                    $folders_count ++;
                } else {
                    $size = filesize($currentFile);
                    $total_size += $size;
                    $files_count++;
                }
            }
	}

	return array($total_size,$files_count,$folders_count);
    }
    /**
    * Convert convert size in bytes to human readable
    *
    * @param  int  $size
    *
    * @return  string
    */
   private function MakeSize($size)
   {
        $units = array( 'B', 'KB', 'MB', 'GB', 'TB' );
        $u = 0;
        while ((round($size / 1024) > 0) && ($u < 4)) {
            $size = $size / 1024;
            $u++;
        }
        return (number_format($size, 0) . " " . $units[ $u ]);
   }
   /**
     * 
     * @param string $path
     * @return string
     * Use this method to ensure there is a / on the end of folder paths
     */
    public function FixPath($path)
    {
        $p=str_replace('\\','/',trim($path));
        return (substr($p,-1)!='/') ? $p.='/' : $p;
    }
}
