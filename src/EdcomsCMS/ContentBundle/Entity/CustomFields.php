<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CustomFields
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\CustomFieldsRepository")
 */
class CustomFields
{
    use \EdcomsCMS\ContentBundle\Traits\EntityHydration;
    CONST NAME_ALLOW_ARCHIVE = 'allow_archive';
    const TYPE_CONTENTSELECTOR = 'contentselector';
    
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
     * @ORM\Column(name="name", type="string", length=40)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="field_type", type="string", length=18)
     */
    private $fieldType;

    /**
     * @var string
     * 
     * @ORM\Column(name="label", type="string", length=40)
     * @Assert\NotBlank()
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="default_value", type="string", length=40, nullable=TRUE)
     */
    private $defaultValue;

    /**
     * @var boolean
     *
     * @ORM\Column(name="required", type="boolean", nullable=TRUE)
     */
    private $required;

    /**
     * @var string
     *
     * @ORM\Column(name="options", type="string", length=2048, nullable=TRUE)
     */
    private $options;

    /**
     * @var integer
     * @ORM\Column(name="ordering", type="integer", nullable=TRUE)
     */
    private $order;
    
    /**
     * @var ContentType
     * @ORM\ManyToOne(targetEntity="ContentType", inversedBy="custom_fields", fetch="EAGER")
     * @ORM\JoinColumn(name="content_typeID", referencedColumnName="id", onDelete="CASCADE")
     */
    private $content_type;
    
    /**
     *
     * @var boolean
     * @ORM\Column(name="adminOnly", type="boolean", nullable=TRUE) 
     */
    private $adminOnly;
    
    /**
     *
     * @var CustomFields
     * @ORM\ManyToOne(targetEntity="CustomFields", inversedBy="children", fetch="EAGER")
     * @ORM\JoinColumn(name="parentID", referencedColumnName="id", onDelete="CASCADE") 
     */
    private $parent;
    
    /**
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="CustomFields", mappedBy="parent", cascade={"all"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $children;
    
    /**
     *
     * @var boolean 
     * @ORM\Column(name="repeatable", type="boolean", nullable=TRUE)
     */
    private $repeatable;


    public function __construct()
    {
        $this->children = new ArrayCollection();
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
     * Set id
     * Used to set an ID if it is something like -1 by default and we want null
     * @return Custom_fields
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Custom_fields
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
     * Set description
     *
     * @param string $description
     * @return CustomFields
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
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

    /**
     * Set contentType
     *
     * @param string $content_type
     * @return CustomFields
     */
    public function setContentType(ContentType $content_type = null, $recurs=true)
    {
        $this->content_type = $content_type;
        if ($recurs && $content_type !== null) {
            $content_type->setCustomFields($this, false);
        }
        return $this;
    }

    /**
     * Get contentType
     *
     * @return string 
     */
    public function getContentType()
    {
        return $this->content_type;
    }
    
    /**
     * Set label
     * @param string $label
     * @return CustomFields
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }
    
    /**
     * Get defaultValue
     * @return $defaultValue
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set defaultValue
     * @param string $defaultValue
     * @return CustomFields
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * Get required
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set required
     * @param string $required
     * @return CustomFields
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Get options
     * @return options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set options
     * @param string $options
     * @return CustomFields
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get label
     * @return $label
     */
    public function getLabel()
    {
        return $this->label;
    }
    
    /**
     * Set Order
     * @param string $order
     * @return CustomFields
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }
    
    /**
     * Get order
     * @return $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set fieldType
     *
     * @param string $fieldType
     * @return CustomFields
     */
    public function setFieldType($fieldType)
    {
        $this->fieldType = $fieldType;

        return $this;
    }

    /**
     * Get fieldType
     *
     * @return string 
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }
    
    /**
     * 
     * @param bool $admin
     * @return \EdcomsCMS\ContentBundle\Entity\CustomFields
     */
    public function setAdminOnly($admin)
    {
        $this->adminOnly = $admin;
        return $this;
    }
    
    /**
     * 
     * @return bool
     */
    public function getAdminOnly()
    {
        return $this->adminOnly;
    }
    
    /**
     * 
     * @return CustomFields
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     * 
     * @param \EdcomsCMS\ContentBundle\Entity\CustomFields $parent
     * @return $this
     */
//    public function setParent(CustomFields $parent)
    public function setParent($parent)
    {
//        var_dump($this->id);
//        echo '<br/>';
//        var_dump(get_class($parent));
//        echo '<br/>';
//        is_null($parent)? '': var_dump($parent->getName());
//        echo '<br/>';
//        echo '<br/>';

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
            if (!is_a($child, 'EdcomsCMS\ContentBundle\Entity\CustomFields')) {
                $child = new CustomFields($child);
            }
            $this->addChild($child);
        }
        return $this;
    }
    
    /**
     * 
     * @param \EdcomsCMS\ContentBundle\Entity\CustomFields $child
     * @param boolean $recurs
     * @return $this
     */
    public function addChild(CustomFields $child, $recurs=true)
    {
        $this->children[] = $child;
        if ($recurs) {
            $child->setParent($this);
        }
        return $this;
    }

    /**
     *
     * @param \EdcomsCMS\ContentBundle\Entity\CustomFields $child
     * @return $this
     */
    public function removeChild(CustomFields $child)
    {
        if($this->getChildren()->contains($child)){
            $child->setParent(null);
            $this->getChildren()->removeElement($child);
        }
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function getRepeatable()
    {
        return $this->repeatable;
    }
    
    /**
     * 
     * @param boolean $repeatable
     * @return $this
     */
    public function setRepeatable($repeatable)
    {
        $this->repeatable = $repeatable;
        return $this;
    }
    
    public function toJSON($vars=[]) {
        unset($this->json);
        $this->json = get_object_vars($this);
        if (empty($vars)) {

            $this->json['parent'] = ($this->parent !== null && !is_array($this->json['parent'])) ? $this->parent->toJSON() : $this->parent;
            // remove anything with an _ \\
            $this->json = array_filter($this->json, array(&$this, 'json_filter'), ARRAY_FILTER_USE_BOTH);
            unset($this->json['content_type']);
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
        return $this->getLabel() ? $this->getLabel() : '';
    }

}
