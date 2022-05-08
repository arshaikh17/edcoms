<?php

namespace EdcomsCMS\ContentBundle\Helpers;

use EdcomsCMS\ContentBundle\Model\AbstractFilterOptions;
use Symfony\Component\HttpFoundation\Request;
use EdcomsCMS\ContentBundle\Helpers\SearchHelper;

class FilterOptionsHelper
{
    private $container;
    private $doctrine;
    private $request;
    /**
     *
     * @var SearchHelper 
     */
    private $searchHelper;
    private $filterOptions = null;
    private $filterParameters = null;
    private $returnChildren = false;
    private $searchEngine = false;
    private $targetStatus = 'published';
    
    public function __construct($container, $doctrine)
    {
        $this->container = $container;
        $this->doctrine = $doctrine;
    }
    
    public function setTargetStatus($status)
    {
        $this->targetStatus = $status;
    }
    
    /**
     * Returns an array of all of the filterable fields along with all of their values available to filter with.
     * The method fetches the 'customfield' where it's name has been declared in the FilterOptions model (if it exists).
     * The values are then derived from fetching all customfielddata values associated to each customfield.
     *
     * @param   string  $uri
     * @param   integer $structureID        The ID of the parent Structure being used to search for content.
     *
     * @return  array                       The filterable fields as the keys each with an array of possible values to filter with.
     */
    public function getFilterOptions($uri, $structureID)
    {
        if ($this->filterOptions === null) {
            $this->filterOptions = $this->fetchFilterOptions($uri, $structureID, $this->filterParameters);
        }
        
        return ['filters'=>$this->filterOptions, 'options'=>[AbstractFilterOptions::FILTEROPTION_RETURNCHILDREN=>$this->returnChildren, AbstractFilterOptions::FILTEROPTION_SEARCH=>$this->searchEngine]];
    }
    
    public function getFetchedOptions()
    {
        return $this->filterOptions;
    }
    
    public function setRequest(Request $request)
    {
        $requestQuery = $request->query;
        // set fetch parameters and get the filter options.
        $filterParameters = $requestQuery->get('filter');
        
        // set up the search bar filtering.
        if ($requestQuery->has('filter-search')) {
            $filterSearch = $requestQuery->get('filter-search');
            
            if ($filterSearch !== '') {
                if ($filterParameters === null) {
                    $filterParameters = [];
                }
                
                $filterParameters['filter-search'] = $filterSearch;
            }
        }
        $this->request = $request;
        $this->filterParameters = $filterParameters;
    }
    
    public function setSearchedValue()
    {
        $requestQuery = $this->request->query;
        if ($requestQuery->has($this->searchEngine[AbstractFilterOptions::FILTEROPTION_SEARCHPARAM])) {
            // means a search is allowed and it happened \\
            $this->searchEngine['searched'] = $requestQuery->get($this->searchEngine[AbstractFilterOptions::FILTEROPTION_SEARCHPARAM]);
        }
        if (!empty($this->searchEngine[AbstractFilterOptions::FILTEROPTION_SEARCHSECTION])) {
            $sectionArr = array_map(function($item) {
                return 'section:'.$item;
            }, $this->searchEngine[AbstractFilterOptions::FILTEROPTION_SEARCHSECTION]);
            if ($this->searchEngine[AbstractFilterOptions::FILTEROPTION_SEARCHFILES] && !empty($sectionArr)) {
                // if we are searching for files, and there is a filter in place, use this to ensure it gets ignored for files
                $this->searchEngine[AbstractFilterOptions::FILTEROPTION_SEARCHFIELDS][] = '-index_type:content';
            }
            $this->searchEngine[AbstractFilterOptions::FILTEROPTION_SEARCHFIELDS] = array_merge(((!is_array($this->searchEngine[AbstractFilterOptions::FILTEROPTION_SEARCHFIELDS])) ? [] : $this->searchEngine[AbstractFilterOptions::FILTEROPTION_SEARCHFIELDS]), $sectionArr);
        }
    }
    
