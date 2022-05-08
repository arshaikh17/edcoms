<?php

namespace EdcomsCMS\ContentBundle\Helpers;

use ZipArchive;

class ZIPHelper
{
    private $ctrl_dir = [];
    private $datasec = [];
    private $eof_ctrl_dir = '\x50\x4b\x05\x06\x00\x00\x00\x00';
    private $old_offset = 0;
    private $rootDir = null;
    
    public function __construct($rootDir)
    {
        $this->rootDir = realpath($rootDir . '/../var') . '/tmp';
        
        if (!file_exists($this->rootDir)) {
            if (!mkdir($this->rootDir)) {
                throw new \Exception("Cannot create directory in '{$this->rootDir}'.");
            }
        }
    }
    
    /**
     * Creates a ZIP file from '$files', and either returns a string for the ZIP path or false if something has gone wrong.
     * 
     * @param   array   $files      Collection of files to archive.
     *
     * @return  mixed               Link to the newly create ZIP file, or 'false' is something has gone wrong.
     */
    public function createZip(array $files, $overwrite = false)
    {
        // we're not creating an empty ZIP file.
        if (empty($files)) {
            return false;
        }
        
        // make a separate temporary path for ZIP file generation.
        $timestamp = date('YmdHis');
        $zipPath = $this->rootDir . "/$timestamp.zip";
        
        $filesToZip = [];
        
        foreach ($files as $file) {
            $isArray = is_array($file);
            $fileName = $isArray ? (isset($file['name']) ? $file['name'] : basename($file['location'])) : basename($file);
            $fileLocation = $isArray ? $file['location'] : $file;
            
            if (!file_exists($fileLocation)) {
                throw new \Exception("No such file or directory '$fileLocation'.");
            }
            
            $this->add_file($fileLocation, $fileName);
            
            $filesToZip[] = [
                'name' => $fileName,
                'location' => $fileLocation
            ];
        }
        
        //if we have good files...
        if (!empty($filesToZip)) {
            //create the archive
            $zip = new ZipArchive();
            
            if ($zip->open($zipPath, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                return false;
            }
            
            //add the files
            foreach ($filesToZip as $file) {
                $zip->addFile($file['location'], $file['name']);
            }
            
            //close the zip -- done!
            $zip->close();
    
            //check to make sure the file exists
            return $zipPath;
        }
        
        return false;
    }
    
    /**
     * Adds a directory to creating archive.
     * Nasty codes are for Windows compatibility.
     */
    private function add_dir($name)
    {
            $name = str_replace('', '/', $name);
            $fr = '\x50\x4b\x03\x04';
            $fr .= '\x0a\x00';
            $fr .= '\x00\x00';
            $fr .= '\x00\x00';
            $fr .= '\x00\x00\x00\x00';
            $fr .= pack('V',0);
            $fr .= pack('V',0);
            $fr .= pack('V',0);
            $fr .= pack('v', strlen($name));
            $fr .= pack('v', 0);
            $fr .= $name;
            $fr .= pack('V', 0);
            $fr .= pack('V', 0);
            $fr .= pack('V', 0);
            $this->datasec[] = $fr;
            $new_offset = strlen(implode('', $this->datasec));
            $cdrec = '\x50\x4b\x01\x02';
            $cdrec .= '\x00\x00';
            $cdrec .= '\x0a\x00';
            $cdrec .= '\x00\x00';
            $cdrec .= '\x00\x00';
            $cdrec .= '\x00\x00\x00\x00';
            $cdrec .= pack('V',0);
            $cdrec .= pack('V',0);
            $cdrec .= pack('V',0);
            $cdrec .= pack('v', strlen($name));
            $cdrec .= pack('v', 0);
            $cdrec .= pack('v', 0);
            $cdrec .= pack('v', 0);
            $cdrec .= pack('v', 0);
            $ext = '\x00\x00\x10\x00';
            $ext = '\xff\xff\xff\xff';
            $cdrec .= pack('V', 16);
            $cdrec .= pack('V', $this->old_offset);
            $cdrec .= $name;
            $this->ctrl_dir[] = $cdrec;
            $this->old_offset = $new_offset;
    }
    
    /**
     * Adds a file to creating archive.
     * Nasty codes are for Windows compatibility.
     */
    private function add_file($data, $name)
    {
        $fp = @fopen($data, 'r');
            
            if ($fp) {
                $data = fread($fp, filesize($data));
                fclose($fp);
                
                $name = str_replace('', '/', $name);
                $dataLength = strlen($data);
                $crcChecksum = crc32($data);
                $compressedData = gzcompress($data);
                $compressedData = substr ($compressedData, 2, -4);
                $compressedDataLength = strlen($compressedData);
                
                $fr = '\x50\x4b\x03\x04';
                $fr .= '\x14\x00';
                $fr .= '\x00\x00';
                $fr .= '\x08\x00';
                $fr .= '\x00\x00\x00\x00';
                $fr .= pack('V', $crcChecksum);
                $fr .= pack('V', $compressedDataLength);
                $fr .= pack('V', $dataLength);
                $fr .= pack('v', strlen($name));
                $fr .= pack('v', 0);
                $fr .= $name;
                $fr .= $compressedData;
                $fr .= pack('V', $crcChecksum);
                $fr .= pack('V', $compressedDataLength);
                $fr .= pack('V', $dataLength);
                $this->datasec[] = $fr;
                $new_offset = strlen(implode('', $this->datasec));
                $cdrec = '\x50\x4b\x01\x02';
                $cdrec .= '\x00\x00';
                $cdrec .= '\x14\x00';
                $cdrec .= '\x00\x00';
                $cdrec .= '\x08\x00';
                $cdrec .= '\x00\x00\x00\x00';
                $cdrec .= pack('V', $crcChecksum);
                $cdrec .= pack('V', $compressedDataLength);
                $cdrec .= pack('V', $dataLength);
                $cdrec .= pack('v', strlen($name));
                $cdrec .= pack('v', 0);
                $cdrec .= pack('v', 0);
                $cdrec .= pack('v', 0);
                $cdrec .= pack('v', 0);
                $cdrec .= pack('V', 32);
                $cdrec .= pack('V', $this->old_offset);
                $this->old_offset = $new_offset;
                $cdrec .= $name;
                $this->ctrl_dir[] = $cdrec;
            }
    }
}
