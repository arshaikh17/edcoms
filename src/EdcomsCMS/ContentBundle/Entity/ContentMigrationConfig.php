<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content migration configuration object
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\ContentMigrationConfigRepository")
 */
class ContentMigrationConfig
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
     * @ORM\Column(name="last_cfd_update_parent_id", type="integer", nullable=true)
     */
    private $lastCFDUpdateParentId;

    public function __construct()
    {
        $this->lastCFDUpdateParentId = null;
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
     * Set lastCFDUpdateParentId
     *
     * @param integer $lastCFDUpdateParentId
     *
     * @return ContentMigrationConfig
     */
    public function setLastCFDUpdateParentId($lastCFDUpdateParentId)
    {
        $this->lastCFDUpdateParentId = $lastCFDUpdateParentId;

        return $this;
    }

    /**
     * Get lastCFDUpdateParentId
     *
     * @return integer
     */
    public function getLastCFDUpdateParentId()
    {
        return $this->lastCFDUpdateParentId;
    }
}
