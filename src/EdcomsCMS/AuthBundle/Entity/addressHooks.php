<?php

namespace EdcomsCMS\AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * address_hooks
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\AuthBundle\Entity\addressHooksRepository")
 */
class addressHooks
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
     * @var integer
     *
     * @ORM\Column(name="addressID", type="integer")
     */
    private $addressID;

    /**
     * @var string
     *
     * @ORM\Column(name="hook_type", type="string", length=8)
     */
    private $hookType;

    /**
     * @var integer
     *
     * @ORM\Column(name="typeID", type="integer")
     */
    private $typeID;


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
     * Set addressID
     *
     * @param integer $addressID
     * @return address_hooks
     */
    public function setAddressID($addressID)
    {
        $this->addressID = $addressID;

        return $this;
    }

    /**
     * Get addressID
     *
     * @return integer 
     */
    public function getAddressID()
    {
        return $this->addressID;
    }

    /**
     * Set hookType
     *
     * @param string $hookType
     * @return address_hooks
     */
    public function setHookType($hookType)
    {
        $this->hookType = $hookType;

        return $this;
    }

    /**
     * Get hookType
     *
     * @return string 
     */
    public function getHookType()
    {
        return $this->hookType;
    }

    /**
     * Set typeID
     *
     * @param integer $typeID
     * @return address_hooks
     */
    public function setTypeID($typeID)
    {
        $this->typeID = $typeID;

        return $this;
    }

    /**
     * Get typeID
     *
     * @return integer 
     */
    public function getTypeID()
    {
        return $this->typeID;
    }
}
