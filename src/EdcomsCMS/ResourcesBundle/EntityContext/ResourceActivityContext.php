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
use EdcomsCMS\ResourcesBundle\Model\ResourceActivityInterface;

/**
 * Class ResourceActivityContext
 * @package AppBundle\EntityContext
 *
 * @ORM\Entity()
 *
 * @Context(
 *     label = "Resource activity",
 *     name = "resource_activity",
 * )
 */
class ResourceActivityContext extends StructureContext implements StructureContextInterface
{

    /**
     * @var ResourceActivityInterface
     *
     * @ORM\OneToOne(targetEntity="EdcomsCMS\ResourcesBundle\Model\ResourceActivityInterface", cascade={"persist"})
     * @ORM\JoinColumn(name="resource_activity_id", referencedColumnName="id")
     */
    protected $context;

    /**
     * @return ResourceActivityInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param ResourceActivityInterface $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
}