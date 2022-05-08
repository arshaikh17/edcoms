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
use EdcomsCMS\ResourcesBundle\Model\VideoResourceInterface;

/**
 * Class VideoResourceContext
 * @package AppBundle\EntityContext
 *
 * @ORM\Entity()
 *
 * @Context(
 *     label = "Video Resource",
 *     name = "resource_video",
 * )
 */
class VideoResourceContext extends StructureContext implements StructureContextInterface
{

    /**
     * @var VideoResourceInterface
     *
     * @ORM\OneToOne(targetEntity="EdcomsCMS\ResourcesBundle\Model\VideoResourceInterface", cascade={"persist", "remove"}, inversedBy="context")
     * @ORM\JoinColumn(name="video_resource_id", referencedColumnName="id")
     */
    protected $context;

    /**
     * @return VideoResourceInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param VideoResourceInterface $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
}