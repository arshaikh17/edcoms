<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Model\Filter;


interface FilterableEntityInterface
{

    /** @return string */
    public function getFilterLabel();

    /** @return string */
    public function getFilterValue();
}