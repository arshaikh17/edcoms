<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Admin;

use EdcomsCMS\ContentBundle\Service\SitemapService;
use GuzzleHttp\Client;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class SitemapAdmin
 *
 * @package EdcomsCMS\ContentBundle\Admin
 */
class SitemapAdmin extends AbstractAdmin{


    protected $baseRoutePattern = 'sitemap';

    /** @var \EdcomsCMS\ContentBundle\Service\SitemapService  */
    private $sitemapService;

    /**
     * SitemapAdmin constructor.
     *
     * @param $code
     * @param $class
     * @param $baseControllerName
     * @param \EdcomsCMS\ContentBundle\Service\SitemapService $sitemapService
     */
    public function __construct($code, $class, $baseControllerName, SitemapService $sitemapService)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->sitemapService = $sitemapService;
    }

    public function configureActionButtons($action, $object = null)
    {
        $list = parent::configureActionButtons($action,$object);

        $list['build_dictionary'] = array(
            'template' =>  'EdcomsCMSContentBundle:Admin/Sitemap:menu_button_view_sitemap.html.twig',
        );

        return $list;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->setLabel("Sitemap custom URL");
        $formMapper
            ->add('url')
            ->add('priority', ChoiceType::class,[
                'required' => false,
                'empty_data' => '0.3',
                'placeholder' => 'Choose an option (Default 0.3)',
                'choices' => [
                    '0.1' => 0.1,
                    '0.2' => 0.2,
                    '0.3' => 0.3,
                    '0.4' => 0.4,
                    '0.5' => 0.5,
                    '0.6' => 0.6,
                    '0.7' => 0.7,
                    '0.8' => 0.8,
                    '0.9' => 0.9,
                    '1' => 1,
                ],
            ])
            ->add('active')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('active')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('url', null, [
                'label' => 'URL Path'
            ])
            ->add('priority')
            ->add('active', null, array('editable' => true))
            ->add("_action", "actions", array(
                "actions" => array(
                    'test_page' => [
                        'template' => 'EdcomsCMSContentBundle:Admin/Sitemap:button_view_page.html.twig'
                    ],
                )
            ))
        ;
    }

    public function getContentEntriesCount(){
        return count($this->sitemapService->getContent());
    }

    public function getCustomEntriesCount(){
        return count($this->sitemapService->getCustomURLs());
    }

}