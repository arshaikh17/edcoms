<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use EdcomsCMS\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Media
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\MediaRepository")
 */
class Media
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=250)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=250, nullable=true)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", length=250, nullable=true)
     */
    private $target;
    
    /**
     * @var string
     * @ORM\Column(name="path", type="string", length=250)
     */
    private $path;

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
     * @var cms_users
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUsers")
     * @ORM\JoinColumn(name="modified_by", referencedColumnName="id", nullable=true)
     */
    private $modifiedUser;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="modified_by_user", referencedColumnName="id")
     */
    private $modifiedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified_on", type="datetime", nullable=true)
     */
    private $modifiedOn;
    
    /**
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="MediaFiles", mappedBy="media", cascade={"all"})
     * @ORM\OrderBy({"addedOn"="DESC"})
     */
    private $mediaFiles;

    /**
     *
     * @var boolean
     * @ORM\Column(name="deleted", type="boolean", nullable=false, options={"default"=false}))
     */
    private $deleted;

    /**
     *
     * @var integer
     * @ORM\Column(name="videoId", type="integer", nullable=true)
     */
    private $videoId;

    /**
     * @var File
     */
    private $attachment;


    /**
     * @var bool
     */
    private $uploadedWithAttachment;

    public function __construct()
    {
        $this->mediaFiles = new ArrayCollection();
        $this->deleted = false;
        $this->addedOn = new \DateTime();
        $this->uploadedWithAttachment=true;
    }
    
    /**
     * Set id
     *
     * @param int $id
     * @return Media
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
     * Set title
     *
     * @param string $title
     * @return Media
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }
    /**
     * Set path
     *
     * @param string $path
     * @return Media
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
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
     * Set modifiedUser
     *
     * @param cmsUsers $modifiedUser
     * @return Media
     */
    public function setModifiedUser($modifiedUser)
    {
        $this->modifiedUser = $modifiedUser;

        return $this;
    }

    /**
     * Get modifiedUser
     *
     * @return cmsUsers
     */
    public function getModifiedUser()
    {
        return $this->modifiedUser;
    }

    /**
     * Set modifiedOn
     *
     * @param \DateTime $modifiedOn
     * @return Media
     */
    public function setModifiedOn($modifiedOn)
    {
        $this->modifiedOn = $modifiedOn;

        return $this;
    }

    /**
     * Get modifiedOn
     *
     * @return \DateTime 
     */
    public function getModifiedOn()
    {
        return $this->modifiedOn;
    }
    
    /**
     * Set mediaFiles
     * @param ArrayCollection $mediaFiles
     * @return Media
     */
    public function setMediaFiles(ArrayCollection $mediaFiles, $recurs=true)
    {
        foreach ($mediaFiles as $mediaFile) {
            $this->addMediaFile($mediaFile, $recurs);
        }
        return $this;
    }

    public function getMediaFiles() {
        return $this->mediaFiles;
    }
    
    /**
     * Add mediaFile
     * @param MediaFiles $mediaFile
     * @return Media
     */
    public function addMediaFile(MediaFiles $mediaFile, $recurs=true)
    {
        $this->mediaFiles[] = $mediaFile;
        if ($recurs) {
            $mediaFile->setMedia($this, false);
        }
        return $this;
    }

    /**
     * Remove a media file
     *
     * @param MediaFiles $mediaFile
     * @return Media
     */
    public function removeMediaFile(MediaFiles $mediaFile)
    {
        $this->mediaFiles->removeElement($mediaFile);

        return $this;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return Media
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set videoId
     *
     * @param integer $videoId
     *
     * @return Media
     */
    public function setVideoId($videoId)
    {
        $this->videoId = $videoId;

        return $this;
    }

    /**
     * Get videoId
     *
     * @return integer
     */
    public function getVideoId()
    {
        return $this->videoId;
    }

    /**
     * @return User
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * @param User $modifiedBy
     */
    public function setModifiedBy($modifiedBy)
    {
        $this->modifiedBy = $modifiedBy;
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

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return File
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param File $attachment
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    public function getFullPath(){
        return $this->getPath() ? sprintf('%s/%s',$this->getPath(),$this->getTitle()) : $this->getTitle();
    }

    /**
     * @return bool
     */
    public function isUploadedWithAttachment()
    {
        return $this->uploadedWithAttachment;
    }

    /**
     * @param bool $uploadedWithAttachment
     */
    public function setUploadedWithAttachment(bool $uploadedWithAttachment)
    {
        $this->uploadedWithAttachment = $uploadedWithAttachment;
    }



}
