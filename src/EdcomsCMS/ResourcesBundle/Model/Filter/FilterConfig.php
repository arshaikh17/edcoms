<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Model\Filter;


class FilterConfig
{

    /**
     * @var bool
     *
     * Display option "All"
     */
    private $displayAll=true;

    public function __construct()
    {

    }

    /**
     * @return bool
     */
    public function isDisplayAll(): bool
    {
        return $this->displayAll;
    }

    /**
     * @param bool $displayAll
     */
    public function setDisplayAll(bool $displayAll)
    {
        $this->displayAll = $displayAll;
    }



}