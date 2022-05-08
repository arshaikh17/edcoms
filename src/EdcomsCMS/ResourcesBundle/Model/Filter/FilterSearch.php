<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Model\Filter;

use Doctrine\ORM\QueryBuilder;

class FilterSearch
{

    /**
     * @var integer
     */
    private $page;

    /**
     * @var integer
     */
    private $batch;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;


    public function __construct($page, $batch, QueryBuilder $queryBuilder)
    {
        $this->page = $page;
        $this->batch = $batch;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page)
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getBatch()
    {
        return $this->batch;
    }

    /**
     * @param int $batch
     */
    public function setBatch(int $batch)
    {
        $this->batch = $batch;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

}