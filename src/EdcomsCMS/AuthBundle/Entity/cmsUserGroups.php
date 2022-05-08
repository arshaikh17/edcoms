<?php

namespace EdcomsCMS\AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * cmsUserGroups
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\AuthBundle\Entity\cmsUserGroupsRepository")
 */
class cmsUserGroups implements RoleInterface
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
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;
    
    /**
     *
     * @var boolean
     * @ORM\Column(name="default_value",  type="boolean", nullable=true)
     */
    private $defaultValue;

    /**
     * @var cms_users
     * @ORM\ManyToMany(targetEntity="cmsUsers", mappedBy="groups")
     * @ORM\JoinTable(name="cmsGroupHooks",
     *                joinColumns={@ORM\JoinColumn(name="groupID", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="userID", referencedColumnName="id")})
     */
    private $user;

    /**
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="cmsGroupPerms", mappedBy="group")
     * @ORM\JoinColumn(name="groupID", referencedColumnName="id")
     */
    private $perms;

    private $role;


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
     * Set name
     *
     * @param string $name
     * @return cmsUserGroups
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set defaultValue
     *
     * @param string $defaultValue
     * @return cmsUserGroups
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get defaultValue
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
    
    /**
     * 
     * @param \EdcomsCMS\AuthBundle\Entity\cmsUsers $user
     * @return \EdcomsCMS\AuthBundle\Entity\cmsUserGroups
     */
    public function setUser(cmsUsers $user)
    {
        $this->user = $user;
        return $this;
    }
    /**
     * 
     * @return cmsUsers
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return cmsUserGroups
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
    
    /**
     * 
     * @param ArrayCollection $perms
     * @return \EdcomsCMS\AuthBundle\Entity\cmsUserGroups
     */
    public function setPerms(ArrayCollection $perms)
    {
        foreach ($perms as $perm) {
            $this->addPerm($perm);
        }
        return $this;
    }

    /**
     * 
     * @param \EdcomsCMS\AuthBundle\Entity\cmsGroupPerms $perm
     * @return \EdcomsCMS\AuthBundle\Entity\cmsUserGroups
     */
    public function addPerm(cmsGroupPerms $perm)
    {
        $this->perms[] = $perm;
        return $this;
    }
    /**
     * 
     * @return cmsGroupPerms
     */
    public function getPerms()
    {
        return $this->perms;
    }
    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function toJSON($vars=[]) {
        unset($this->json);
        if (empty($vars)) {
            $this->json = get_object_vars($this);
            
            // remove anything with an _ \\
            $this->json = array_filter($this->json, array(&$this, 'json_filter'), ARRAY_FILTER_USE_BOTH);
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
     * Constructor
     */
    public function __construct()
    {
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
        $this->perms = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add user
     *
     * @param \EdcomsCMS\AuthBundle\Entity\cmsUsers $user
     *
     * @return cmsUserGroups
     */
    public function addUser(\EdcomsCMS\AuthBundle\Entity\cmsUsers $user)
    {
        $this->user[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \EdcomsCMS\AuthBundle\Entity\cmsUsers $user
     */
    public function removeUser(\EdcomsCMS\AuthBundle\Entity\cmsUsers $user)
    {
        $this->user->removeElement($user);
    }

    /**
     * Remove perm
     *
     * @param \EdcomsCMS\AuthBundle\Entity\cmsGroupPerms $perm
     */
    public function removePerm(\EdcomsCMS\AuthBundle\Entity\cmsGroupPerms $perm)
    {
        $this->perms->removeElement($perm);
    }

    public function __toString()
    {
        return $this->getName() ? $this->getName() : '';
    }
}
