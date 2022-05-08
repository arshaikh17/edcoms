<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\EntityContext;

use EdcomsCMS\ContentBundle\Entity\StructureContext;
use EdcomsCMS\ContentBundle\Entity\StructureContextInterface;
use EdcomsCMS\ContentBundle\Annotation\StructureContext as Context;
use Doctrine\ORM\Mapping as ORM;
use EdcomsCMS\ResourcesBundle\Entity\ResourceTopic;
use EdcomsCMS\ResourcesBundle\Model\ResourceTopicInterface;

/**
 * Class ResourceTopicContext
 * @package AppBundle\EntityContext
 *
 * @ORM\Entity()
 *
 * @Context(
 *     label = "Resource topic",
 *     name = "resource_topic",
 * )
 */
class ResourceTopicContext extends StructureContext implements StructureContextInterface
{

    /**
     * @var ResourceTopicInterface
     *
     * @ORM\OneToOne(targetEntity="EdcomsCMS\ResourcesBundle\Model\ResourceTopicInterface", cascade={"persist"})
     * @ORM\JoinColumn(name="resource_topic_id", referencedColumnName="id")
     */
    protected $context;

    /**
     * @return ResourceTopicInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param ResourceTopicInterface $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
}