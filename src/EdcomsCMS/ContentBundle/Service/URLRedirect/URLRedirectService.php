<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Service\URLRedirect;

use Doctrine\ORM\EntityManager;
use EdcomsCMS\ContentBundle\Entity\URLRedirect;
use EdcomsCMS\ContentBundle\Entity\URLRedirectUsage;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class URLRedirectService
 * @package EdcomsCMS\ContentBundle\Service\URLRedirect
 */
class URLRedirectService
{

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param $relativePath
     * @return false|URLRedirect
     */
    public function redirectExist($relativePath){
        $redirect = $this->em->getRepository(URLRedirect::class)->findOneBy([
            'url' => $relativePath,
            'active' => true
        ]);

        return $redirect ?: false;

    }

    /**
     * @param URLRedirect $redirect
     * @param Request $request
     */
    public function trackURLRedirect(URLRedirect $redirect, Request $request){
        try{
            $urlRedirectUsage = new URLRedirectUsage();
            $urlRedirectUsage
                ->setUrlRedirect($redirect)
                ->setIpAddress($request->getClientIp())
                ->setReferrer($request->headers->get('referer'))
                ->setUserAgent($request->headers->get('User-Agent'))
            ;

            $this->em->persist($urlRedirectUsage);
            $this->em->flush();
        }catch (\Exception $e){
            // TODO log that
        }
    }
}