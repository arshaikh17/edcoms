<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Entity;

use EdcomsCMS\ResourcesBundle\Model\BaseResource;
use Doctrine\ORM\Mapping AS ORM;

/**
 * Class Resource
 * @package EdcomsCMS\ResourcesBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "base" = "EdcomsCMS\ResourcesBundle\Entity\Resource",
 *     "video" = "EdcomsCMS\ResourcesBundle\Entity\VideoResource"
 * })
 * @ORM\Table("edcoms_resource")
 */
class Resource extends BaseResource
{

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }



}