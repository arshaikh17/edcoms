<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use EdcomsCMS\ResourcesBundle\Model\BaseAgeGroup;

/**
 * Class AgeGroup
 * @package EdcomsCMS\ResourcesBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table("edcoms_age_group")
 */
class AgeGroup extends BaseAgeGroup
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