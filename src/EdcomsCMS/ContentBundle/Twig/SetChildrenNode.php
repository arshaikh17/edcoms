<?php

namespace EdcomsCMS\ContentBundle\Twig;

use Twig_Compiler;
use Twig_Node_Expression;


use EdcomsCMS\ContentBundle\Helpers\GetContent;

class SetChildrenNode extends \Twig_Node {
    protected $em;
    
    protected $container;
    public function __construct($em, $container, $name, Twig_Node_Expression $id, Twig_Node_Expression $status, Twig_Node_Expression $limit, Twig_Node_Expression $page, $line, $tag = null)
    {
        parent::__construct(array('id' => $id, 'status'=>$status, 'limit'=>$limit, 'page'=>$page), array('name' => $name), $line, $tag);
        $this->em = $em;
        $this->container = $container;
    }

    public function compile(Twig_Compiler $compiler)
    {
        $content = new GetContent($this->em, $this->container);
        $compiler
            ->addDebugInfo($this)
            ->write('$context[\''.$this->getAttribute('name').'\'] = ')
            ->write('json_decode(\'')
            ->write(json_encode($content->GetContentByParent($this->getNode('id'), $this->getNode('status'), $this->getNode('limit'), $this->getNode('page'))))
            ->write('\')')
            ->raw(";\n")
        ;
    }
}
