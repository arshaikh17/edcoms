<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use EdcomsCMS\ContentBundle\Validator\Constraints\ValidSitemapCustomURL;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class SitemapCustomURL
 *
 * @package EdcomsCMS\ContentBundle\Entity
 *
 * @ORM\Entity()
 * @UniqueEntity(fields={"url"})
 */
class SitemapCustomURL {

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
     * @ORM\Column(name="seo_title", type="string", nullable=false, unique=true)
     */
    private $url;

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
    private $priority;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active;

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
     * Set url
     *
     * @param string $url
     *
     * @return SitemapCustomURL
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
     * Set priority
     *
     * @param float $priority
     *
     * @return SitemapCustomURL
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return float
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return SitemapCustomURL
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

    public function __toString() {
        return $this->getUrl() ?: '';
    }
}
