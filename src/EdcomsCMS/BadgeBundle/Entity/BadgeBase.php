<?php
/**
 * Created by PhpStorm.
 * User: stevenduncan-brown
 * Date: 05/10/2016
 * Time: 15:19
 */

namespace EdcomsCMS\BadgeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use EdcomsCMS\ContentBundle\Entity\Media;

/**
 * Class BadgeBase
 * @package EdcomsCMS\BadgeBundle\Entity
 *
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="badge_type", type="string")
 */
abstract class BadgeBase
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Name of the badge
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    protected $name;

    /**
     * The slug of the uri on which this badge will be made public
     *
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=100, unique=true)
     */
    protected $slug;

    /**
     * Description of the badge
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=500, nullable=true)
     */
    protected $description;

    /**
     * Group or classification the badges belong to.
     * This is more for grouping the badges on the FE.
     * @var string
     *
     * @ORM\Column(name="group", type="string", length=500, nullable=true)
     */
    protected $group;

    /**
     * Ordering for badges to be pulled out of the db in
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=true)
     */
    protected $order;

    /**
     * Image representation of the badge
     * @var Media
     *
     * @ORM\OneToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\Media")
     * @ORM\JoinColumn(name="mediaID", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $image;

    /**
     * Boolean to signify if the badge is active or inactive
     * @var Boolean
     *
     * @ORM\Column(name="isActive", type="boolean", nullable=true)
     */
    protected $isActive;

    /**
     * Boolean to signify if the badge is to be linked to Open Badges
     * @var Boolean
     *
     * @ORM\Column(name="isOpenBadge", type="boolean", nullable=true)
     */
    protected $isOpenBadge;

    /**
     * The cmsUserGroup eligible to get badge
     * @var cmsUserGroups
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUserGroups")
     * @ORM\joinColumn(name="cmsUserGroupID", referencedColumnName="id", nullable=true)
     */
    protected $cmsUserGroup;

    /**
     * Human readable criteria
     * @var string
     *
     * @ORM\Column(name="readableCriteria", type="string", length=500, nullable=true)
     */
    protected $readableCriteria;

    public function __construct(string $name = null, string $slug = null, $order = null)
    {
        $this->name = (!is_null($name))? $name:null;
        $this->slug = (!is_null($slug))? $slug:null;
        $this->order = (!is_null($order))? $order:null;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return integer
     */
    public function setId($id)
    {
        return $this->id = $id;
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
     * Set name
     *
     * @param string $name
     *
     * @return BadgeBase
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return BadgeBase
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return BadgeBase
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
     * Set group
     *
     * @param string $group
     *
     * @return BadgeBase
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set order
     *
     * @param integer $order
     *
     * @return BadgeBase
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set image
     *
     * @param \EdcomsCMS\ContentBundle\Entity\Media $image
     *
     * @return BadgeBase
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
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return BadgeBase
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set isOpenBadge
     *
     * @param boolean $isOpenBadge
     *
     * @return BadgeBase
     */
    public function setIsOpenBadge($isOpenBadge)
    {
        $this->isOpenBadge = $isOpenBadge;

        return $this;
    }

    /**
     * Get isOpenBadge
     *
     * @return boolean
     */
    public function getIsOpenBadge()
    {
        return $this->isOpenBadge;
    }

    /**
     * Set cmsUserGroup
     *
     * @param \EdcomsCMS\AuthBundle\Entity\cmsUserGroups $cmsUserGroup
     *
     * @return BadgeBase
     */
    public function setCmsUserGroup(\EdcomsCMS\AuthBundle\Entity\cmsUserGroups $cmsUserGroup = null)
    {
        $this->cmsUserGroup = $cmsUserGroup;

        return $this;
    }

    /**
     * Get cmsUserGroup
     *
     * @return \EdcomsCMS\AuthBundle\Entity\cmsUserGroups
     */
    public function getCmsUserGroup()
    {
        return $this->cmsUserGroup;
    }

    /**
     * Set readableCriteria
     *
     * @param string $readableCriteria
     *
     * @return BadgeBase
     */
    public function setReadableCriteria($readableCriteria)
    {
        $this->readableCriteria = $readableCriteria;

        return $this;
    }

    /**
     * Get readableCriteria
     *
     * @return string
     */
    public function getReadableCriteria()
    {
        return $this->readableCriteria;
    }

    public function toJSON($vars=[]) {
        unset($this->json);
        if (empty($vars)) {
            $this->json = get_object_vars($this);

            if (!is_null($this->getImage())) {
                $this->json['image'] = (!is_array($this->json['image'])) ?
                    [
                        'id' => $this->getImage()->getId(),
                        'path' => $this->getImage()->getPath(),
                        'title' => $this->getImage()->getTitle()
                    ] : $this->getImage();
            }
            $this->json['cmsUserGroup'] = (!is_array($this->json['cmsUserGroup'])) ? $this->getCmsUserGroup()->toJSON() : $this->getCmsUserGroup();
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
