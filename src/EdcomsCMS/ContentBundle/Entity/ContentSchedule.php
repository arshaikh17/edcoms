<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content_schedule
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\ContentScheduleRepository")
 */
class ContentSchedule
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
     * @ORM\Column(name="versionID", type="integer")
     */
    private $versionID;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=10)
     */
    private $action;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;


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
     * Set versionID
     *
     * @param integer $versionID
     * @return ContentSchedule
     */
    public function setVersionID($versionID)
    {
        $this->versionID = $versionID;

        return $this;
    }

    /**
     * Get versionID
     *
     * @return integer 
     */
    public function getVersionID()
    {
        return $this->versionID;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return ContentSchedule
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return ContentSchedule
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }
}
