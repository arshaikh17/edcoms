<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends Controller {

    public function indexAction(){

        $sitemapService =  $this->get('edcoms.content.service.sitemap');
        $sitemap = $sitemapService->build();

        $xmlData = $sitemapService->render($sitemap);
        $response = new Response($xmlData, 200);
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

}