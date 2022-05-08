<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Model\Sitemap;

use JMS\Serializer\Annotation as JMS;

/**
 * Class URLNode
 *
 * @package EdcomsCMS\ContentBundle\Model\Sitemap
 * @JMS\XmlRoot("urlset")
 * @JMS\ExclusionPolicy("all")
 */
class URLNode {

    /**
     * @var string
     *
     * @JMS\Expose
     * @JMS\XmlElement(cdata=false)
     * @JMS\SerializedName("loc")
     */
    private $loc;

    /**
     * @var float
     *
     * @JMS\Expose
     */
    private $priority=0.5;

    /**
     * @var \DateTime
     *
     * @JMS\Expose
     * @JMS\XmlElement(cdata=false)
     * @JMS\SerializedName("lastmod")
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    private $lastModified;

    /**
     * @var array
     *
     * @JMS\XmlList(inline = true, entry = "image:image")
     * @JMS\Expose
     */
    private $images;


    public function __construct(){
        $this->images = [];
    }

    /**
     * @return \DateTime
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @param \DateTime $lastModified
     * @return $this
     */
    public function setLastModified(\DateTime $lastModified)
    {
        $this->lastModified = $lastModified;
        return $this;
    }

    /**
     * @return string
     */
    public function getLoc()
    {
        return $this->loc;
    }

    /**
     * @param string $loc
     * @return $this
     */
    public function setLoc($loc)
    {
        $this->loc = $loc;
        return $this;
    }

    /**
     * @return float
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @param URLImage $image
     * @return $this
     */
    public function addImage(ImageNode $image)
    {
        if(!in_array($image, $this->images)){
            $this->images[] = $image;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getImages(){
        return $this->images;
    }
}