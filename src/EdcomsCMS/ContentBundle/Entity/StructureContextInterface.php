<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Entity;


interface StructureContextInterface
{

    public function getContext();

    /**
     * @return Structure
     */
    public function getStructure();

    /**
     * @param Structure $structure
     */
    public function setStructure(Structure $structure);
}