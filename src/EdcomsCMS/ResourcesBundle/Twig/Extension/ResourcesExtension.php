<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\Container;

class ResourcesExtension extends \Twig_Extension
{

    /** @var Container  */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('array_unset', array($this, 'arrayUnset')),
        );
    }

    public function arrayUnset($array, $key)
    {
        unset($array[$key]);

        return $array;
    }

    public function getName()
    {
        return 'edcoms.resources.twig_extension';
    }
}