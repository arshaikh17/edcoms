<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PageMetadata
 * @package EdcomsCMS\ContentBundle\Entity
 *
 * @ORM\Entity()
 */
class PageMetadata
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
     *
     * @ORM\OneToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\Structure", mappedBy="pageMetadata")
     */
    private $structure;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_title", type="string",nullable=true)
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "The SEO title cannot be longer than {{ limit }} characters"
     * )
     */
    private $seoTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_description", type="string",nullable=true)
     * @Assert\Length(
     *      min = 10,
     *      max = 160,
     *      minMessage = "The SEO description must be at least {{ limit }} characters long",
     *      maxMessage = "The SEO description cannot be longer than {{ limit }} characters"
     * )
     */
    private $seoDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_keywords", type="text",nullable=true)
     */
    private $seoKeywords;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hide_from_search_engines", type="boolean", nullable=true)
     */
    private $hideFromSearchEngines;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string",nullable=true)
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "The title cannot be longer than {{ limit }} characters"
     * )
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string",nullable=true)
     * @Assert\Length(
     *      min = 10,
     *      max = 160,
     *      minMessage = "The description must be at least {{ limit }} characters long",
     *      maxMessage = "The description cannot be longer than {{ limit }} characters"
     * )
     */
    private $description;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\Media")
     */
    private $image;

    /**
     * @var float
     *
     * @ORM\Column(name="priority", type="float", nullable=true)
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "The priority connot be lest than {{ limit }}",
     *      max = 1,
     *      maxMessage = "The priority cannot be more than {{ limit }}"
     * )
     */
    private $seoPriority;

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
     * Set seoTitle
     *
     * @param string $seoTitle
     *
     * @return PageMetadata
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }

    /**
     * Get seoTitle
     *
     * @return string
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * Set seoDescription
     *
     * @param string $seoDescription
     *
     * @return PageMetadata
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    /**
     * Get seoDescription
     *
     * @return string
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * Set seoKeywords
     *
     * @param string $seoKeywords
     *
     * @return PageMetadata
     */
    public function setSeoKeywords($seoKeywords)
    {
        $this->seoKeywords = $seoKeywords;

        return $this;
    }

    /**
     * Get seoKeywords
     *
     * @return string
     */
    public function getSeoKeywords()
    {
        return $this->seoKeywords;
    }

    /**
     * Set hideFromSearchEngines
     *
     * @param boolean $hideFromSearchEngines
     *
     * @return PageMetadata
     */
    public function setHideFromSearchEngines($hideFromSearchEngines)
    {
        $this->hideFromSearchEngines = $hideFromSearchEngines;

        return $this;
    }

    /**
     * Get hideFromSearchEngines
     *
     * @return boolean
     */
    public function getHideFromSearchEngines()
    {
        return $this->hideFromSearchEngines;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return PageMetadata
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
     * Set description
     *
     * @param string $description
     *
     * @return PageMetadata
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set structure
     *
     * @param \EdcomsCMS\ContentBundle\Entity\Structure $structure
     *
     * @return PageMetadata
     */
    public function setStructure(\EdcomsCMS\ContentBundle\Entity\Structure $structure = null)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Get structure
     *
     * @return \EdcomsCMS\ContentBundle\Entity\Structure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Set image
     *
     * @param \EdcomsCMS\ContentBundle\Entity\Media $image
     *
     * @return PageMetadata
     */
    public function setImage(\EdcomsCMS\ContentBundle\Entity\Media $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \EdcomsCMS\ContentBundle\Entity\Media
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set seoPriority
     *
     * @param float $seoPriority
     *
     * @return PageMetadata
     */
    public function setSeoPriority($seoPriority)
    {
        $this->seoPriority = $seoPriority;

        return $this;
    }

    /**
     * Get seoPriority
     *
     * @return float
     */
    public function getSeoPriority()
    {
        return $this->seoPriority;
    }
}
