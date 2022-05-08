<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Service;


use Doctrine\ORM\EntityManager;
use EdcomsCMS\ContentBundle\Entity\SitemapCustomURL;
use EdcomsCMS\ContentBundle\Entity\Structure;
use EdcomsCMS\ContentBundle\Model\Sitemap\ImageNode;
use EdcomsCMS\ContentBundle\Model\Sitemap\Sitemap;
use EdcomsCMS\ContentBundle\Model\Sitemap\URLNode;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\Routing\Router;

/**
 * Class SitemapService
 *
 * @package EdcomsCMS\ContentBundle\Service
 */
class SitemapService {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \JMS\Serializer\Serializer
     */
    private $serializer;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    private $router;

    /**
     * @var \EdcomsCMS\ContentBundle\Service\MediaUrlGenerator
     */
    private $mediaURLGenerator;

    /**
     * SitemapService constructor.
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @param \Symfony\Component\Routing\Router $router
     * @param \EdcomsCMS\ContentBundle\Service\MediaUrlGenerator $mediaURLGenerator
     */
    public function __construct(EntityManager $em, Router $router, MediaUrlGenerator $mediaURLGenerator) {
        $this->serializer = SerializerBuilder::create()->build();
        $this->entityManager = $em;
        $this->router = $router;
        $this->mediaURLGenerator = $mediaURLGenerator;
    }

    /**
     * @return Sitemap
     */
    public function build(){
        $sitemap = new Sitemap();

        // Add homepage
        $url = new URLNode();
        $url->setLoc($this->router->generate('cms_home',[],Router::ABSOLUTE_URL));
        $url->setPriority(1);
        $sitemap->addURLNode($url);


        // Add Content
        $structures = $this->getContent();

        foreach ($structures as $structure){
            /** @var \EdcomsCMS\ContentBundle\Entity\Structure $structure */
            $url = new URLNode();
            $link = $structure->getFullLink(true);
            if(!$link){
                continue;
            }
            $pageMetadata = $structure->getPageMetadata();
            $url->setLoc($this->router->generate('cms',['path'=> $link],Router::ABSOLUTE_URL));
            if($structure->getPublishedContent()->getUpdatedOn()){
                $url->setLastModified($structure->getPublishedContent()->getUpdatedOn());
            }
            if($pageMetadata->getSeoPriority()){
                $url->setPriority($pageMetadata->getSeoPriority());
            }
            if($img = $structure->getPageMetadata()->getImage()){
                $url->addImage(new ImageNode($this->mediaURLGenerator->generateMediaUrl($img,Router::ABSOLUTE_URL)));
            }
            $sitemap->addURLNode($url);
        }

        // Add Custom URLs
        $customURLs = $this->getCustomURLs();

        foreach ($customURLs as $customURL){
            /** @var SitemapCustomURL $customURL */
            $url = new URLNode();
            $link = $customURL->getUrl();
            if(!$link){
                continue;
            }
            $url->setLoc($this->router->generate('cms',['path'=> $link],Router::ABSOLUTE_URL));
            if($customURL->getPriority()){
                $url->setPriority($customURL->getPriority());
            }
            $sitemap->addURLNode($url);
        }

        return $sitemap;
    }

    public function render(Sitemap $sitemap){
        return $this->serializer->serialize($sitemap->getURLSet(),'xml');
    }

    /**
     * Returns all the Structure entities that should be listed in sitemap.xml
     * @return array | \EdcomsCMS\ContentBundle\Entity\Structure
     */
    public function getContent(){
        $qb = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('structure', 'content', 'pageMetadata','context')
            ->from(Structure::class, 'structure')
            ->leftJoin('structure.content', 'content')
            ->leftJoin('content.contentType', 'contentType')
            ->leftJoin('structure.context', 'context')
            ->leftJoin('structure.pageMetadata', 'pageMetadata')
            ->where('content.status=:status')
            ->andWhere('contentType.isPage=true')
            ->andWhere('pageMetadata.hideFromSearchEngines=false')
            ->setParameter('status', 'published')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array|SitemapCustomURL
     */
    public function getCustomURLs(){
        $qb = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sitemapCustomURL')
            ->from(SitemapCustomURL::class, 'sitemapCustomURL')
            ->where('sitemapCustomURL.active=true')
            ;

        return $qb->getQuery()->getResult();
    }
}