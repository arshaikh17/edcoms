<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Content
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\UserGeneratedContentEntryRepository")
 */
class UserGeneratedContentEntry
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
     * @var UserGeneratedContentForm
     * @ORM\ManyToOne(targetEntity="UserGeneratedContentForm")
     * @ORM\JoinColumn(name="formID", referencedColumnName="id")
     */
    private $userGeneratedContentForm;
    
    /**
     * @var cmsUsers
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUsers")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;
    
    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=20, nullable=true)
     */
    private $status='pending';
    
    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=250, nullable=true)
     */
    private $title;
    
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="UserGeneratedContentValues", mappedBy="entry")
     */
    private $userGeneratedContentValues;
    
    public function __construct()
    {
        $this->userGeneratedContentValues = new ArrayCollection();
        $date = new \DateTime();
        $this->title = $date->format('d/m/Y H:i:s');
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
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set UserGeneratedContentForm
     *
     * @param UserGeneratedContentForm $userGeneratedContentForm
     * @return UserGeneratedContentEntry
     */
    public function setUserGeneratedContentForm($userGeneratedContentForm)
    {
        $this->userGeneratedContentForm = $userGeneratedContentForm;

        return $this;
    }

    /**
     * Get UserGeneratedContentForm
     *
     * @return UserGeneratedContentForm 
     */
    public function getUserGeneratedContentForm()
    {
        return $this->userGeneratedContentForm;
    }

    /**
     * Get User
     * @return cmsUsers
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Set User
     * @param cmsUsers $user
     * @return UserGeneratedContentEntry
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
    
    public function getDate()
    {
        return $this->date;
    }
    
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }
    
    public function getUserGeneratedContentValues($json=false)
    {
        if ($json) {
            $values = [];
            $this->userGeneratedContentValues->forAll(function($ind, $value) use (&$values) {
                $values[] = $value->toJSON();
                return true;
            });
            return $values;
        }
        return $this->userGeneratedContentValues;
    }
    
    public function setUserGeneratedContentValues($userGeneratedContentValues)
    {
        foreach ($userGeneratedContentValues as $userGeneratedContentValue) {
            $this->addUserGeneratedContentValue($userGeneratedContentValue);
        }
        return $this;
    }
    
    public function addUserGeneratedContentValue(UserGeneratedContentValues $userGeneratedContentValue, $recurs=true)
    {
        $this->userGeneratedContentValues[] = $userGeneratedContentValue;
        if ($recurs) {
            $userGeneratedContentValue->setEntry($this);
        }
        return $this;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
    
    public function getTitle()
    {
        if (empty($this->title)) {
            $date = new \DateTime();
            $this->title = $date->format('d/m/Y H:i:s');
        }
        return $this->title;
    }
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    public function toJSON($vars=[]) {
        $this->json = [];
        // get title just to populate it \\
        $this->getTitle();
        if (empty($vars) || array_search('userGeneratedContentValues', $vars)) {
            $tmpugcv = [];
            foreach ($this->getUserGeneratedContentValues() as $ugcv) {
                $tmpugcv[] = $ugcv->toJSON();
            }
            
            $this->json['userGeneratedContentValues'] = $tmpugcv;
        }
        if (empty($vars)) {
            $this->json = get_object_vars($this);
            $this->json['userGeneratedContentForm'] = (!is_array($this->json['userGeneratedContentForm'])) ? (!is_null($this->userGeneratedContentForm)) ? $this->userGeneratedContentForm->toJSON() : null : $this->userGeneratedCOntentForm;
            $this->json['user'] = (!is_array($this->json['user'])) ? (!is_null($this->user)) ? $this->user->toJSON() : null : $this->user;
            // remove anything with an _ \\
            $this->json = array_filter($this->json, array(&$this, 'json_filter'), ARRAY_FILTER_USE_BOTH);
            return $this->json;
        }
        $obj= [];
        foreach ($vars as $prop) {
            if (!isset($this->json[$prop])) {
                $this->json[$prop] = (is_object($this->{$prop}) && method_exists($this->{$prop}, 'toJSON')) ? $this->{$prop}->toJSON() : $this->{$prop};
            }
            $obj[$prop] = $this->json[$prop];
        }
        return $obj;
    }
    public function json_filter($val, $key) {
        if (!strstr($key, '__')) {
            return true;
        }
        return false;
    }
}
