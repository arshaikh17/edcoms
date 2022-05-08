<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Content
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\ContentCacheRepository")
 * @UniqueEntity("UUID")
 */
class ContentCache
{
    use \EdcomsCMS\ContentBundle\Traits\EntityHydration;
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
     * @var string 
     * @ORM\Column(name="UUID", type="string", length=120, unique=true)
     */
    private $UUID;
    
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=60)
     */
    private $type;

    /**
     * @var text
     *
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
     * Set UUID
     *
     * @param string $uuid
     * @return ContentCache
     */
    public function setUuid($uuid)
    {
        $this->UUID = $uuid;

        return $this;
    }

    /**
     * Get UUID
     *
     * @return string 
     */
    public function getUuid()
    {
        return $this->UUID;
    }
    
    /**
     * Set type
     *
     * @param string $type
     * @return ContentCache
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Set value
     *
     * @param string $value
     * @return ContentCache
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
}
