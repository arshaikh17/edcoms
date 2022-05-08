<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Twig\Extension;

use EdcomsCMS\ResourcesBundle\Model\Filter\FilterResult;
use Symfony\Component\DependencyInjection\Container;
use EdcomsCMS\ResourcesBundle\Provider\FilterBuilderServiceProvider;
use EdcomsCMS\ResourcesBundle\Service\Filter\FilterFormRenderer;
use EdcomsCMS\ResourcesBundle\Service\Filter\ResourcesFilterConfigurationService;

class ResourcesFilterExtension extends \Twig_Extension
{

    /** @var Container  */
    private $container;

    /** @var FilterBuilderServiceProvider  */
    private $filterBuilderProvider;

    /** @var FilterFormRenderer */
    private $filterFormRenderer;

    /** @var ResourcesFilterConfigurationService  */
    private $filterConfigService;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->filterBuilderProvider = $container->get('edcoms.resources.filter_provider.builder_service');
        $this->filterFormRenderer = $container->get('edcoms.resources.filter_renderer');
        $this->filterConfigService = $this->container->get('edcoms.resources.service.filter_configuration');
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('filter_form_config', array($this, 'filterFormConfig')),
            new \Twig_SimpleFunction('filter_result_loadmore', array($this, 'filterResultLoadMore'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('filter_result_pagination', array($this, 'filterResultPagination'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('resource_api_endpoint', array($this, 'resourceAPIEndpoint')),
            new \Twig_SimpleFunction('load_more_api_endpoint', array($this, 'loadMoreAPIEndpoint')),
            new \Twig_SimpleFunction('active_filter_value', array($this, 'getActiveFilter')),
        );
    }

    public function filterFormConfig($alias){
        $filterForm = $this->filterBuilderProvider->get($alias);
        $filterForm->applyCurrentFilters($this->filterConfigService);
        return $this->filterFormRenderer->render($filterForm);
    }

    /**
     * @param FilterResult $filterResult|null
     * @param $label
     * @return string
     */
    public function filterResultLoadMore($filterResult, $label=''){
        return $this->container->get('twig')->render('@EdcomsCMSResources/Block/filter_result_load_more.html.twig', [
            'result' => $filterResult,
            'label' => $label
        ]);
    }

    public function filterResultPagination(FilterResult $filterResult){
        return $this->container->get('twig')->render('@EdcomsCMSResources/Block/filter_result_pagination.html.twig', [
            'result' => $filterResult
        ]);
    }

    public function resourceAPIEndpoint(){
        return $this->filterConfigService->getAPIURL();
    }

    public function loadMoreAPIEndpoint(){
        return $this->filterConfigService->getLoadMorePlaceholderURL();
    }

    public function getActiveFilter($filterName){
        return $this->filterConfigService->getActiveFilter($filterName);
    }

    public function getName()
    {
        return 'edcoms.resources.filter_twig_extension';
    }
}