<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Model\Sitemap;

use JMS\Serializer\Annotation as JMS;

/**
 * Class ImageNode
 *
 * @package EdcomsCMS\ContentBundle\Model\Sitemap
 */
class ImageNode {

    /**
     * @var string
     *
     * @JMS\SerializedName("image:loc")
     * @JMS\XmlElement(cdata=false)
     */
    private $path;

    public function __construct($imagePath){
        $this->path = $imagePath;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
}