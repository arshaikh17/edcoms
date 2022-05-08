<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use EdcomsCMS\ContentBundle\Validator\Constraints\ValidRedirect;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class URLRedirect
 * @package EdcomsCMS\ContentBundle\Entity
 *
 * @ORM\Table("edcoms_url_redirect")
 * @ORM\Entity()
 *
 * @JMS\ExclusionPolicy("all")
 * @ValidRedirect()
 * @UniqueEntity(fields={"url"})
 */
class URLRedirect
{

    const TYPE_STRUCTURE = 'structure';
    const TYPE_FREE_TEXT = 'free-text';

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
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\Structure", inversedBy="urlRedirects")
     * @ORM\JoinColumn(name="destination_structure_id", referencedColumnName="id")
     */
    private $destinationStructure;

    /**
     * @var Structure
     *
     * @ORM\OneToMany(targetEntity="EdcomsCMS\ContentBundle\Entity\URLRedirectUsage", mappedBy="urlRedirect", cascade={"remove"})
     */
    private $usage;

    /**
     * @var string
     *
     * @ORM\Column(name="destination_link", type="string", length=255, nullable=true)
     */
    private $destinationLink;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, unique=true)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="is_vanity_url", type="boolean", nullable=true)
     */
    private $isVanityUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="is_temporary_redirect", type="boolean", nullable=true)
     */
    private $isTemporaryRedirect;

    /**
     * @var string
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="track_usage", type="boolean", nullable=true)
     */
    private $trackUsage;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\UserBundle\Entity\User")
     */
    private $createdBy;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\UserBundle\Entity\User")
     */
    private $lastUpdatedBy;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->usages = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set destinationLink
     *
     * @param string $destinationLink
     *
     * @return URLRedirect
     */
    public function setDestinationLink($destinationLink)
    {
        $this->destinationLink = $destinationLink;

        return $this;
    }

    /**
     * Get destinationLink
     *
     * @return string
     */
    public function getDestinationLink()
    {
        return $this->destinationLink;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return URLRedirect
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
     * Set url
     *
     * @param string $url
     *
     * @return URLRedirect
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set isVanityUrl
     *
     * @param boolean $isVanityUrl
     *
     * @return URLRedirect
     */
    public function setIsVanityUrl($isVanityUrl)
    {
        $this->isVanityUrl = $isVanityUrl;

        return $this;
    }

    /**
     * Get isVanityUrl
     *
     * @return boolean
     */
    public function getIsVanityUrl()
    {
        return $this->isVanityUrl;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return URLRedirect
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set destinationStructure
     *
     * @param \EdcomsCMS\ContentBundle\Entity\Structure $destinationStructure
     *
     * @return URLRedirect
     */
    public function setDestinationStructure(\EdcomsCMS\ContentBundle\Entity\Structure $destinationStructure = null)
    {
        $this->destinationStructure = $destinationStructure;

        return $this;
    }

    /**
     * Get destinationStructure
     *
     * @return \EdcomsCMS\ContentBundle\Entity\Structure
     */
    public function getDestinationStructure()
    {
        return $this->destinationStructure;
    }

    /**
     * Set createdBy
     *
     * @param \EdcomsCMS\UserBundle\Entity\User $createdBy
     *
     * @return URLRedirect
     */
    public function setCreatedBy(\EdcomsCMS\UserBundle\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \EdcomsCMS\UserBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return URLRedirect
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set isTemporaryRedirect
     *
     * @param boolean $isTemporaryRedirect
     *
     * @return URLRedirect
     */
    public function setIsTemporaryRedirect($isTemporaryRedirect)
    {
        $this->isTemporaryRedirect = $isTemporaryRedirect;

        return $this;
    }

    /**
     * Get isTemporaryRedirect
     *
     * @return boolean
     */
    public function isTemporaryRedirect()
    {
        return $this->isTemporaryRedirect;
    }

    public function __toString()
    {
        return $this->getUrl() ?: '';
    }

    public function getRedirectPath(){
        $redirectPath = null;
        switch($this->getType()){
            case self::TYPE_FREE_TEXT:
                $redirectPath = $this->getDestinationLink();
                break;
            case self::TYPE_STRUCTURE:
                $redirectPath = sprintf('/%s', $this->getDestinationStructure()->getFullLink(true));
                break;
        }
        return $redirectPath;
    }

    public function getRedirectStatusCode(){
        return $this->isTemporaryRedirect() ? 302 : 301;
    }


    /**
     * Get isTemporaryRedirect
     *
     * @return boolean
     */
    public function getIsTemporaryRedirect()
    {
        return $this->isTemporaryRedirect;
    }

    /**
     * Add usage
     *
     * @param \EdcomsCMS\ContentBundle\Entity\URLRedirectUsage $usage
     *
     * @return URLRedirect
     */
    public function addUsage(\EdcomsCMS\ContentBundle\Entity\URLRedirectUsage $usage)
    {
        $this->usage[] = $usage;

        return $this;
    }

    /**
     * Remove usage
     *
     * @param \EdcomsCMS\ContentBundle\Entity\URLRedirectUsage $usage
     */
    public function removeUsage(\EdcomsCMS\ContentBundle\Entity\URLRedirectUsage $usage)
    {
        $this->usage->removeElement($usage);
    }

    /**
     * Get usage
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * Set trackUsage
     *
     * @param boolean $trackUsage
     *
     * @return URLRedirect
     */
    public function setTrackUsage($trackUsage)
    {
        $this->trackUsage = $trackUsage;

        return $this;
    }

    /**
     * Get trackUsage
     *
     * @return boolean
     */
    public function getTrackUsage()
    {
        return $this->trackUsage;
    }

    /**
     * Set lastUpdatedBy
     *
     * @param \EdcomsCMS\UserBundle\Entity\User $lastUpdatedBy
     *
     * @return URLRedirect
     */
    public function setLastUpdatedBy(\EdcomsCMS\UserBundle\Entity\User $lastUpdatedBy = null)
    {
        $this->lastUpdatedBy = $lastUpdatedBy;

        return $this;
    }

    /**
     * Get lastUpdatedBy
     *
     * @return \EdcomsCMS\UserBundle\Entity\User
     */
    public function getLastUpdatedBy()
    {
        return $this->lastUpdatedBy;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return URLRedirect
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
