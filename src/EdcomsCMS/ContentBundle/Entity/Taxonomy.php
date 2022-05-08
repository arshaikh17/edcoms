<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Taxonomy
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\TaxonomyRepository")
 */
class Taxonomy
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
     * @ORM\Column(name="label", type="string", length=20)
     */
    private $label;

    /**
     * @var integer
     *
     * @ORM\Column(name="added_by", type="integer")
     */
    private $addedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="added_on", type="datetime")
     */
    private $addedOn;

    /**
     * @var string
     *
     * @ORM\Column(name="colour", type="string", length=7)
     */
    private $colour;


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
     * Set label
     *
     * @param string $label
     * @return Taxonomy
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
     * Set addedBy
     *
     * @param integer $addedBy
     * @return Taxonomy
     */
    public function setAddedBy($addedBy)
    {
        $this->addedBy = $addedBy;

        return $this;
    }

    /**
     * Get addedBy
     *
     * @return integer 
     */
    public function getAddedBy()
    {
        return $this->addedBy;
    }

    /**
     * Set addedOn
     *
     * @param \DateTime $addedOn
     * @return Taxonomy
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

    /**
     * Set colour
     *
     * @param string $colour
     * @return Taxonomy
     */
    public function setColour($colour)
    {
        $this->colour = $colour;

        return $this;
    }

    /**
     * Get colour
     *
     * @return string 
     */
    public function getColour()
    {
        return $this->colour;
    }
}
