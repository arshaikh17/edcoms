<?php

namespace EdcomsCMS\ContentBundle\Twig;

use EdcomsCMS\ContentBundle\Entity\Media;
use EdcomsCMS\ContentBundle\Entity\Structure;
use EdcomsCMS\ContentBundle\Helpers\GetContent;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Router;

class EdcomsExtension extends \Twig_Extension {

    protected $em;

    /** @var  Container */
    protected $container;
    private $getContentHelper;

    /**
     * @var Packages
     */
    private $packages;

    public function __construct($em, $container)
    {
        $this->em = $em;
        $this->container = $container;
        $this->getContentHelper = new GetContent($this->em, $this->container);
        $this->packages = $container->get('assets.packages');
    }

    /**
     * Returns a list of custom filters
     *
     * @return array An array of filters
     */
    function getFilters() {
        return [
            new \Twig_SimpleFilter('sortStructureByDate', [$this, 'sortStructureByDateFilter']),
            new \Twig_SimpleFilter('json_decode', [$this, 'jsonDecode']),
            new \Twig_SimpleFilter('filePickerPlaceholder', [$this, 'filePickerPlaceholder']),
            new \Twig_SimpleFilter('basename', [$this, 'basename'])
        ];
    }

    public function filePickerPlaceholder($path){
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $placeholder = $this->packages->getUrl('bundles/edcomscmscontent/image/media/placeholder-none.jpg', null);
        if($path){
            if(in_array($extension,['png', 'jpg', 'jpeg', 'gif', 'bmp'])){
                $placeholder = $path;
            }else{
                $placeholder = $this->packages->getUrl('bundles/edcomscmscontent/image/media/placeholder.jpg', null);
            }
        }
        return $placeholder;
    }

    public function basename($path){
        return basename($path);
    }

    /**
     * Sort an array of structures by added on date
     *
     * @param $structureArray - array
     * @param bool|true $asc - true for asc, false for desc
     * @return array|\Twig_Error
     */
    public function sortStructureByDateFilter($structureArray, $asc = true) {

        if (!is_array($structureArray)) {
            return new \Twig_Error('parameter 1 must be an array');
        }

        if ($asc) {//ascending
            usort($structureArray, [$this, 'sortStructureByDateAsc']);
        } else {//descending
            usort($structureArray, [$this, 'sortStructureByDateDesc']);
        }

        return $structureArray;
    }

    /**
     * Sort children array ascending
     *
     * @param $structureA
     * @param $structureB
     * @return int
     */
    private function sortStructureByDateAsc($structureA, $structureB) {
        //convert params from array to object if required
        if (!is_object($structureA)) {
            $structureA = (object) $structureA;
        }

        if (!is_object($structureB)) {
            $structureB = (object) $structureB;
        }

        $timeA = $structureA->content['addedOn']->format('Y-m-d H:i:s');
        $timeB = $structureB->content['addedOn']->format('Y-m-d H:i:s');

        if ($timeA === $timeB) {
            return 0;
        }

        return $timeA > $timeB? 1: -1;
    }

    /**
     * Sort children array descending
     *
     * @param $structureA
     * @param $structureB
     * @return int
     */
    private function sortStructureByDateDesc($structureA, $structureB) {
        //convert params from array to object if required
        if (!is_object($structureA)) {
            $structureA = (object) $structureA;
        }
        if (!is_object($structureB)) {
            $structureB = (object) $structureB;
        }

        $timeA = $structureA->content['addedOn']->format('Y-m-d H:i:s');
        $timeB = $structureB->content['addedOn']->format('Y-m-d H:i:s');

        if ($timeA === $timeB) {
            return 0;
        }

        return $timeA < $timeB? 1: -1;
    }

    /**
     * Returns a list of custom functions
     *
     * @return array An array of functions
     */
    function getFunctions() {
        return [
            new \Twig_SimpleFunction('setChildren', [$this,'setChildren']),
            new \Twig_SimpleFunction('getContentByStructureId', [$this,'getContentByStructureId']),
            new \Twig_SimpleFunction('getFilterValues', [$this, 'getFilterValues']),
            new \Twig_SimpleFunction('sortArray', [$this, 'sortArray']),
            new \Twig_SimpleFunction('edcoms_media_url', [$this, 'getMediaUrl']),
            new \Twig_SimpleFunction('getContentFromContext', [$this, 'getContentFromContext']),
            new \Twig_SimpleFunction('getStructureMeta', [$this, 'getStructureMeta']),
            new \Twig_SimpleFunction('getContentByStructure', [$this,'getContentByStructure']),
        ];
    }

    public function sortArray(array $array, $sortingProperty=false, $ordering='ASC'){
        usort($array, function($a, $b) use($sortingProperty, $ordering){
            $valueA = $sortingProperty ? $a[$sortingProperty] : $a;
            $valueB = $sortingProperty ? $b[$sortingProperty] : $b;

            if($valueA==$valueB){
                return 0;
            }
            return $valueA>$valueB ? 1 : -1;
        });

        return $array;
    }

    /**
     * @param $structure
     * @return \EdcomsCMS\ContentBundle\Entity\PageMetadata|null
     */
    public function getStructureMeta($structure){
        if(is_numeric($structure) ){
            $structure = $this->container->get('doctrine.orm.default_entity_manager')->getRepository(Structure::class)->find($structure);
            if(!$structure){
                return null;
            }
            return $structure->getPageMetadata();
        }elseif (is_object($structure) && ($structure instanceof Structure)){
            return $structure->getPageMetadata();
        }else{
            return null;
        }
    }

    /**
     * @param $media
     * @return int|string
     */
    public function getMediaUrl($media, $absoluteURL=false){
        return $this->container->get('edcoms.content.service.media.url_generator')->generateMediaUrl($media, $absoluteURL ? Router::ABSOLUTE_URL : Router::ABSOLUTE_PATH);
    }

    public function getContentFromContext($context)
    {
        return $this->container->get('edcoms.content.service.content_service')->getContentFromContext($context);
    }

    /**
     * Get an item of content by structure id ready to be used in template
     *
     * @param $ids
     * @return array
     */
    public function getContentByStructureId($ids) {
        return $this->getContentHelper->getContentByStructureId($ids);
    }

    /**
     * @param int|Structure $structure
     * @return array|null
     */
    public function getContentByStructure($structure) {
      return $this->getContentHelper->getContentByStructure($structure);
    }

    /**
     * Get an array of paginated content objects
     *
     * @param $id - parent structure id
     * @param $status - status of the paginated content to get
     * @param $limit - how many itmes of content to get
     * @param $page - which page of content to get
     * @return array - array of content objects
     */
    public function setChildren($id, $status, $limit, $page) {
        return $this->getContentHelper->GetContentByParent(intval($id), $status, intval($limit), intval($page));
    }

    public function getFilterValues($fieldName)
    {
        $result = null;
        
        if ($this->container->has('EdcomsCMSFilterOptions')) {
            $result = $this
                ->container
                ->get('EdcomsCMSFilterOptions')
                ->getValuesForField($fieldName);
        }
        
        return $result;
    }

    public function getName()
    {
        return 'edcoms';
    }

    public function jsonDecode($string) {
        return json_decode($string,true);
    }
}
