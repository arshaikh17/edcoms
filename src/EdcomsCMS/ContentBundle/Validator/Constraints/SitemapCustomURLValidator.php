<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class SitemapCustomURLValidator
 *
 * @package EdcomsCMS\ContentBundle\Validator\Constraints
 */
class SitemapCustomURLValidator extends ConstraintValidator
{

    /** @var \Symfony\Component\Routing\Router  */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function validate($value, Constraint $constraint)
    {
        $host = $this->router->getContext()->getHost();
        $client = new Client();
        /** @var \EdcomsCMS\ContentBundle\Entity\SitemapCustomURL $value */

        // Validate Sitemap URL only when it's active.
        if($value->getActive()){
            try{
                $client->get(sprintf('%s/%s',$host,$value->getUrl()));
            }catch (\Exception $e){
                $this->context->buildViolation('Page not found')
                    ->atPath('url')
                    ->addViolation();
            }
        }
    }
}