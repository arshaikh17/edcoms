<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content_perms
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\ContentPermsRepository")
 */
class ContentPerms
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
     * @ORM\Column(name="contentID", type="integer")
     */
    private $contentID;

    /**
     * @var integer
     *
     * @ORM\Column(name="groupID", type="integer")
     */
    private $groupID;

    /**
     * @var string
     *
     * @ORM\Column(name="access", type="string", length=10)
     */
    private $access;


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
     * Set contentID
     *
     * @param integer $contentID
     * @return ContentPerms
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

    /**
     * Set groupID
     *
     * @param integer $groupID
     * @return ContentPerms
     */
    public function setGroupID($groupID)
    {
        $this->groupID = $groupID;

        return $this;
    }

    /**
     * Get groupID
     *
     * @return integer 
     */
    public function getGroupID()
    {
        return $this->groupID;
    }

    /**
     * Set access
     *
     * @param string $access
     * @return ContentPerms
     */
    public function setAccess($access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * Get access
     *
     * @return string 
     */
    public function getAccess()
    {
        return $this->access;
    }
}
