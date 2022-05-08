<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use EdcomsCMS\UserBundle\Entity\User;

/**
 * CustomFieldData
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\CustomFieldDataRepository")
 */
class CustomFieldData
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
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    private $value;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="added_on", type="datetime")
     */
    private $addedOn;
    
    /**
     * @var cmsUsers
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUsers")
     * @ORM\JoinColumn(name="added_by", referencedColumnName="id")
     */
    private $addedUser;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="added_by_user", referencedColumnName="id")
     */
    private $addedBy;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;

    /**
     * @var CustomFields
     * @ORM\ManyToOne(targetEntity="CustomFields")
     * @ORM\JoinColumn(name="fieldID", referencedColumnName="id")
     */
    private $custom_fields;
    
    /**
     * @var Content
     * @ORM\ManytoOne(targetEntity="Content", inversedBy="custom_field_data")
     * @ORM\JoinColumn(name="contentID", referencedColumnName="id", onDelete="CASCADE")
     */
    private $content;
    
    /**
     *
     * @var CustomFieldData
     * @ORM\ManyToOne(targetEntity="CustomFieldData", inversedBy="children", fetch="EAGER")
     * @ORM\JoinColumn(name="parentID", referencedColumnName="id", onDelete="CASCADE") 
     */
    private $parent;
    
    /**
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="CustomFieldData", mappedBy="parent", cascade={"all"}, orphanRemoval=true) 
     */
    private $children;

    public function __construct()
    {
        $this->addedOn = new \DateTime();
        $this->children = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return CustomFieldData
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     * Set value
     *
     * @param string $value
     * @return CustomFieldData
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set addedOn
     *
     * @param \DateTime $addedOn
     * @return CustomFieldData
     */
    public function setAddedOn($addedOn)
    {
        $this->addedOn = $addedOn;

        return $this;
    }

    /**
     * Get addedOn
     *
     * @return \DateTime 
     */
    public function getAddedOn()
    {
        return $this->addedOn;
    }
    
    public function setAddedUser($addedUser)
    {
        $this->addedUser = $addedUser;
        return $this;
    }
    
    public function getAddedUser()
    {
        return $this->addedUser;
    }
    
    public function setCustomFields($custom_fields)
    {
        $this->custom_fields = $custom_fields;
        return $this;
    }
    
    public function getCustomFields()
    {
        return $this->custom_fields;
    }
    
    public function setContent($content, $recurs=false)
    {
        $this->content = $content;
        if ($recurs) {
            $content->addCustomFieldData($this, false);
        }
        return $this;
    }
    
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * 
     * @return CustomFieldData
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     * 
     * @param $parent
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }
    
    /**
     * 
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }
    
    /**
     * 
     * @param Array $children
     * @return $this
     */
    public function setChildren($children)
    {
        foreach ($children as $child) {
            $this->setChild($child, false);
        }
        return $this;
    }
    
    /**
     * 
     * @param \EdcomsCMS\ContentBundle\Entity\CustomFieldData $child
     * @param boolean $recurse
     * @return $this
     */
    public function setChild(CustomFieldData $child, $recurse=true)
    {
        $this->children[] = $child;
        if ($recurse) {
            $child->setParent($this);
        }
        return $this;
    }


    /**
     * @param CustomFieldData $child
     * @return $this
     */
    public function addChild(CustomFieldData $child){
        if(!$this->getChildren()->contains($child)){
            $this->getChildren()->add($child);

        }
        return $this;
    }


    public function toJSON($vars=[]) {
        unset($this->json);
        $this->json = get_object_vars($this);
        $this->json['addedUser'] = (!is_array($this->json['addedUser']) && !is_null($this->addedUser)) ? $this->addedUser->toJSON() : $this->addedUser;
        $this->json['custom_fields'] = (!is_array($this->json['custom_fields'])) ? $this->custom_fields->toJSON() : $this->custom_fields;
        if (empty($vars)) {
            // remove anything with an _ \\
            $this->json = array_filter($this->json, array(&$this, 'json_filter'), ARRAY_FILTER_USE_BOTH);
            unset($this->json['content']);
            return $this->json;
        }
        $obj = [];
        foreach ($vars as $prop) {
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

    public function __toString()
    {
       return '';
    }

    /**
     * @return User
     */
    public function getAddedBy()
    {
        return $this->addedBy;
    }

    /**
     * @param User $addedBy
     */
    public function setAddedBy($addedBy)
    {
        $this->addedBy = $addedBy;
    }

    /**
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param User $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    }

    /**
     * @param $name
     * @return string
     */
    public function getChildValue($name){
        foreach ($this->children as $child){
            /** @var CustomFieldData $child */
            if($child->getCustomFields()->getName()==$name){
                return $child->getValue();
            }
        }
        return '';
    }

}
