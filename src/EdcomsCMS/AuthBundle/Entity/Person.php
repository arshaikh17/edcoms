<?php

namespace EdcomsCMS\AuthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Person
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\AuthBundle\Entity\PersonRepository")
 */
class Person
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
     * @ORM\Column(name="first_name", type="string", length=100)
     */
    private $firstName;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="last_name", type="string", length=100)
     */
    private $lastName;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Contact", inversedBy="person", cascade={"persist"})
     * @ORM\JoinTable(name="contact_hooks",
     *                joinColumns={@ORM\JoinColumn(name="personID", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="id", referencedColumnName="id")})
     */
    private $contacts;

    public function __construct() {
        $this->contacts = new ArrayCollection();
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
     * Set firstName
     *
     * @param string $firstName
     * @return Person
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return Person
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }
    
    public function getContacts()
    {
        return $this->contacts;
    }
    public function addContact(Contact $contact)
    {
        if (!$this->hasContact($contact)) {
            $this->contacts[] = $contact;
        }
    }
    public function hasContact(Contact $contact)
    {
        return $this->getContacts()->contains($contact);
    }
    public function setContacts($contacts)
    {
        foreach ($contacts as $contact) {
            $this->addContact($contact);
        }
        return $this;
    }
    
    public function toJSON($vars=[]) {
        unset($this->json);
        if (empty($vars)) {
            $this->json = get_object_vars($this);
            
            $tempcontacts = [];
            $emailFound = false;
            foreach ($this->contacts as $key => $contact) {
                //make sure the email contact is always coming as the first item
                if ($contact->getType() == 'email' && !$emailFound) {
                    array_unshift($tempcontacts, $contact->toJSON());
                    $emailFound = true;
                } else {
                    $tempcontacts[] = $contact->toJSON();
                }
            }
            $this->json['contacts'] = $tempcontacts;
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
     * Remove contact
     *
     * @param \EdcomsCMS\AuthBundle\Entity\Contact $contact
     */
    public function removeContact(\EdcomsCMS\AuthBundle\Entity\Contact $contact)
    {
        $this->contacts->removeElement($contact);
    }
}
