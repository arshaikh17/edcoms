<?php

namespace EdcomsCMS\AuthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Person
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\AuthBundle\Entity\TokenRepository")
 */
class Token
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
     * @Assert\NotBlank()
     * @ORM\Column(name="action", type="string", length=100)
     */
    private $action;

    /**
     * @var cmsUsers
     * @ORM\ManyToOne(targetEntity="cmsUsers")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    private $user;

    /**
     *
     * @var string
     * @ORM\Column(name="token", type="string", length=200)
     */
    private $token;
    
    /**
     *
     * @var datetime
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;
    
    /**
     *
     * @var boolean
     * @ORM\Column(name="used", type="boolean", nullable=true, options={"default"=false});
     */
    private $used;
    
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
     * Set action
     *
     * @param string $action
     * @return Token
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
     * Set user
     *
     * @param cmsUsers $user
     * @return Token
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return cmsUsers
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Set token
     *
     * @param string $token
     * @return Token
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * Set date
     * 
     * @param type $date
     * @return Token
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }
    
    /**
     * Get date
     * 
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
    
    /**
     * Set used
     * 
     * @param boolean $used
     * @return Token
     */
    public function setUsed($used)
    {
        $this->used = $used;
        return $this;
    }
    
    /**
     * Get used
     * 
     * @return boolean
     */
    public function getUsed()
    {
        return $this->used;
    }
    
    public function toJSON() {
        unset($this->json);
        
        $this->json = get_object_vars($this);
        $this->json = array_filter($this->json, array(&$this, 'json_filter'), ARRAY_FILTER_USE_BOTH);
        return $this->json;
    }
    public function json_filter($val, $key) {
        if (!strstr($key, '__')) {
            return true;
        }
        return false;
    }
}
