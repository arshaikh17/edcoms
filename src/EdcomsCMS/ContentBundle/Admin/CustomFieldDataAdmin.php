<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Admin;

use EdcomsCMS\ContentBundle\Entity\CustomFieldData;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CustomFieldDataAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->getFormBuilder()->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var CustomFieldData $customFieldData */
            $customFieldData = $event->getData();
            if(!$customFieldData || !$customFieldData->getCustomFields()){
                return ;
            }
            $form = $event->getForm();
            $form->add('value', 'text',array(
                'label' => $customFieldData->getCustomFields()->getName()
            ));
        });
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('value');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('value');
    }
}