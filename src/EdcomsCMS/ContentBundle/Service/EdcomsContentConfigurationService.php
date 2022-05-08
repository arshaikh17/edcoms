<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Service;

class EdcomsContentConfigurationService
{

    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function isContextEnabled(){
        if(!isset($this->config['structure']['context_enabled'])){
            throw new \Exception('Configuration parameter "context_enabled" for EdcomsCMSContentBundle is missing');
        }
        return $this->config['structure']['context_enabled']===true ? true : false;
    }

    public function getConfigContextClasses(){
        if(!isset($this->config['structure']['additional_context_classes'])){
            throw new \Exception('Configuration parameter "additional_context_classes" for EdcomsCMSContentBundle is missing');
        }
        return $this->config['structure']['additional_context_classes'];
    }

    public function getStructureVisibility(){
        return isset($this->config['show_visible_checkbox']) && $this->config['show_visible_checkbox'];
    }

    public function getCDNSettings(){
        return $this->config['cdn'];
    }
}
