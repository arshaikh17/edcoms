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
use EdcomsCMS\ResourcesBundle\Model\ResourceSubjectInterface;

/**
 * Class ResourceSubjectContext
 * @package AppBundle\EntityContext
 *
 * @ORM\Entity()
 *
 * @Context(
 *     label = "Resource subject",
 *     name = "resource_subject",
 * )
 */
class ResourceSubjectContext extends StructureContext implements StructureContextInterface
{

    /**
     * @var ResourceSubjectInterface
     *
     * @ORM\OneToOne(targetEntity="EdcomsCMS\ResourcesBundle\Model\ResourceSubjectInterface", cascade={"persist"})
     * @ORM\JoinColumn(name="resource_subject_id", referencedColumnName="id")
     */
    protected $context;

    /**
     * @return ResourceSubjectInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param ResourceSubjectInterface $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
}