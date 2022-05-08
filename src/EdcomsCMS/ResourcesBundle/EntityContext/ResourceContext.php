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
use EdcomsCMS\ResourcesBundle\Model\ResourceInterface;

/**
 * Class ResourceContext
 * @package AppBundle\EntityContext
 *
 * @ORM\Entity()
 *
 * @Context(
 *     label = "Resource",
 *     name = "resource",
 * )
 */
class ResourceContext extends StructureContext implements StructureContextInterface
{

    /**
     * @var ResourceInterface
     *
     * @ORM\OneToOne(targetEntity="EdcomsCMS\ResourcesBundle\Model\ResourceInterface", cascade={"persist", "remove"}, inversedBy="context")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
     */
    protected $context;

    /**
     * @return ResourceInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param ResourceInterface $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
}