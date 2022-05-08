<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\ContentType;

use EdcomsCMS\AdminBundle\Form\Type\PolyCollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


/**
 * Class ContentTypeFieldsType
 * @package EdcomsCMS\ContentBundle\Form\Type\ContentType
 */
class ContentTypeFieldsType extends PolyCollectionType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder,$options);

        /**
         * The following is a hacky way to overwrite the subfields data of the hidden Form.
         * At the moment the subfields are displayed twice
         *
         *     - As children fields of a Group CustomField
         *     - As hidden forms in the collection @see CustomFieldType::buildView()
         *
         * This results to the issue where the hidden fields overwrite the visible fields.
         *
         * @todo Refactor. Subfields should be removed from the Collection form and should be added again on Submit.
         *
         */
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if(is_array($data) && count($data)){
                $subfields = [];
                $rootFields = [];
                /** Find all subfields and store them at $subfields */
                foreach ($data as $customField){
                    if(isset($customField['children']) && is_array($customField['children']) && count($customField['children'])){
                        $rootFields[$customField['name']] = $customField;
                        foreach ($customField['children'] as $subfield){
                            $subfields[$subfield['name']] = $subfield;
                        }
                    }
                }
                foreach ($data as $key=>$customField){
                    if(isset($subfields[$customField['name']])){
                        $data[$key] = $subfields[$customField['name']];
                    }
                }
            }

            $event->setData($data);
        });
    }

}