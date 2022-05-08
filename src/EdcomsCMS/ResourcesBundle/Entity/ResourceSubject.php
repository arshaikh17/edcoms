<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Entity;

use EdcomsCMS\ResourcesBundle\Model\BaseResourceSubject;
use Doctrine\ORM\Mapping AS ORM;

/**
 * Class ResourceSubject
 * @package EdcomsCMS\ResourcesBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table("edcoms_resource_subject")
 */
class ResourceSubject extends BaseResourceSubject
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