<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\EventListener\Serialisation;

use EdcomsCMS\ContentBundle\Entity\Media;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Router;

class SerializationListener implements EventSubscriberInterface
{

    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    static public function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.post_serialize', 'class' => Media::class, 'method' => 'onMediaPostSerialize')
        );
    }

    public function onMediaPostSerialize(ObjectEvent $event)
    {
        $media = $event->getObject();
        $event->getVisitor()->addData('src', $this->container->get('edcoms.content.service.media.url_generator')->generateMediaUrl($media,Router::ABSOLUTE_URL));
    }

}