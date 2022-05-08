<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Service;

use EdcomsCMS\ResourcesBundle\Entity\Resource;

class EdcomsResourcesConfigurationService
{

    const RESOURCE = 'resource';
    const RESOURCE_VIDEO = 'video_resource';
    const RESOURCE_SUBJECT = 'resource_subject';
    const RESOURCE_TYPE = 'resource_type';
    const RESOURCE_TOPIC = 'resource_topic';
    const AGE_GROUP = 'age_group';

    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function isResourceClassOverwritten(){
        return $this->config['resource']['entity_class']!=Resource::class ? true : false;
    }

    /**
     * @param $entity
     * @return bool
     */
    public function getEntityClass($entity){
        return $this->isEntityValid($entity) ? $this->config[$entity]['entity_class'] : false;
    }

    public function getBaseResource(){
        return $this->config['base_resource'];
    }

    public function getAPIResourceRoute(){
        return $this->config['filtering']['api']['resource_route'];
    }

    public function getAPIBatchValue(){
        return $this->config['filtering']['api']['options']['batch']['default'];
    }

    /**
     * @param $entity
     * @return bool
     */
    private function isEntityValid($entity){
        return in_array($entity,[
            self::RESOURCE,
            self::RESOURCE_VIDEO,
            self::RESOURCE_SUBJECT,
            self::RESOURCE_TYPE,
            self::RESOURCE_TOPIC,
            self::AGE_GROUP,
        ]);
    }
}