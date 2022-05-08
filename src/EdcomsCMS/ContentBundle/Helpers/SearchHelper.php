<?php

namespace EdcomsCMS\ContentBundle\Helpers;
use EdcomsCMS\ContentBundle\Controller\MediaController;

/**
 * Description of SearchHelper
 *
 * @author richard
 */
class SearchHelper {
    private $doctrine;
    /**
     *
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    private $token;
    private $config;
    
    /**
     *
     * @var MediaController
     */
    private $media;
    
    /**
     *
     * @var NaturalLanguageHelper
     */
    private $nlp;
    /**
     *
     * @var SearchEngineHelper
     */
    public $engine;
    public function __construct($doctrine, $container, $token)
    {
        $this->doctrine = $doctrine;
        $this->container = $container;
        $this->token = $token;
        $this->config = $this->container->getParameter('search');
        $this->media = new MediaController();
        $this->media->setContainer($this->container);
        $this->configureEngine();
    }
    public function configureEngine()
    {
        $engine = __NAMESPACE__.'\\'.ucfirst($this->config['engine']).'Helper';
        if (class_exists($engine)) {
            $this->engine = new $engine($this->config, $this->media);
        }
        if ($this->config['natural_language']) {
            $this->nlp = new NaturalLanguageHelper($this->doctrine, $this->container);
        }
    }
    public function getEngineName()
    {
        return $this->engine->getName();
    }
    public function indexItem($type, $item)
    {
        switch ($type) {
            case 'content':
                $data = $this->engine->contentIndex($item);
                break;
        }
        return $this->engine->addEntry($data);
    }
    public function search($term, $results=0, $page=0)
    {
        return $this->engine->getResults($term, $results, $page);
    }
    public function searchFields($fields, $return, $term, $results=0, $page=0, $structureIds=null)
    {
        return $this->engine->getFieldResults($fields, $return, $term, $results, $page, $structureIds);
    }
    public function suggestions($term)
    {
        // if the search engine can handle suggestions, use it, if not, use our internal one \\
        if (method_exists($this->engine, 'getSuggestions')) {
            return $this->engine->getSuggestions($term);
        }
        return $this->nlp->getSuggestions($term);
    }
    public function saveSuggestion($term)
    {
        $term = trim($term, '"');
        // if the search engine can handle suggestions, use it, if not, use our internal one \\
        if (method_exists($this->engine, 'saveSuggestion')) {
            return $this->engine->saveSuggestion($term);
        }
        return $this->nlp->saveSuggestion($term);
    }
    public function spellingSuggestions($term)
    {
        $term = trim($term, '"');
        // if the search engine can handle suggestions, use it, if not, use our internal one \\
        if (method_exists($this->engine, 'spellSuggest')) {
            return $this->engine->spellSuggest($term);
        }
        die(var_dump($this->nlp->spellSuggest($term)));
        return $this->nlp->spellSuggest($term);
    }
    public function commit()
    {
        $this->engine->commit();
    }
    public function removeIndexItem($id)
    {
        return $this->engine->deleteById($id);
    }
}
