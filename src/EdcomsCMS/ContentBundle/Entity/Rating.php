<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use EdcomsCMS\AuthBundle\Entity\cmsUsers;

/**
 * SEO
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\RatingRepository")
 */
class Rating
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
     * @var Structure
     * @ORM\ManyToOne(targetEntity="Structure", inversedBy="rating", fetch="EAGER")
     * @ORM\JoinColumn(name="structureID", referencedColumnName="id")
     */
    private $structure;

    /**
     * @var cmsUsers
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUsers", fetch="EAGER")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    private $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="rating", type="integer", length=4)
     */
    private $rating;
    
    /**
     * @var datetime
     * @ORM\Column(name="addedOn", type="datetime", nullable=true)
     */
    private $addedOn;

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
     * Set Structure
     *
     * @param Structure $structure
     * @param boolean $recurs
     * @return Rating
     */
    public function setStructure(Structure $structure, $recurs = true)
    {
        $this->structure = $structure;
        if ($recurs) {
            $structure->addRating($this, false);
        }
        return $this;
    }

    /**
     * Get Structure
     *
     * @return Structure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Set user
     *
     * @param cmsUsers $user
     * @return Rating
     */
    public function setUser(cmsUsers $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return cmsUsers
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set rating
     *
     * @param integer $rating
     * @return Rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get Rating
     *
     * @return integer
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set addedOn
     * @param DateTime $addedOn
     * @return Rating
     */
    public function setAddedOn($addedOn)
    {
        $this->addedOn = $addedOn;
        return $this;
    }
    
    /**
     * Get addedOn
     * @return datetime
     */
    public function getAddedOn()
    {
        return $this->addedOn;
    }
}
