<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Service\Filter;

use Doctrine\ORM\EntityManager;
use EdcomsCMS\ResourcesBundle\Model\Filter\FilterSearch;
use EdcomsCMS\ResourcesBundle\Service\EdcomsResourcesConfigurationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

class ResourcesFilterConfigurationService
{

    const FILTER_PARAM_AGE =        'agegroup';
    const FILTER_PARAM_TOPIC =      'topic';
    const FILTER_PARAM_TYPE =       'type';
    const FILTER_PARAM_SUBJECT =    'subject';
    const FILTER_PARAM_BATCH =      'batch';
    const FILTER_PARAM_PAGE =       'page';
    const FILTER_PARAM_SEARCH =     'search';

    private $agegroup;
    private $topic;
    private $type;
    private $subject;
    private $batch;
    private $page;
    private $search;

    private $loadMoreParameters = [];

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EdcomsResourcesConfigurationService
     */
    private $edcomsResourcesConfiguration;

    /**
     * @var Router
     */
    private $router;

    /** @var  array */
    private $params;

    /**
     * ResourcesFilterConfigurationService constructor.
     * @param EntityManager $em
     * @param EdcomsResourcesConfigurationService $edcomsResourcesConfiguration
     * @param Router $router
     */
    public function __construct(
        EntityManager $em,
        EdcomsResourcesConfigurationService $edcomsResourcesConfiguration,
        Router $router
    )
    {
        $this->em = $em;
        $this->edcomsResourcesConfiguration = $edcomsResourcesConfiguration;
        $this->router = $router;
        $this->batch=$edcomsResourcesConfiguration->getAPIBatchValue();
        $this->page=1;
        $this->params = [];
    }

    /**
     * @param $params array|Request
     */
    public function init($params){
        if(is_object($params) && get_class($params)==Request::class){
            $params = $params->query->all();
        }
        $this->params = $params;
        $filterParameters = [
            self::FILTER_PARAM_AGE => $this->edcomsResourcesConfiguration->getEntityClass(EdcomsResourcesConfigurationService::AGE_GROUP),
            self::FILTER_PARAM_TOPIC => $this->edcomsResourcesConfiguration->getEntityClass(EdcomsResourcesConfigurationService::RESOURCE_TOPIC),
            self::FILTER_PARAM_TYPE => $this->edcomsResourcesConfiguration->getEntityClass(EdcomsResourcesConfigurationService::RESOURCE_TYPE),
            self::FILTER_PARAM_SUBJECT => $this->edcomsResourcesConfiguration->getEntityClass(EdcomsResourcesConfigurationService::RESOURCE_SUBJECT),
            self::FILTER_PARAM_PAGE => null,
            self::FILTER_PARAM_BATCH => null,
            self::FILTER_PARAM_SEARCH => null
        ];

        foreach ($filterParameters as $key => $entityClass){
            if(isset($params[$key]) && $params[$key]){
                if($entityClass){
                    $value = explode(',',$params[$key]);
                }else{
                    $value = $params[$key];
                }
                $this->{$key} = $value;
            }
        }
    }

    public function getFilterConfiguration(){
        $qb = $this->em->createQueryBuilder();

        $resourceClass = $this->edcomsResourcesConfiguration->getBaseResource();

        // TODO DC: Look at optimising the DQL.
        $qb
            ->select('resource', 'types')
            ->from($resourceClass, 'resource')
            ->leftJoin('resource.ageGroups', 'age_groups')
            ->leftJoin('resource.type', 'types')
            ->leftJoin('resource.topics', 'topics')
            ->leftJoin('resource.subjects', 'subjects')
        ;

        if(is_array($this->agegroup) && count($this->agegroup)>0){
            $qb
                ->andWhere('age_groups.slug IN (:age_groups)')
                ->setParameter('age_groups', $this->agegroup)
                ;
        }

        if(is_array($this->type) && count($this->type)>0){
            $qb
                ->andWhere('types.slug IN (:resource_types)')
                ->setParameter('resource_types',$this->type)
            ;
        }

        if($this->topic){
            $qb
                ->andWhere('topics.slug IN (:topics)')
                ->setParameter('topics',$this->topic)
            ;
        }

        if($this->subject){
            $qb
                ->andWhere('subjects.slug IN (:subjects)')
                ->setParameter('subjects', $this->subject)
            ;
        }

        if($this->search){
            $qb->andWhere($qb->expr()->like($qb->expr()->lower('resource.title'), ':search'))
                ->setParameter('search',"%".strtolower($this->search)."%")
                ;
        }
        $qb->orderBy('resource.createdOn', 'DESC');
        return new FilterSearch((int) $this->page, (int) $this->batch, $qb);
    }

    public function getActiveFilter($slug){
        if(in_array($slug,$this->getActiveFilters())){
            return $this->{$slug};
        }elseif(array_key_exists($slug,$this->params)){
            return explode(',', $this->params[$slug]);
        }else{
            return [];
        }
    }

    public function getActiveFilters(){
        return [
            self::FILTER_PARAM_AGE,
            self::FILTER_PARAM_TOPIC,
            self::FILTER_PARAM_TYPE,
            self::FILTER_PARAM_SUBJECT,
        ];
    }

    public function getLoadMorePlaceholderURL(){
        $route = $this->edcomsResourcesConfiguration->getAPIResourceRoute();
        $routeParams = [];
        foreach ($this->getActiveFilters() as $filter){
            if($this->{$filter}){
                $routeParams[$filter] = implode(',', $this->{$filter});
            }
        }
        $routeParams['page']='__PAGE__';
        $routeParams = array_merge($routeParams, $this->loadMoreParameters);
        return $this->router->generate($route,$routeParams);
    }

    public function addLoadMoreParameter($paramName, $paramValue){
        $this->loadMoreParameters[$paramName] = $paramValue;
    }

    public function getAPIURL(){
        $route = $this->edcomsResourcesConfiguration->getAPIResourceRoute();
        return $this->router->generate($route);
    }

}