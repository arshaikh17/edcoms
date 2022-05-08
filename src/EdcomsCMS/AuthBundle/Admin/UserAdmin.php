<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\AuthBundle\Admin;

use EdcomsCMS\AuthBundle\Entity\cmsUsers;
use EdcomsCMS\AuthBundle\Form\Type\PersonType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Length;

class UserAdmin extends AbstractAdmin
{

    protected $maxPerPage = 100;

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var cmsUsers $user */
        $user = $this->getSubject();

        $formMapper
            ->tab('General')
                ->with('Details',array('class' => 'col-md-8'))
                    ->add('username')
                    ->add('person', PersonType::class,array(
                        'label'=>false
                    ))
                ->end()
                ->with('Advanced',array('class' => 'col-md-4'))
                    ->add('is_active')
                    ->add('deleted')
                    ->add('new_password', PasswordType::class, array(
                        'mapped'=>false,
                        'required'=>$user->getId() ? false : true,
                        'label' => 'New password',
                        'constraints' => [new Length(["min"=>6])],
                        'attr'=> ['autocomplete' => 'new-password']
                    ))
                ->end()
            ->end()
            ->tab('Groups')
                ->with('Groups')
                    ->add('groups', null,array(
                        "expanded" => true,
                        "label"=> false
                    ))
                ->end()
            ->end()
            ;

        $passwordEncoder = $this->configurationPool->getContainer()->get('security.password_encoder');
        $formMapper->getFormBuilder()->addEventListener(FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use($passwordEncoder, $user){
                $data = $event->getData();
                if(isset($data['new_password']) && $data['new_password']!=''){
                    $user->setPassword($passwordEncoder->encodePassword($user, $data['new_password']));
                }
            });
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('person.firstName')
            ->add('person.lastName')
            ->add('groups')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('username')
            ->add('person.firstName')
            ->add('person.lastName')
            ->add('addedOn')
            ->add('is_active')
        ;
    }

    public function getExportFields()
    {
        // avoid security field to be exported
        return array_filter(parent::getExportFields(), function ($v) {
            return !in_array($v, array('password', 'salt'));
        });
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);

        return $actions;
    }

}