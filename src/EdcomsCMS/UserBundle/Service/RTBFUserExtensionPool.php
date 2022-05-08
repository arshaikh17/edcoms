<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\UserBundle\Service;

/**
 * Class RTBFUserExtensionPool
 *
 * @package EdcomsCMS\UserBundle\Service
 */
class RTBFUserExtensionPool {

    /**
     * @var \EdcomsCMS\UserBundle\Service\RTBFUserExtensionInterface[]
     */
    private $extensions;

    /**
     * RTBFUserExtensionPool constructor.
     */
    public function __construct()
    {
        $this->extensions = array();
    }

    public function addRTBFUserExtension(RTBFUserExtensionInterface $extension)
    {
        $this->extensions[] = $extension;
    }

    /**
     * @return \EdcomsCMS\UserBundle\Service\RTBFUserExtensionInterface[]
     */
    public function getRTBFUserExtensions(){
        return $this->extensions;
    }

}