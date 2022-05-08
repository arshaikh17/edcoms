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
use EdcomsCMS\ResourcesBundle\Model\AgeGroupInterface;

/**
 * Class AgeGroupContext
 * @package AppBundle\EntityContext
 *
 * @ORM\Entity()
 *
 * @Context(
 *     label = "Age group",
 *     name = "age_group",
 * )
 */
class AgeGroupContext extends StructureContext implements StructureContextInterface
{

    /**
     * @var AgeGroupInterface
     *
     * @ORM\OneToOne(targetEntity="EdcomsCMS\ResourcesBundle\Model\AgeGroupInterface", cascade={"persist"})
     * @ORM\JoinColumn(name="age_group_id", referencedColumnName="id")
     */
    protected $context;

    /**
     * @return AgeGroupInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param AgeGroupInterface $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
}