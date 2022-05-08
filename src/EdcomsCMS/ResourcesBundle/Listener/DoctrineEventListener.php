<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Listener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ClassMetadata;
use EdcomsCMS\ContentBundle\Entity\StructureContext;
use EdcomsCMS\ResourcesBundle\Entity\Resource;
use EdcomsCMS\ResourcesBundle\Entity\ResourceSubject;
use EdcomsCMS\ResourcesBundle\EntityContext\AgeGroupContext;
use EdcomsCMS\ResourcesBundle\EntityContext\ResourceActivityContext;
use EdcomsCMS\ResourcesBundle\EntityContext\ResourceContext;
use EdcomsCMS\ResourcesBundle\EntityContext\ResourceSubjectContext;
use EdcomsCMS\ResourcesBundle\EntityContext\ResourceTopicContext;
use EdcomsCMS\ResourcesBundle\EntityContext\ResourceTypeContext;
use EdcomsCMS\ResourcesBundle\EntityContext\VideoResourceContext;
use EdcomsCMS\ResourcesBundle\Service\EdcomsResourcesConfigurationService;

class DoctrineEventListener
{

    /**
     * @var EdcomsResourcesConfigurationService
     */
    private $edcomsResourcesConfiguration;

    /**
     * @var boolean
     */
    private $resourceClassOverwrite;

    public function __construct(EdcomsResourcesConfigurationService $edcomsResourcesConfiguration)
    {
        $this->edcomsResourcesConfiguration = $edcomsResourcesConfiguration;
        $this->resourceClassOverwrite = $edcomsResourcesConfiguration->isResourceClassOverwritten();
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        /** @var ClassMetadata  $metadata */
        $metadata = $event->getClassMetadata();
        $class = $metadata->getReflectionClass();
        if ($class === null) {
            $class = new \ReflectionClass($metadata->getName());
        }
        switch($class->getName()){
            case StructureContext::class:
                $metadata->setSubclasses([
                    ResourceSubjectContext::class,
                    ResourceContext::class,
                    ResourceTypeContext::class,
                    ResourceTopicContext::class,
                    AgeGroupContext::class,
                    VideoResourceContext::class
                ]);
                $metadata->setDiscriminatorMap(array_merge($metadata->discriminatorMap, [
                        'resourcesubjectcontext'=> ResourceSubjectContext::class,
                        'resourcecontext'=> ResourceContext::class,
                        'resourcetype' => ResourceTypeContext::class,
                        'resourcetopic'=> ResourceTopicContext::class,
                        'agegroupcontext' => AgeGroupContext::class,
                        'videoresourcecontext' => VideoResourceContext::class,
                        'resourceactivity' => ResourceActivityContext::class
                    ])
                );
                break;
            case Resource::class:
                if($this->resourceClassOverwrite==true){
                    $resourceAssociations = ['subjects', 'topics', 'ageGroups','activities'];
                    foreach ($resourceAssociations as $assoc){
                        $associationMapping = $metadata->getAssociationMapping($assoc);
                        $associationMapping['joinTable']['name'] = sprintf('edcoms_%s',$associationMapping['joinTable']['name']);
                        $metadata->setAssociationOverride($assoc, $associationMapping);
                    }
                }

                break;
        }

    }
}