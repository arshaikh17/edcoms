<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Content
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\UserGeneratedContentValuesRepository")
 */
class UserGeneratedContentValues
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
     * @var UserGeneratedContentEntry
     *
     * @ORM\ManyToOne(targetEntity="UserGeneratedContentEntry", inversedBy="userGeneratedContentValues")
     * @ORM\JoinColumn(name="entryID", referencedColumnName="id")
     */
    private $entry;

    /**
     * @var string
     * @ORM\Column(name="field", type="string", length=40)
     */
    private $field;
    
    /**
     * @var string
     * @ORM\Column(name="value", type="text")
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
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set entry
     *
     * @param UserGeneratedContentEntry $entry
     * @return UserGeneratedContentValues
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * Get Field
     *
     * @return UserGeneratedContentEntry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Set Field
     *
     * @param string $field
     * @return UserGeneratedContentValues
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get Field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
    
    /**
     * Set Value
     *
     * @param string $value
     * @return UserGeneratedContentValues
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get Value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
    
    public function toJSON($vars=[]) {
        unset($this->json);
        if (empty($vars)) {
            $this->json = get_object_vars($this);
            unset($this->json['entry']);
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
}
