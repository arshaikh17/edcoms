<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */
namespace EdcomsCMS\ResourcesBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface;

class FilterBuilderServiceProvider
{
    private $container;
    private $filterBuilders;

    public function __construct(ContainerInterface $container, array $filterBuilders = array())
    {
        $this->container = $container;
        $this->filterBuilders = $filterBuilders;
    }

    public function get($name, array $options = array())
    {
        if (!isset($this->filterBuilders[$name])) {
            throw new \InvalidArgumentException(sprintf('The filter "%s" is not defined.', $name));
        }

        list($id, $method) = $this->filterBuilders[$name];

        return $this->container->get($id)->$method($options);
    }

    public function has($name, array $options = array())
    {
        return isset($this->filterBuilders[$name]);
    }
}