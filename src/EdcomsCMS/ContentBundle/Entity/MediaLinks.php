<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MediaLinks
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\MediaLinksRepository")
 */
class MediaLinks
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
     * @var integer
     *
     * @ORM\Column(name="mediaID", type="integer")
     */
    private $mediaID;

    /**
     * @var integer
     *
     * @ORM\Column(name="contentID", type="integer")
     */
    private $contentID;


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
     * Set mediaID
     *
     * @param integer $mediaID
     * @return MediaLinks
     */
    public function setMediaID($mediaID)
    {
        $this->mediaID = $mediaID;

        return $this;
    }

    /**
     * Get mediaID
     *
     * @return integer 
     */
    public function getMediaID()
    {
        return $this->mediaID;
    }

    /**
     * Set contentID
     *
     * @param integer $contentID
     * @return MediaLinks
     */
    public function setContentID($contentID)
    {
        $this->contentID = $contentID;

        return $this;
    }

    /**
     * Get contentID
     *
     * @return integer 
     */
    public function getContentID()
    {
        return $this->contentID;
    }
}
