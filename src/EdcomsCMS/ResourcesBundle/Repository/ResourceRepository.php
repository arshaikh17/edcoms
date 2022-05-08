<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Repository;

use Doctrine\ORM\EntityRepository;
use EdcomsCMS\ResourcesBundle\Model\Filter\FilterResult;
use EdcomsCMS\ResourcesBundle\Model\Filter\FilterSearch;
use Knp\Component\Pager\Paginator;

class ResourceRepository extends EntityRepository
{

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @param Paginator $paginator
     */
    public function setPaginator(Paginator $paginator){
        $this->paginator = $paginator;
    }

    public function filterResources(FilterSearch $filterConfiguration){
        $result =  $this->paginator->paginate(
            $filterConfiguration->getQueryBuilder()->getQuery(),
            $filterConfiguration->getPage(),
            $filterConfiguration->getBatch()
        );

        return new FilterResult(
            $result->getItems(),
            (int) ceil($result->getTotalItemCount()/$filterConfiguration->getBatch()),
            $result->getTotalItemCount(),
            $filterConfiguration->getPage()
        );
    }
}