<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use EdcomsCMS\ContentBundle\Entity\Media;
use EdcomsCMS\ResourcesBundle\Model\VideoResourceInterface;

/**
 * Class VideoResource
 * @package EdcomsCMS\ResourcesBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table("edcoms_resource_video")
 */
class VideoResource extends Resource implements VideoResourceInterface
{

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\Media")
     */
    protected $video;

    /**
     * @return Media
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * @param Media $video
     */
    public function setVideo($video)
    {
        $this->video = $video;
    }

}