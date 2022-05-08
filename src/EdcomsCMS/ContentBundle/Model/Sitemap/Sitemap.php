<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Model\Sitemap;

use JMS\Serializer\Annotation as JMS;

/**
 * Class Sitemap
 *
 * @package EdcomsCMS\ContentBundle\Model\Sitemap
 *
 * @JMS\ExclusionPolicy("all")
 */
class Sitemap {

    /**
     * @var \EdcomsCMS\ContentBundle\Model\Sitemap\URLSetNode
     */
    private $urlSet;


    public function __construct(){
        $this->urlSet = new URLSetNode();
    }

    /**
     * @param \EdcomsCMS\ContentBundle\Model\Sitemap\URLNode $urlNode
     *
     * @return $this
     */
    public function addURLNode(URLNode $urlNode){
        $this->urlSet->addURLNode($urlNode);
        return $this;
    }

    /**
     * @return \EdcomsCMS\ContentBundle\Model\Sitemap\URLSetNode
     */
    public function getURLSet(){
        return $this->urlSet;
    }

}