    private function fetchFilterOptions($uri, $structureID, $filterParameters = null)
    {
        $filterOptions = [];
        
        if ($this->container->has('FilterOptions')) {
            // get the model object containing the customfield names we need to search with.
            $filterOptions = $this->container->get('FilterOptions')->get($uri) ?: [];
            
            if ($filterOptions !== null && !empty($filterOptions)) {
                if (array_key_exists(AbstractFilterOptions::FILTEROPTION_RETURNCHILDREN, $filterOptions)) {
                    $this->returnChildren = $filterOptions[AbstractFilterOptions::FILTEROPTION_RETURNCHILDREN];
                    unset($filterOptions[AbstractFilterOptions::FILTEROPTION_RETURNCHILDREN]);
                }
                if (array_key_exists(AbstractFilterOptions::FILTEROPTION_SEARCH, $filterOptions)) {
                    $this->searchEngine = $filterOptions[AbstractFilterOptions::FILTEROPTION_SEARCH];
                    $this->setSearchedValue();
                    unset($filterOptions[AbstractFilterOptions::FILTEROPTION_SEARCH]);
                }
                // create and populate array with 'customfield' names to fetch.
                // if the iterating option has 'fetchValues' set as false,
                // then don't fetch the values, but still return an empty array.
                $filterNames = [];
                $filterValuesToExclude = [];
                
                foreach ($filterOptions as $filterName => $filterOption) {
                    $filterOptions[$filterName] = ['options' => $filterOption];
                    
                    if (isset($filterOption['fetchValues']) && !$filterOption['fetchValues']) {
                        continue;
                    }
                    
                    if (isset($filterOption[AbstractFilterOptions::FILTEROPTION_EXCLUDE])) {
                        $valuesToExclude = $filterOption[AbstractFilterOptions::FILTEROPTION_EXCLUDE];
                        
                        $filterValuesToExclude[$filterName] = $valuesToExclude;
                    }
                    
                    $filterNames[] = $filterName;
                }
                
                // get the filter values.
                $filterOptionsValues = [];
                
                if (!empty($filterNames)) {
                    $filterOptionsValues = $this->doctrine
                        ->getManager('edcoms_cms')
                        ->getRepository('EdcomsCMSContentBundle:CustomFieldData')
                        ->findFilterOptions($structureID, $filterNames, $filterValuesToExclude);
                }
                
                foreach ($filterOptionsValues as $option) {
                    $optionID = intval($option['cfid']);
                    $optionName = $option['name'];
                    $optionStructureID = intval($option['id']);
                    $optionValue = $option['value'];
                    $optionValues = null;
                    
                    // parse the value if it appears to be wrapped up as a JSON string.
                    if (strpos($optionValue, '[') === 0 && strpos($optionValue, ']') === (strlen($optionValue) - 1)) {
                        $optionValues = json_decode($optionValue);
                    }
                    
                    // if parsing the value as a JSON string hasn't worked,
                    // fallback to manual parsing.
                    if ($optionValues === null) {
                        if (strpos($optionValue, ',') !== false) {
                            $optionValues = explode(',', $optionValue);
                        } else {
                            $optionValues = [$optionValue];
                        }
                    }
                    
                    if (!isset($filterOptions[$optionName]['values'])) {
                        $filterOptions[$optionName]['values'] = [];
                    }
                    
                    foreach ($filterOptions[$optionName]['values'] as &$currentFilterValue) {
                        $value = $currentFilterValue['value'];
                        
                        if (in_array($value, $optionValues)) {
                            unset($optionValues[array_search($value, $optionValues)]);
                            
                            $currentFilterValue['matches'][] = $optionStructureID;
                        }
                    }
                    
                    foreach ($optionValues as $optionValue) {
                        $filterOptions[$optionName]['values'][] = [
                            'value' => $optionValue,
                            'matches' => [$optionStructureID]
                        ];
                    }
                }
                // build the returning array.
                // if the model specifies to sort under a filter name, sort the underlying data.
                foreach ($filterOptions as $filterName => $filterOption) {
                    if (isset($filterOption['values'])) {
                        $filterValues = array_values(array_map('unserialize', array_unique(array_map('serialize', $filterOption['values']))));
                        
                        // sort the data if model specifies to.
                        if (isset($filterOptions[$filterName]['options'][AbstractFilterOptions::FILTEROPTION_SORTED]) && $filterOptions[$filterName]['options'][AbstractFilterOptions::FILTEROPTION_SORTED]) {
                            sort($filterValues);
                        }
                        
                        $filterOptions[$filterName]['values'] = $filterValues;
                    } else {
                        $filterOptions[$filterName]['values'] = [];
                    }
                    
                    // convenience value for the view to access.
                    // always send back an array.
                    $selectedValue = [];
                    
                    if (isset($filterParameters[$filterName])) {
                        $filterParameter = $filterParameters[$filterName];
                        
                        if (is_array($filterParameter)) {
                            $selectedValue = $filterParameter;
                        } else if ($filterParameter !== '') {
                            $selectedValue[] = $filterParameter;
                        }
                    }
                    
                    $filterOptions[$filterName]['selected'] = $selectedValue;
                }
            }
        }
        
        if ($filterParameters !== null) {
            foreach ($filterParameters as $filterParameterKey => $filterParameter) {
                if (!isset($filterOptions[$filterParameterKey])) {
                    if (!is_array($filterParameter)) {
                        $filterParameter = [$filterParameter];
                    }
                    
                    if (is_int($filterParameterKey)) {
                        $filterParameterKey = '*';
                        
                        if (!isset($filterOptions[$filterParameterKey])) {
                            $filterOptions[$filterParameterKey] = [];
                        }
                        
                        $filterParameter = array_merge($filterOptions[$filterParameterKey], $filterParameter);
                    }
                    
                    $filterOptions[$filterParameterKey] = ['selected' => $filterParameter];
                    
                    // override 'fetchValues' for the search bar.
                    if ($filterParameterKey === 'filter-search') {
                        if (!isset($filterOptions[$filterParameterKey]['options'])) {
                            $filterOptions[$filterParameterKey]['options'] = [];
                        }
                        
                        $filterOptions[$filterParameterKey]['options']['fetchValues'] = false;
                    }
                }
            }
        }
        
        return $filterOptions;
    }
    
