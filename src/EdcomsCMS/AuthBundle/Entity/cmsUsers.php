<?php

namespace EdcomsCMS\AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

use EdcomsCMS\AuthBundle\DependencyInjection\UserDependentRole;
use EdcomsCMS\AuthBundle\Entity\Person;


/**
 * cmsUsers
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\AuthBundle\Entity\cmsUsersRepository")
 */
class cmsUsers implements AdvancedUserInterface, \Serializable
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
     * @ORM\Column(name="username", type="string", length=100)
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=128)
     */
    private $password;

    
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $is_active;
    
    /**
     *
     * @var Person
     * @ORM\OneToOne(targetEntity="Person", cascade={"all"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="personID", referencedColumnName="id")
     */
    private $person;
    
    /**
     *
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="cmsUserGroups", inversedBy="user", cascade={"persist"})
     * @ORM\JoinTable(name="cmsGroupHooks",
     *                joinColumns={@ORM\JoinColumn(name="userID", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="groupID", referencedColumnName="id")})
     */
    private $groups;
    
    /**
     *
     * @var object 
     * @ORM\OneToMany(targetEntity="cmsUserDashboard", mappedBy="user")
     * @ORM\JoinColumn(name="id", referencedColumnName="userID")
     */
    private $dashboard;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="added_on", type="datetime", nullable=true)
     */
    private $addedOn;
    /**
     *
     * @var boolean
     * @ORM\Column(name="deleted", type="boolean", nullable=false, options={"default"=false}))
     */
    private $deleted;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EdcomsCMS\ContentBundle\Entity\ActivityLog", mappedBy="user", fetch="EXTRA_LAZY")
     */
    private $activityLogs;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->person = new Person();
        $this->addedOn = new \DateTime();
        $this->activityLogs = new ArrayCollection();
        $this->deleted = false;
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
     * Set username
     *
     * @param string $username
     * @return cmsUsers
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return cmsUsers
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * 
     * @param \DateTime $addedOn
     * @return cmsUsers
     */
    public function setAddedOn(\DateTime $addedOn)
    {
        $this->addedOn = $addedOn;
        return $this;
    }


    /**
     * 
     * @return \DateTime
     */
    public function getAddedOn()
    {
        return $this->addedOn;
    }
    
    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }
    
    public function getRoles()
    {
        $roles = [];
        foreach ($this->groups as $group) {
            $roles[] = new UserDependentRole($this, $group);
        }
        return $roles;
    }
    
    public function eraseCredentials()
    {
    }
    
    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->is_active;
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->is_active
        ]);
    }
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->is_active
        ) = unserialize($serialized);
    }

    /**
     * Set is_active
     *
     * @param boolean $isActive
     * @return cmsUsers
     */
    public function setIsActive($isActive)
    {
        if (is_bool($isActive)) {
            $this->is_active = $isActive;
        }
        return $this;
    }

    /**
     * Get is_active
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->is_active;
    }
    
    public function getPerson() {
        return $this->person;
    }
    public function setPerson(Person $person=null) {
        $this->person = $person;
    }
    public function addGroup(cmsUserGroups $group)
    {
        $this->groups[] = $group;
        return $this;
    }
    public function setGroups($groups) {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }
        return $this;
    }
    public function getGroups() {
        return $this->groups;
    }
    public function getDashboard() {
        return $this->dashboard;
    }
    public function toJSON($vars=[]) {
        unset($this->json);
        if (empty($vars)) {
            $this->json = get_object_vars($this);
            
            if (!is_array($this->groups)) {
                $tempgroups = [];
                foreach ($this->groups as $key=>$group) {
                    $tempgroups[$key] = $group->toJSON();
                }
                $this->json['groups'] = $tempgroups;
            }
            $this->json['person'] = (!is_array($this->json['person'])) ? $this->person->toJSON() : $this->person;
            // remove anything with an _ \\
            $this->json = array_filter($this->json, array(&$this, 'json_filter'), ARRAY_FILTER_USE_BOTH);
            unset($this->json['dashboard']);
            unset($this->json['password']);
            unset($this->json['addedOn']);
            unset($this->json['activityLogs']);
            return $this->json;
        }
        $obj = [];
        foreach ($vars as $prop) {
            $obj[$prop] = (is_object($this->{$prop}) && method_exists($this->{$prop}, 'toJSON')) ? $this->{$prop}->toJSON() : $this->{$prop};
        }
        return $obj;
    }
    public function json_filter($val, $key) {
        if (!strstr($key, '__')) {
            return true;
        }
        return false;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return cmsUsers
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
    
    public function addActivityLog($activityLog)
    {
        if (!$this->activityLogs->contains($activityLog)) {
            $activityLog->setUser($this);
            
            $this->activityLogs->add($activityLog);
        }
    
        return $this;
    }
    
    public function removeActivityLog($activityLog)
    {
        if ($this->activityLogs->contains($activityLog)) {
            $activityLog->setUser(null);
            
            $this->activityLogs->add($activityLog);
        }
    
        return $this;
    }
    
    /**
     * Get activity logs
     *
     * @return ArrayCollection
     */
    public function getActivityLogs()
    {
        return $this->activityLogs;
    }

    /**
     * Remove group
     *
     * @param \EdcomsCMS\AuthBundle\Entity\cmsUserGroups $group
     */
    public function removeGroup(\EdcomsCMS\AuthBundle\Entity\cmsUserGroups $group)
    {
        $this->groups->removeElement($group);
    }

    /**
     * Add dashboard
     *
     * @param \EdcomsCMS\AuthBundle\Entity\cmsUserDashboard $dashboard
     *
     * @return cmsUsers
     */
    public function addDashboard(\EdcomsCMS\AuthBundle\Entity\cmsUserDashboard $dashboard)
    {
        $this->dashboard[] = $dashboard;

        return $this;
    }

    /**
     * Remove dashboard
     *
     * @param \EdcomsCMS\AuthBundle\Entity\cmsUserDashboard $dashboard
     */
    public function removeDashboard(\EdcomsCMS\AuthBundle\Entity\cmsUserDashboard $dashboard)
    {
        $this->dashboard->removeElement($dashboard);
    }

    public function __toString()
    {
        return $this->getUsername() ? $this->getUsername() : '';
    }
}
