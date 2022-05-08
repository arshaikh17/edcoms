<?php

namespace EdcomsCMS\ContentBundle\Model;

abstract class AbstractFilterOptions
{
    const FILTEROPTION_EXCLUDE = 'exclude';
    const FILTEROPTION_MATCHANY = 'matchAny';
    const FILTEROPTION_SORTED = 'sorted';
    const FILTEROPTION_SUBCHILDREN = 'sub_children';
    const FILTEROPTION_RETURNCHILDREN = 'return_children';
    // the SEARCH filter option enables us to detect a search engine and use the configuration from this \\
    const FILTEROPTION_SEARCH = 'engine_search';
    const FILTEROPTION_SEARCHFIELDS = 'engine_fields';
    const FILTEROPTION_SEARCHPARAM = 'engine_search_parameter';
    const FILTEROPTION_SEARCHSECTION = 'engine_search_section';
    const FILTEROPTION_SEARCHFILES = 'engine_search_files';
    
    protected $filterOptions = [];
    protected $globalFilterOptions = [];
    
    public function __construct()
    {
        $this->setupOptions();
    }
    
    // @deprecated Deprecated in favour of 'setupOptions()'.
    // abstract protected function setUp();
    
    /**
     * Populates '$filterOptions' and '$globalFilterOptions' with the necessary values required by the main application.
     */
    abstract protected function setupOptions();
    
    /**
     * Returns the necessary filter options.
     * All of the values stored in '$globalFilterOptions' are returned.
     * If specific filter options have been set for the path of '$uri' in '$filterOptions', they are also returned in the same array.
     * 
     * @param   string  $uri    The path of the parent Structure
     * 
     * @return  array           A collection of the filter options appropriate to the requested '$uri'.
     */
    public function get($uri = null)
    {
        $filterOptions = $this->globalFilterOptions;
        
        if (isset($this->filterOptions[$uri])) {
            $filterOptions = array_merge($filterOptions, $this->filterOptions[$uri]);
        }
        
        return $filterOptions;
    }
}
