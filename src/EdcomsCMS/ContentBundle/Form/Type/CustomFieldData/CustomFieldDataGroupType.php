<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData;

use EdcomsCMS\ContentBundle\Entity\CustomFieldData;
use EdcomsCMS\ContentBundle\Form\Type\Content\ContentFieldsType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CustomFieldDataGroupType extends CustomFieldDataType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm( $builder, $options);

        $builder
            ->add('_type',HiddenType::class,array(
                "data"=> 'group_type',
                "mapped"=> false
            ))
            ->add('value', HiddenType::class,array(
                "data" => 'group'
            ))
            ;


        if(isset($options['is_prototype']) && $options['is_prototype']===true){
            $builder
                ->add('children', ContentFieldsType::class,array(
                    "label" => false,
                    "context" => ContentFieldsType::CONTEXT_CUSTOMFIELD,
                    "customFields" => $options['customField'],
                    "is_prototype" => $options['is_prototype']
                ));
        }



        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var CustomFieldData $customFieldData */
            $customFieldData = $event->getData();
            $form = $event->getForm();
            $options = $form->getConfig()->getOptions();
            if($options['is_prototype']===false){
                $form
                    ->add('children', ContentFieldsType::class,array(
                        "label" => false,
                        "context" => ContentFieldsType::CONTEXT_CUSTOMFIELD,
                        "customFields" => $options['customField'],
                        "customFieldsData" => $customFieldData,
                        "is_prototype" => $options['is_prototype']
                    ));
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use($options) {
            /** @var CustomFieldData $customFieldData */
            $customFieldData = $event->getData();
            if($customFieldData){
                foreach ($customFieldData->getChildren() as $child){
                    $child->setParent($customFieldData);
                }
            }
        });
    }

}