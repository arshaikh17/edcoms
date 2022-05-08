<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use EdcomsCMS\ResourcesBundle\Model\Filter\FilterableEntityInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class BaseAgeGroup
 * @package EdcomsCMS\ResourcesBundle\Model
 *
 * @UniqueEntity("slug")
 */
class BaseAgeGroup implements AgeGroupInterface, FilterableEntityInterface
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
     * @var integer
     *
     * @ORM\Column(name="min_age", type="integer")
     * @Assert\Range(
     *      min = 0,
     *      max = 100,
     *      minMessage = "Min age cannot be below {{ limit }}",
     *      maxMessage = "Min age cannot be above {{ limit }}"
     * )
     */
    protected $minAge;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_age", type="integer")
     * @Assert\Range(
     *      min = 1,
     *      max = 100,
     *      minMessage = "Max age cannot be below {{ limit }}",
     *      maxMessage = "Max age cannot be above {{ limit }}"
     * )
     */
    protected $maxAge;

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

    /**
     * @return int
     */
    public function getMinAge()
    {
        return $this->minAge;
    }

    /**
     * @param int $minAge
     */
    public function setMinAge($minAge)
    {
        $this->minAge = $minAge;
    }

    /**
     * @return int
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * @param int $maxAge
     */
    public function setMaxAge($maxAge)
    {
        $this->maxAge = $maxAge;
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

    public function __toString()
    {
        return $this->getTitle() ?: '';
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