<?php
namespace EdcomsCMS\ContentBundle\Twig;

use Twig_Token;

class SetChildrenTokenParser extends \Twig_TokenParser
{
    protected $em;
    protected $container;
    public function __construct($em, $container) {
        $this->em = $em;
        $this->container = $container;
    }
    public function parse(Twig_Token $token) {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $name = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
        $stream->expect(Twig_Token::OPERATOR_TYPE, '=');
        $id = $parser->getExpressionParser()->parseExpression();
        $status = $parser->getExpressionParser()->parseExpression();
        $limit = $parser->getExpressionParser()->parseExpression();
        $page = $parser->getExpressionParser()->parseExpression();
        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        
        return new SetChildrenNode($this->em, $this->container, $name, $id, $status, $limit, $page, $token->getLine(), $this->getTag());
    }
    
    public function getTag()
    {
        return 'setchildren';
    }
}