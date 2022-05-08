<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Model\CustomField;

use EdcomsCMS\ContentBundle\Entity\CustomFields;

class CustomFieldTypeDefinition
{

    /** @var  string */
    private $name;

    /** @var  string */
    private $label;

    /** @var  string */
    private $description;

    /** @var  string */
    private $formType;

    /** @var  mixed */
    private $value;

    /** @var  bool */
    private $isAdmin;

    /** @var  bool */
    private $required;

    /** @var  CustomFields */
    private $customFields;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }



    /**
     * @return string
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * @param string $formType
     */
    public function setFormType($formType)
    {
        $this->formType = $formType;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function isIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * @param bool $isAdmin
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return CustomFields
     */
    public function getCustomFields()
    {
        return $this->customFields;
    }

    /**
     * @param CustomFields $customFields
     */
    public function setCustomFields($customFields)
    {
        $this->customFields = $customFields;
    }




}