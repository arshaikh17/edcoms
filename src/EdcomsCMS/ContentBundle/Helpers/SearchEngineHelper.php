<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace EdcomsCMS\ContentBundle\Helpers;

use EdcomsCMS\ContentBundle\Interfaces\SearchEngine;

/**
 * Description of SearchEngineHelper
 *
 * @author richard
 */
class SearchEngineHelper implements SearchEngine {
    
    use \EdcomsCMS\ContentBundle\Traits\SearchHydration;
    
    
    public function getName()
    {
        return 'Generic Search Engine';
    }
    public function getResults($q)
    {
        return print_r($q, true);
    }
    public function addEntry($data)
    {
        return print_r($q, true);
    }
}
