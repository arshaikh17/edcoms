<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Model\Filter;

class FilterResult
{

    /**
     * @var integer
     */
    private $totalPages;

    /**
     * @var integer
     */
    private $totalItems;

    /**
     * @var integer
     */
    private $currentPage;

    /**
     * @var array
     */
    private $items;

    public function __construct($items, $totalPages, $totalItems, $currentPage)
    {
        $this->totalPages = $totalPages;
        $this->totalItems = $totalItems;
        $this->items = $items;
        $this->currentPage = $currentPage;
    }

    /**
     * @return int
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }



}