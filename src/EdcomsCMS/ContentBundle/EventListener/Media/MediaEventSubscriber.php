<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\EventListener\Media;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use EdcomsCMS\ContentBundle\Entity\Media;
use Symfony\Component\DependencyInjection\Container;

class MediaEventSubscriber implements EventSubscriber
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
        );
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $media = $this->getMedia($args);
        if($media && $media->getAttachment()){
            // Update media
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $media = $this->getMedia($args);
        if($media && $media->getAttachment() && $media->isUploadedWithAttachment()){
            $this->container->get('edcoms.content.helper.media.uploader')->uploadMedia($media);
        }
    }

    public function getMedia(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        return $entity instanceof Media ? $entity : false;
    }
}