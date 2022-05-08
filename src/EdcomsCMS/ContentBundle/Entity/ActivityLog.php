<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatusLog
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\ActivityLogRepository")
 */
class ActivityLog
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
     * @ORM\Column(name="action", type="string", length=255)
     */
    private $action;
    
    /**
     * @var cmsUsers
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUsers", inversedBy="activityLogs")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    private $user;
    
    /**
     *
     * @var string
     * @ORM\Column(name="detail", type="string", length=255)
     */
    private $detail;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;
    
    /**
     * @var integer
     * @ORM\Column(name="referenceID", type="integer")
     */
    private $referenceID;
    
    /**
     * @var string
     * @ORM\Column(name="referenceType", type="string", length=40)
     */
    private $referenceType;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set Action - will be converted to lowercase before adding to the DB
     *
     * @param string $action
     * @return ActivityLog
     */
    public function setAction($action)
    {
        $this->action = strtolower($action);

        return $this;
    }

    /**
     * Get Action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set User
     *
     * @param EdcomsCMS\AuthBundle\Entity\cmsUsers $user
     * @return ActivityLog
     */
    public function setUser(\EdcomsCMS\AuthBundle\Entity\cmsUsers $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get User
     *
     * @return EdcomsCMS\AuthBundle\Entity\cmsUsers
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set Detail
     *
     * @param string $detail
     * @return ActivityLog
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * Get Detail
     *
     * @return string 
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return ActivityLog
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
    
    /**
     * Set referenceID
     *
     * @param integer $referenceID
     * @return ActivityLog
     */
    public function setReferenceID($referenceID)
    {
        $this->referenceID = $referenceID;

        return $this;
    }

    /**
     * Get referenceID
     *
     * @return integer
     */
    public function getReferenceID()
    {
        return $this->referenceID;
    }
    
    /**
     * Set referenceType - will be converted to lowercase before adding to the DB
     *
     * @param string $referenceType
     * @return ActivityLog
     */
    public function setReferenceType($referenceType)
    {
        $this->referenceType = strtolower($referenceType);

        return $this;
    }

    /**
     * Get referenceType
     *
     * @return string
     */
    public function getReferenceType()
    {
        return $this->referenceType;
    }
}
