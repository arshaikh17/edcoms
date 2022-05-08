<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Model\Context;

class StructureContextItemConfig
{

    /** @var  string */
    private $class;

    /** @var  string */
    private $contextClass;

    /** @var  string */
    private $label;

    /** @var  string */
    private $name;

    /** @var  string */
    private $form;

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getContextClass()
    {
        return $this->contextClass;
    }

    /**
     * @param string $contextClass
     */
    public function setContextClass($contextClass)
    {
        $this->contextClass = $contextClass;
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
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param string $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }


}