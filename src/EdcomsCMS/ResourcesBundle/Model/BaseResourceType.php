<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use EdcomsCMS\ContentBundle\Entity\Media;
use EdcomsCMS\ResourcesBundle\Model\Filter\FilterableEntityInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class BaseResourceType
 * @package EdcomsCMS\ResourcesBundle\Model
 *
 * @UniqueEntity("slug")
 */
class BaseResourceType implements ResourceTypeInterface, FilterableEntityInterface
{

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string")
     * @Assert\NotBlank()
     *
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", unique=true)
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     */
    protected $slug;

    /**
     * @var bool
     *
     * @ORM\Column(name="use_as_filter", type="boolean", nullable=true)
     */
    protected $useAsFilter;

    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="text", nullable=true)
     */
    protected $summary;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\Media")
     */
    protected $thumbImage;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\Media")
     */
    protected $headerImage;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdOn;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedOn;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    protected $active;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param \DateTime $createdOn
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     * @param \DateTime $updatedOn
     */
    public function setUpdatedOn($updatedOn)
    {
        $this->updatedOn = $updatedOn;
    }

    public function __toString()
    {
        return $this->getTitle() ?: '';
    }

    /**
     * @return Media
     */
    public function getThumbImage()
    {
        return $this->thumbImage;
    }

    /**
     * @param Media $thumbImage
     */
    public function setThumbImage($thumbImage)
    {
        $this->thumbImage = $thumbImage;
    }

    /**
     * @return Media
     */
    public function getHeaderImage()
    {
        return $this->headerImage;
    }

    /**
     * @param Media $headerImage
     */
    public function setHeaderImage($headerImage)
    {
        $this->headerImage = $headerImage;
    }

    /**
     * @return bool
     */
    public function isUseAsFilter()
    {
        return $this->useAsFilter;
    }

    /**
     * @param bool $useAsFilter
     */
    public function setUseAsFilter($useAsFilter)
    {
        $this->useAsFilter = $useAsFilter;
    }

    public function getFilterLabel()
    {
        return $this->getTitle();
    }

    public function getFilterValue()
    {
        return $this->getSlug();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

}