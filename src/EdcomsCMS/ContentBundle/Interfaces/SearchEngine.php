<?php

namespace EdcomsCMS\ContentBundle\Interfaces;

/**
 * Description of SearchEngine
 *
 * @author richard
 */
interface SearchEngine {
    public function getName();
    public function getResults($q);
    public function addEntry($data);
}
