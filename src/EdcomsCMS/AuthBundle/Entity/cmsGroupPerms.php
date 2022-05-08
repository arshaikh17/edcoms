<?php

namespace EdcomsCMS\AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * cmsGroupPerms
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\AuthBundle\Entity\cmsGroupPermsRepository")
 */
class cmsGroupPerms
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
     * @var cmsUserGroups
     *
     * @ORM\ManyToOne(targetEntity="cmsUserGroups", inversedBy="perms")
     * @ORM\JoinColumn(name="groupID", referencedColumnName="id")
     */
    private $group;
    
    /**
     *
     * @var string
     * 
     * @ORM\Column(name="context", type="string", length=50, nullable=true)
     */
    private $context;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=20)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="perm", type="boolean")
     */
    private $value;


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
     * Set group
     *
     * @param cmsUserGroups $group
     * @return cmsGroupPerms
     */
    public function setGroup(cmsUserGroups $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return cmsUserGroups
     */
    public function getGroup()
    {
        return $this->group;
    }
    
    /**
     * Set context
     *
     * @param string $context
     * @return cms_group_perms
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context
     *
     * @return string 
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return cms_group_perms
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
     * Set value
     *
     * @param boolean $value
     * @return cms_group_perms
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return boolean 
     */
    public function getValue()
    {
        return $this->value;
    }
}
