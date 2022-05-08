<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Service;

use Doctrine\ORM\EntityManager;
use EdcomsCMS\ContentBundle\Entity\Media;
use Symfony\Component\Routing\Router;

/**
 * Class MediaUrlGenerator
 *
 * @package EdcomsCMS\ContentBundle\Service
 */
class MediaUrlGenerator
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    private $router;

    /**
     * @var \EdcomsCMS\ContentBundle\Service\EdcomsContentConfigurationService
     */
    private $configService;

    public function __construct(EntityManager $entityManager, Router $router, EdcomsContentConfigurationService $configurationService)
    {
        $this->em = $entityManager;
        $this->router = $router;
        $this->configService = $configurationService;
    }

    public function generateMediaUrl($media, $referenceType=Router::ABSOLUTE_PATH){
        if(!$media){
            return '';
        }

        if(is_numeric($media)){
            $media = $this->em->getRepository(Media::class)->find($media);
            if(!$media){
                return '';
            }
        }elseif (! ($media instanceof Media)){
            return '';
        }

        /** @var Media $media */

        if($media->getVideoId()){
            // Return plain video ID
            return $media->getVideoId();
        }else{
            $cdnSettings = $this->configService->getCDNSettings();
            $isCDNEnabled = isset($cdnSettings['enabled']) && $cdnSettings['enabled']===true && $cdnSettings['cdn_host'];

            $mediaPath = $media->getPath() ? sprintf('%s/%s',$media->getPath(), $media->getTitle()) : $media->getTitle();
            if($isCDNEnabled){
                // Return CDN URL
                $cdnHost = rtrim($cdnSettings['cdn_host'], '/');
                return sprintf('%s%s', $cdnHost, $this->router
                    ->generate('media_view',
                        array('file'=> $mediaPath, Router::RELATIVE_PATH)));
            }else{
                // Return local URL
                return $this->router
                    ->generate('media_view',
                        array('file'=> $mediaPath, $referenceType));
            }
        }
    }
}
