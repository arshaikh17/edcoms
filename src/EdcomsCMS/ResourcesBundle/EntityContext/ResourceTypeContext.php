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
use EdcomsCMS\ResourcesBundle\Model\ResourceTypeInterface;

/**
 * Class ResourceTypeContext
 * @package AppBundle\EntityContext
 *
 * @ORM\Entity()
 *
 * @Context(
 *     label = "Resource type",
 *     name = "resource_type",
 * )
 */
class ResourceTypeContext extends StructureContext implements StructureContextInterface
{

    /**
     * @var ResourceTypeInterface
     *
     * @ORM\OneToOne(targetEntity="EdcomsCMS\ResourcesBundle\Model\ResourceTypeInterface", cascade={"persist"})
     * @ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")
     */
    protected $context;

    /**
     * @return ResourceTypeInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param ResourceTypeInterface $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
}