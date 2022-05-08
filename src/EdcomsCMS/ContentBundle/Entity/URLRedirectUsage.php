<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;

/**
 * Class URLRedirectUsage
 * @package EdcomsCMS\ContentBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table("edcoms_url_redirect_usage")
 *
 * @JMS\ExclusionPolicy("all")
 */
class URLRedirectUsage
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var URLRedirect
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\URLRedirect", inversedBy="usage")
     * @ORM\JoinColumn(name="urlredirect_id", referencedColumnName="id")
     */
    private $urlRedirect;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="user_agent", type="string", length=255, nullable=true)
     */
    private $userAgent;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=255, nullable=true)
     */
    private $ipAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="referrer", type="string", length=255, nullable=true)
     */
    private $referrer;

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return URLRedirectUsage
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
     * Set userAgent
     *
     * @param string $userAgent
     *
     * @return URLRedirectUsage
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Get userAgent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set ipAddress
     *
     * @param string $ipAddress
     *
     * @return URLRedirectUsage
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set referrer
     *
     * @param string $referrer
     *
     * @return URLRedirectUsage
     */
    public function setReferrer($referrer)
    {
        $this->referrer = $referrer;

        return $this;
    }

    /**
     * Get referrer
     *
     * @return string
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * Set urlRedirect
     *
     * @param \EdcomsCMS\ContentBundle\Entity\URLRedirect $urlRedirect
     *
     * @return URLRedirectUsage
     */
    public function setUrlRedirect(\EdcomsCMS\ContentBundle\Entity\URLRedirect $urlRedirect = null)
    {
        $this->urlRedirect = $urlRedirect;

        return $this;
    }

    /**
     * Get urlRedirect
     *
     * @return \EdcomsCMS\ContentBundle\Entity\URLRedirect
     */
    public function getUrlRedirect()
    {
        return $this->urlRedirect;
    }
}
