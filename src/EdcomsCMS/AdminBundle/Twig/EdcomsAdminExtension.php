<?php

namespace EdcomsCMS\AdminBundle\Twig;

use Symfony\Component\DependencyInjection\Container;

class EdcomsAdminExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('javascriptAssets', array($this, 'getJavascripts')),
            new \Twig_SimpleFunction('stylesheetAssets', array($this, 'getStylesheets')),
            new \Twig_SimpleFunction('class', array($this, 'getClass')),
        );
    }

    public function getJavascripts()
    {

        return $this->container->getParameter('edcoms.admin.assets.javascripts');
    }

    public function getStylesheets()
    {
        return $this->container->getParameter('edcoms.admin.assets.stylesheets');
    }

    public function getClass($object, $includeNameSpace=false)
    {
        $rfc = new \ReflectionClass($object);
        return $includeNameSpace===true ? $rfc->getName() : $rfc->getShortName();
    }

    public function getName()
    {
        return 'edcoms.admin.twig_extension';
    }

    public function getGlobals() {
        return [
            'LEGACY_VIDEO_PLAYER_SNIPPET' => $this->container->getParameter('edcoms.admin.video.legacy_player_snippet')
        ];
    }

}
