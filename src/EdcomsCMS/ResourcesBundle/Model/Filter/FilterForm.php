<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Model\Filter;

use EdcomsCMS\ResourcesBundle\Service\Filter\ResourcesFilterConfigurationService;

class FilterForm
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var array|FilterElement
     */
    private $elements = [];

    /**
     * @var string
     */
    private $apiURL;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getElements(): array
    {
        return $this->elements;
    }


    /**
     * @param FilterElement $filterElement
     * @throws \Exception
     */
    public function addFilterElement(FilterElement $filterElement){
        if(array_key_exists($filterElement->getSlug(),$this->elements)){
            throw new \Exception(sprintf('A filter with slug %s already exists in the filter form %s',$filterElement->getSlug(),$this->getName()));
        }
        $this->elements[$filterElement->getSlug()] = $filterElement;
    }

    public function applyCurrentFilters(ResourcesFilterConfigurationService $filterConfiguration){
        foreach ($this->elements as $element){
            $selectedValues = $filterConfiguration->getActiveFilter($element->getSlug());
            if($selectedValues){
                $element->setSelectedValues($selectedValues);
            }
        }
    }

    /**
     * @return string
     */
    public function getApiURL()
    {
        return $this->apiURL;
    }

    /**
     * @param string $apiURL
     */
    public function setApiURL(string $apiURL)
    {
        $this->apiURL = $apiURL;
    }

}