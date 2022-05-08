<?php

namespace EdcomsCMS\AuthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contact
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\AuthBundle\Entity\ContactRepository")
 */
class Contact
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
     * @ORM\Column(name="type", type="string", length=50)
     */
    private $type;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="title", type="string", length=100)
     */
    private $title;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="value", type="string", length=100)
     */
    private $value;

    
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Person", mappedBy="contacts")
     */
    private $person;
    
    public function __construct()
    {
        $this->person = new ArrayCollection();
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
     * Set type
     *
     * @param string $type
     * @return Contact
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
     * Set title
     *
     * @param string $title
     * @return Contact
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Contact
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
    
    public function addPerson(Person $person)
    {
        $this->person[] = $person;
    }
    
    public function setPerson($persons)
    {
        if (!is_array($persons) || !$persons instanceof ArrayCollection) {
            $persons = [$persons];
        }
        foreach ($persons as $person) {
            $this->addPerson($person);
        }
        return $this;
    }

    /**
     * Remove person
     *
     * @param \EdcomsCMS\AuthBundle\Entity\Person $person
     */
    public function removePerson(\EdcomsCMS\AuthBundle\Entity\Person $person)
    {
        $this->person->removeElement($person);

    }
    
    public function getPerson()
    {
        return $this->person;
    }

    public function toJSON() {
        unset($this->json);
        $this->json = get_object_vars($this);
        $this->json = array_filter($this->json, array(&$this, 'json_filter'), ARRAY_FILTER_USE_BOTH);
        unset($this->json['id']);
        unset($this->json['person']);
        return $this->json;
    }
    public function json_filter($val, $key) {
        if (!strstr($key, '__')) {
            return true;
        }
        return false;
    }

    public function __toString()
    {
        return $this->getTitle() ? $this->getTitle() : '';
    }
}
