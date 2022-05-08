<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractStructureContext
 * @package AppBundle\Entity\Content
 *
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 */
abstract class StructureContext implements StructureContextInterface
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;


    /**
     * @var Structure
     *
     * @ORM\OneToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\Structure", inversedBy="context")
     * @ORM\JoinColumn(name="structure_id", referencedColumnName="id")
     */
    protected $structure;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Structure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @param Structure $structure
     */
    public function setStructure(Structure $structure)
    {
        $this->structure = $structure;
    }

//    public function getContext()
//    {
//       return null;
//    }

}