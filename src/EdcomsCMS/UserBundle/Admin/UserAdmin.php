<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\UserBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\UserBundle\Admin\Entity\UserAdmin as SonataUserAdmin;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class UserAdmin extends SonataUserAdmin
{

    protected $baseRouteName = 'sonata.admin';
    protected $baseRoutePattern = 'sonata.admin';

    /**
     * This overwrite the SonataUser admin as validation groups are not used at the moment
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function getFormBuilder()
    {
        $this->formOptions['data_class'] = $this->getClass();

        $formBuilder = $this->getFormContractor()->getFormBuilder(
            $this->getUniqid(),
            $this->formOptions
        );

        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query
            ->andWhere("o.rtbfApplied=:rtbf_status OR o.rtbfApplied IS NULL")
            ->setParameter('rtbf_status', false)
        ;
        return $query;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {

        $formMapper
            ->tab('General')
                ->with('Details',array('class' => 'col-md-8'))
                    ->add('firstName', TextareaType::class,[
                        'label' => 'First name'
                    ])
                    ->add('lastName', TextareaType::class,[
                        'label' => 'Last name'
                    ])
                    ->add('username')
                    ->add('email')
                    ->add('enabled')
                    ->add('lastLogin','sonata_type_datetime_picker',array(
                        "disabled" => true
                    ))
                    ->add('groups')
                ->end()
                ->with('Advanced',array('class' => 'col-md-4'))
                    ->add('plainPassword', PasswordType::class, array(
                        'required' => (!$this->getSubject() || is_null($this->getSubject()->getId())),
                        'constraints' => [new Length(['min'=> 6 ])],
                        'attr'=> ['autocomplete' => 'new-password']
                    ))
                ->end()
            ->end()
            ->tab('Roles')
                ->with('Roles')
                    ->add('roles','sonata_security_roles', array(
                        'label' => 'form.label_roles',
                        'expanded' => true,
                        'multiple' => true,
                        'required' => false,
                    ))
                ->end()
            ->end()
        ;
    }

    // Define what User fields to export
    public function getExportFields()
    {
        $fields = array('firstName', 'lastName', 'email', 'lastLogin', 'enabled', 'createdAt');

        foreach ($this->getExtensions() as $extension) {
            if (method_exists($extension, 'configureExportFields')) {
                $fields = $extension->configureExportFields($this, $fields);
            }
        }

        return array_filter($fields, function ($v) {
            return !in_array($v, array('password', 'salt'));
        });
    }

    // Overwrite getDataSourceIterator method in order to increase the max exexution time. It is needed as it might take more than 30 seconds to export/download many thousands of user records
    public function getDataSourceIterator()
    {
        ini_set('max_execution_time', 120);
        return parent::getDataSourceIterator(); // TODO: Change the autogenerated stub
    }

    public function setBaseRouteName($baseRouteName)
    {
        $this->baseRouteName = $baseRouteName;
    } 

    public function setBaseRoutePattern($baseRoutePattern)
    {
        $this->baseRoutePattern = $baseRoutePattern;
    }

  /**
   * {@inheritdoc}
   */
  protected function configureShowFields(ShowMapper $showMapper)
  {
    $showMapper
      ->with('General', array('class'=>'col-md-6'))
        ->add('username')
        ->add('email')
        ->add('enabled', null, [
          'label' => 'Enabled'
        ])
        ->add('confirmed', null, [
          'label' => 'Email confirmed'
        ])
        ->add('createdAt', null, [
          'label' => 'Registration date'
        ])
        ->add('updatedAt', null, [
          'label' => 'Latest profile update at'
        ])
        ->add('groups')
      ->end()
      ->with('Profile', array('class'=>'col-md-6'))
        ->add('firstname')
        ->add('lastname')
      ->end()
      ->with('Activity', array('class'=>'col-md-6'))
        ->add('lastLogin', null,[
          'label' => 'Last log in at'
        ])
        ->add('passwordRequestedAt', null, [
          'label' => 'Password requested at'
        ])
        ->add('emailChangeRequestedAt', null, [
          'label' => 'Email change requested at'
        ])
        ->add('pendingEmail', null, [
          'label' => 'Pending email'
        ])
      ->end()
    ;
  }

    protected function configureDatagridFilters(DatagridMapper $filterMapper) {
        parent::configureDatagridFilters(
            $filterMapper
        );
        $filterMapper->add('createdAt','doctrine_orm_date_range', [
                'label'  => 'Registration date',
                'field_type'=>'sonata_type_datetime_range_picker',
                'field_options' => array(
                    'field_options_start' => array(
                        'date_format'           => 'dd/MM/yyyy',
                        'format'                => 'd/M/y',
                        'required'              => true,
                        'dp_pick_time'          => false
                    ),
                    'field_options_end' => array(
                        'date_format'           => 'dd/MM/yyyy',
                        'format'                => 'd/M/y',
                        'required'              => true,
                        'dp_pick_time'          => false
                    )
                )
            ]
        );
    }

    protected function configureRoutes(RouteCollection $collection)
    {

        $collection->add('rtbf', $this->getRouterIdParameter().'/rtbf');
        $collection->add('rtbf_user_check', 'rtbf-user-check');
    }

    public function configureActionButtons($action, $object = null)
    {
        $list = parent::configureActionButtons($action, $object);

        $container = $this->getConfigurationPool()->getContainer();
        $RTBFService = $container->get('edcoms.user.service.rtbf_user');

        if($action=="edit"
            && $this->isGrantedRTBFAccess()
            && $RTBFService->isRTBFAllowed($this->getSubject())
        ){
            $list['rtbf']['template'] = 'EdcomsCMSUserBundle:Admin:User/edit__action_rtbf.html.twig';
        }

        if($action=="list"){
            $list['rtbfUserCheck']['template'] = 'EdcomsCMSUserBundle:Admin:User/list__action_rtbf_check.html.twig';
        }

        return $list;
    }

    private function isGrantedRTBFAccess(){
        $container = $this->getConfigurationPool()->getContainer();
        $authorizationChecker = $container->get('security.authorization_checker');
        return $authorizationChecker->isGranted('ROLE_RTBF');
    }
}
