<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatusLog
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\StatusLogRepository")
 */
class Status_log
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
     * @ORM\Column(name="status_to", type="string", length=12)
     */
    private $statusTo;

    /**
     * @var string
     *
     * @ORM\Column(name="status_from", type="string", length=12)
     */
    private $statusFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="changed_on", type="datetime")
     */
    private $changedOn;

    /**
     * @var integer
     *
     * @ORM\Column(name="changed_by", type="integer")
     */
    private $changedBy;


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
     * Set statusTo
     *
     * @param string $statusTo
     * @return Status_log
     */
    public function setStatusTo($statusTo)
    {
        $this->statusTo = $statusTo;

        return $this;
    }

    /**
     * Get statusTo
     *
     * @return string 
     */
    public function getStatusTo()
    {
        return $this->statusTo;
    }

    /**
     * Set statusFrom
     *
     * @param string $statusFrom
     * @return Status_log
     */
    public function setStatusFrom($statusFrom)
    {
        $this->statusFrom = $statusFrom;

        return $this;
    }

    /**
     * Get statusFrom
     *
     * @return string 
     */
    public function getStatusFrom()
    {
        return $this->statusFrom;
    }

    /**
     * Set changedOn
     *
     * @param \DateTime $changedOn
     * @return StatusLog
     */
    public function setChangedOn($changedOn)
    {
        $this->changedOn = $changedOn;

        return $this;
    }

    /**
     * Get changedOn
     *
     * @return \DateTime 
     */
    public function getChangedOn()
    {
        return $this->changedOn;
    }

    /**
     * Set changedBy
     *
     * @param integer $changedBy
     * @return StatusLog
     */
    public function setChangedBy($changedBy)
    {
        $this->changedBy = $changedBy;

        return $this;
    }

    /**
     * Get changedBy
     *
     * @return integer 
     */
    public function getChangedBy()
    {
        return $this->changedBy;
    }
}
