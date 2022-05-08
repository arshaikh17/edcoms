<?php

namespace EdcomsCMS\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EdcomsCMSAdminExtension extends Extension implements PrependExtensionInterface
{

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->attachAssets($config, $container);
        $container->setParameter('edcoms.admin.video.legacy_player_snippet', $config['legacy_video_player_snippet'] );
    }

    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $this->prependSonataAdminConfig($bundles, $container);
        $this->prependSonataBlockConfig($bundles, $container);
        $this->prependEdcomsCMSContentConfig($bundles, $container);
        $this->prependEdcomsCMSBatchConfig($bundles, $container);
        $this->prependTinymceBundleConfig($bundles, $container);
    }

    /**
     * @param $bundles
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function prependTinymceBundleConfig($bundles, ContainerBuilder $container){
        if (!isset($bundles['StfalconTinymceBundle'])) {
            throw new \Exception("StfalconTinymceBundle bundle is not enabled");
        }

        $container->prependExtensionConfig('stfalcon_tinymce', array(
            'external_plugins'=> [
                'filemanager' => [
                    'url' => '/bundles/edcomscmstemplates/src/assets/js/tinymce/plugins/responsivefilemanager/plugin.min.js'
                ]
            ],
            'theme' => array(
                'simple' => array(
                    'menubar' => false,
                    'plugins' => 'media'
                ),
                'advanced' => [
                    'browser_spellcheck'=> true,
                    'plugins' => [
                        'advlist autolink link image lists charmap print preview hr anchor pagebreak',
                        'searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking',
                        'table contextmenu directionality emoticons paste textcolor code filemanager'
                    ],
                    'toolbar1' => 'newdocument bold italic underline strikethrough alignleft aligncenter alignright alignjustify  formatselect | cut copy paste pastetext bullist numlist outdent indent blockquote undo redo removeformat',
                    'toolbar2' => 'link code | image media',
                    'extended_valid_elements' => 'script[type|src]',
                    'image_advtab' => true,
                    'relative_urls' => false,
                    'paste_as_text' => true,
                    'external_filemanager_path' => '/cms/filemanager/',
                    'filemanager_title' => 'Responsive Filemanager',
                    'media_alt_source' =>  false
                ]
            )
        ));
    }

    /**
     * @param $bundles
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function prependSonataAdminConfig($bundles, ContainerBuilder $container){
        if (!isset($bundles['SonataAdminBundle'])) {
            throw new \Exception("SonataAdminBundle bundle is not enabled");
        }

        $container->prependExtensionConfig('sonata_admin', array(
            'title'  =>  'EdComs CMS',
            'title_logo' => 'bundles/edcomscmsadmin/icons/edcoms_logo.png',
            'templates' => array(
                'search_result_block' => 'SonataAdminBundle:Block:block_search_result.html.twig',
                'layout'              => 'EdcomsCMSAdminBundle::layout.html.twig'
            ),
            'security'=>array(
                "handler"=> 'sonata.admin.security.handler.role'
            )
        ));
    }

    /**
     * @param $bundles
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function prependSonataBlockConfig($bundles, ContainerBuilder $container){
        if (!isset($bundles['SonataBlockBundle'])) {
            throw new \Exception("SonataBlockBundle bundle is not enabled");
        }

        $container->prependExtensionConfig('sonata_block', array(
            'default_contexts' => array('cms'),
            'blocks' => array(
                'sonata.admin.block.admin_list' => array(
                    'contexts'  => array('cms')
                ),
                'sonata.admin.block.search_result' => array(
                    'contexts'  => array('cms')
                ),
            ),
        ));
    }

    /**
     * @param $bundles
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function prependEdcomsCMSContentConfig($bundles, ContainerBuilder $container){
        if (!isset($bundles['EdcomsCMSContentBundle'])) {
            throw new \Exception("EdcomsCMSContent bundle is not enabled");
        }

        $container->prependExtensionConfig('edcoms_cms_content', array(
            'email' => '%mailer_user%'
        ));
    }

    /**
     * @param $bundles
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function prependEdcomsCMSBatchConfig($bundles, ContainerBuilder $container){
        if (!isset($bundles['EdcomsCMSBadgeBundle'])) {
            throw new \Exception("EdcomsCMSBadge bundle is not enabled");
        }

        $container->prependExtensionConfig('edcoms_cms_badge', array());
    }

    private function attachAssets($config, ContainerBuilder $container){
        $container->setParameter('edcoms.admin.assets.stylesheets', isset($config['assets']['stylesheets']) ? $config['assets']['stylesheets'] : [] );
        $container->setParameter('edcoms.admin.assets.javascripts', isset($config['assets']['javascripts']) ? $config['assets']['javascripts'] : [] );
    }
}
