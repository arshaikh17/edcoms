<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Service\Filter;

use EdcomsCMS\ResourcesBundle\Model\Filter\FilterForm;
use EdcomsCMS\ResourcesBundle\Service\EdcomsResourcesConfigurationService;

class FilterFactory
{

    /**
     * @var array
     */
    private $filterForms=[];

    /** @var ResourcesFilterConfigurationService  */
    private $filterConfigurationService;

    public function __construct(ResourcesFilterConfigurationService $filterConfigurationService)
    {
        $this->filterConfigurationService = $filterConfigurationService;
    }

    /**
     * @param $slug
     * @return FilterForm
     * @throws \Exception
     */
    public function createFilterForm($slug){
        if(array_key_exists($slug,$this->filterForms)){
            throw new \Exception(sprintf('A filter form with slug %s already exists. Use a different slug.', $slug));
        }

        $filterForm = new FilterForm($slug);
        $filterForm->setApiURL($this->filterConfigurationService->getAPIURL());
        $this->filterForms[$slug] = $filterForm;

        return $filterForm;
    }

    /**
     * @param $slug
     * @return FilterForm|null
     */
    public function get($slug){
        return isset($this->filterForms[$slug]) ? $this->filterForms[$slug] : null;
    }
}