<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FormBuilderElements
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\FormBuilderElementsRepository")
 */
class FormBuilderElements
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
     *
     * @var UserGeneratedContentForm
     * @ORM\ManyToOne(targetEntity="UserGeneratedContentForm", inversedBy="formBuilderElements")
     * @ORM\JoinColumn(name="formID", referencedColumnName="id")
     */
    private $userGeneratedContentForm;
    
    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=30)
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=80)
     */
    private $label;

    /**
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="FormBuilderElementTypes")
     * @ORM\JoinColumn(name="typeID", referencedColumnName="id")
     */
    private $type;
    
    /**
     * @var boolean
     * @ORM\Column(name="required", type="boolean")
     */
    private $required;

    /**
     * @var string
     * @ORM\Column(name="defaultValue", type="string", length=120)
     */
    private $defaultValue;

    /**
     * Set id
     * @param integer
     * @return FormBuilderElements
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
     * Set UserGeneratedContentForm
     *
     * @param UserGeneratedContentForm
     * @return FormBuilderElements
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
     * Set name
     *
     * @param string $name
     * @return FormBuilderElements
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
     * Set label
     *
     * @param string $label
     * @return FormBuilderElements
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set type
     *
     * @param FormBuilderElementType $type
     * @return FormBuilderElements
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return FormBuilderElementType
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Set label
     *
     * @param boolean $required
     * @return FormBuilderElements
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Get label
     *
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set defaultValue
     *
     * @param string $defaultValue
     * @return FormBuilderElements
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
}
