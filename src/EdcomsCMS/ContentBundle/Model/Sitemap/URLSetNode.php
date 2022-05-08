<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Model\Sitemap;

use JMS\Serializer\Annotation as JMS;

/**
 * Class URLSetNode
 *
 * @package EdcomsCMS\ContentBundle\Model\Sitemap
 * @JMS\XmlRoot("urlset")
 * @JMS\XmlNamespace(uri="http://www.sitemaps.org/schemas/sitemap/0.9")
 * @JMS\XmlNamespace(uri="http://www.google.com/schemas/sitemap-image/1.1", prefix="image")
 * @JMS\XmlNamespace(uri="http://www.w3.org/1999/xhtml", prefix="xhtml")
 */
class URLSetNode {

    /**
     * @var array | \EdcomsCMS\ContentBundle\Model\Sitemap\URLNode
     *
     * @JMS\XmlList(inline = true, entry = "url")
     */
    private $urlNodes;

    public function __construct() {
        $this->urlNodes = [];
    }

    /**
     * @param \EdcomsCMS\ContentBundle\Model\Sitemap\URLNode $urlNode
     *
     * @return $this
     */
    public function addURLNode(URLNode $urlNode){
        if(!in_array($urlNode, $this->urlNodes)){
            $this->urlNodes[] = $urlNode;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getURLNodes(){
        return $this->urlNodes;
    }
}