    public function getValuesForField($fieldName)
    {
        $result = null;
        
        if (isset($this->filterOptions[$fieldName])) {
            $filterValues = $this->filterOptions[$fieldName]['values'];
            $result = [];
            
            foreach ($filterValues as $filterValue) {
                $result[] = $filterValue['value'];
            }
        }
        
        return $result;
    }
    public function filterBySearch($items, $mode='array', $structureIds=null)
    {
        // only do this the first time and cache the result to save SOLR \\
        if ($this->searchEngine && !isset($this->searchEngine['results'])) {
            if (isset($this->searchEngine['searched'])) {
                $this->searchHelper = $this->container->get('SearchHelper');
                if ($this->searchHelper) {
                    $ids = $this->searchHelper->searchFields($this->searchEngine[AbstractFilterOptions::FILTEROPTION_SEARCHFIELDS], ['id', 'parent', 'structure'], $this->searchEngine['searched'], 0, 0, $structureIds);
                    $this->searchEngine['results'] = $this->resultsObjectToIds($ids);
                }
            }
        }
        switch ($mode) {
            case 'array':
                $filterMode = 'filterSearchArray';
                break;
            case 'structure':
                $filterMode = 'filterSearchStructure';
                break;
            case 'parent':
                $filterMode = 'filterSearchParent';
                break;
        }
        return array_filter(array_map([&$this, $filterMode], $items));
    }
    private function resultsObjectToIds($rows)
    {
        $ids = ['content'=>[], 'parent'=>[], 'structure'=>[]];
        if (is_array($rows->docs)) {
            foreach ($rows->docs as $row) {
                $ids['content'][] = (int)$row->id;
                if (isset($row->parent)) {
                    $ids['parent'][(int)$row->id] = (int)$row->parent;
                }
                if (isset($row->structure)) {
                    $ids['structure'][(int)$row->id] = (int)$row->structure;
                }
            }
        }
        return $ids;
    }
    private function filterSearchArray($item)
    {
        // return true if nothing was searched \\
        if (!isset($this->searchEngine['searched'])) {
            return $item;
        }
        if (isset($item['content'])) {
            return (
                    array_search($item['content']['id'], $this->searchEngine['results']['content']) !== false ||
                    array_search($item['id'], $this->searchEngine['results']['parent']) !== false
                   ) ? $item : null;
        }
        return null;
    }
    private function filterSearchStructure($item)
    {
        // return true if nothing was searched \\
        if (!isset($this->searchEngine['searched'])) {
            return $item;
        }
        if ($item->getContent()) {
            return (
                    array_search($item->getContent($this->targetStatus)->first()->getId(), $this->searchEngine['results']['content']) !== false ||
                    array_search($item->getId(), $this->searchEngine['results']['parent']) !== false
                   ) ? $item : null;
        }
        return null;
    }
    private function filterSearchParent($item)
    {
        // return true if nothing was searched \\
        if (!isset($this->searchEngine['searched'])) {
            return null;
        }
        //loop through results and collect together children ids
        $children = [];
        if (isset($this->searchEngine['results'])) {
            foreach ($this->searchEngine['results']['parent'] as $k => $resultParent) {
                if (isset($resultParent)) {
                    if (intval($resultParent) === intval($item->getId())) {
                        $children[] = $k;
                    }
                }
            }
        }

        return count($children) > 0 ? $children : null;
    }
}
