<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use EdcomsCMS\UserBundle\Entity\User;

/**
 * MediaFiles
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\MediaFilesRepository")
 */
class MediaFiles
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Media", inversedBy="mediaFiles")
     * @ORM\JoinColumn(name="mediaID", referencedColumnName="id")
     */
    private $media;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=200)
     */
    private $filename;
    
    /**
     * @var cms_users
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUsers")
     * @ORM\JoinColumn(name="added_by", referencedColumnName="id")
     */
    private $addedUser;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="added_by_user", referencedColumnName="id")
     */
    private $addedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="added_on", type="datetime")
     */
    private $addedOn;
    
    /**
     * @var MediaTypes
     *
     * @ORM\ManyToOne(targetEntity="MediaTypes")
     * @ORM\JoinColumn(name="typeID", referencedColumnName="id")
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="filesize", type="integer")
     */
    private $filesize;
    


    /**
     * Set id
     *
     * @param integer $id
     * @return MediaFiles
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set media
     *
     * @param Media $media
     * @return MediaFiles
     */
    public function setMedia(Media $media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media
     *
     * @return MediaFile
     */
    public function getMedia()
    {
        return $this->media;
    }
    
    /**
     * Set filename
     *
     * @param string $filename
     * @return Media
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }
    
    /**
     * Set addedUser
     *
     * @param cmsUsers $addedUser
     * @return Media
     */
    public function setAddedUser($addedUser)
    {
        $this->addedUser = $addedUser;

        return $this;
    }

    /**
     * Get addedUser
     *
     * @return cmsUsers 
     */
    public function getAddedUser()
    {
        return $this->addedUser;
    }

    /**
     * Set addedOn
     *
     * @param \DateTime $addedOn
     * @return Media
     */
    public function setAddedOn($addedOn)
    {
        $this->addedOn = $addedOn;

        return $this;
    }

    /**
     * Get addedOn
     *
     * @return \DateTime 
     */
    public function getAddedOn()
    {
        return $this->addedOn;
    }

    /**
     * Set type
     *
     * @param MediaTypes $type
     * @return MediaFiles
     */
    public function setType(MediaTypes $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return MediaTypes 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set filesize
     *
     * @param integer $filesize
     * @return MediaFiles
     */
    public function setFilesize($filesize)
    {
        $this->filesize = $filesize;

        return $this;
    }

    /**
     * Get filesize
     *
     * @return integer 
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

    /**
     * @return User
     */
    public function getAddedBy()
    {
        return $this->addedBy;
    }

    /**
     * @param User $addedBy
     */
    public function setAddedBy($addedBy)
    {
        $this->addedBy = $addedBy;
    }

    public function getFullPath(){
        return $this->getMedia()->getPath() ? sprintf('%s/%s',$this->getMedia()->getPath(),$this->getFilename()) : $this->getFilename();
    }
}
