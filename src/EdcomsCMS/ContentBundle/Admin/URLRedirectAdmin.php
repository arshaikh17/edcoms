<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Admin;

use EdcomsCMS\ContentBundle\Entity\URLRedirect;
use EdcomsCMS\UserBundle\Entity\User;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class URLRedirectAdmin extends AbstractAdmin
{

    /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage  */
    private $tokenStorage;

    public function __construct($code, $class, $baseControllerName, Container $container)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->tokenStorage = $container->get('security.token_storage');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, array('edit', 'show'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id      = $admin->getRequest()->get('id');

        $menu->addChild(
            "Edit",
            array('uri' => $admin->generateUrl('edit', array('id' => $id)))
        );

        $menu->addChild(
            "Usage",
            array('uri' => $admin->generateUrl('edcoms.content.admin.url_redirect_usage.list', array('id' => $id)))
        );

    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('url', null, [
                'label' => 'Redirect path',
                'help' => 'The URL path to get redirected (not the full URL). For example to redirect the page http://www.domain.com/ww2018, the path is "ww2018" '
            ])
            ->add('type', ChoiceFieldMaskType::class,[
                'choices' => [
                    'Page' => URLRedirect::TYPE_STRUCTURE,
                    'Custom link' => URLRedirect::TYPE_FREE_TEXT
                ],
                'map' => array(
                    URLRedirect::TYPE_FREE_TEXT => array('destinationLink'),
                    URLRedirect::TYPE_STRUCTURE => array('destinationStructure'),
                ),
                'placeholder' => 'Choose type',
            ])
            ->add('destinationStructure', null, [
                'label' => 'Destination page'
            ])
            ->add('destinationLink', null, [
                'label' => 'Destination path'
            ])
            ->add('isVanityUrl')
            ->add('isTemporaryRedirect')
            ->add('active')
            ->add('trackUsage', null, [
                'label' => 'Track usage'
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('active')
            ->add('isVanityUrl')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('url')
            ->add('active', null, array('editable' => true))
            ->add('createdAt')
            ->add('createdBy')
            ->add('lastUpdatedBy')
        ;
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('url')
            ->add('redirectPath')
            ->add('active')
            ->add('isVanityUrl')
            ->add('isTemporaryRedirect')
            ->add('trackUsage')
            ->add('createdAt')
            ->add('createdBy')
            ->add('updatedAt')
            ->add('lastUpdatedBy')
        ;
    }

    public function prePersist($object)
    {
        /** @var URLRedirect $object */
        $user = $this->tokenStorage->getToken()->getUser();
        if($user && is_object($user) && $user instanceof User){
            $object->setCreatedBy($user);
        }
        parent::postPersist($object);
    }

    public function preUpdate($object)
    {
        /** @var URLRedirect $object */
        $user = $this->tokenStorage->getToken()->getUser();
        if($user && is_object($user) && $user instanceof User){
            $object->setLastUpdatedBy($user);
        }
        parent::postPersist($object);
    }


}