<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\Content;

use Doctrine\Common\Collections\ArrayCollection;
use EdcomsCMS\AdminBundle\Form\Type\PolyCollectionType;
use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\CustomFieldData;
use EdcomsCMS\ContentBundle\Entity\CustomFields;
use EdcomsCMS\ContentBundle\Form\EventListener\Content\ResizeContentFieldsListener;
use EdcomsCMS\ContentBundle\Service\Content\ContentService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentFieldsType extends AbstractType
{

    const CONTEXT_CONTENT = 'context';
    const CONTEXT_CUSTOMFIELD = 'customfield';

    private $contentService;

    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventSubscriber(new ResizeContentFieldsListener($this->contentService, $options));

        /** @var Content $content */
        switch($options['context']){
            case self::CONTEXT_CONTENT:
                $content = $options['content'];
                if(!$content){
                    throw new \Exception("Content not found. The ResizeContentFieldsListener supports only Content Form");
                }
                $customFields = $content->getContentType()->getCustomFields();
                $customFieldsData = $content->getCustomFieldData();
                break;
            case self::CONTEXT_CUSTOMFIELD:
                $content = $options['content'];
                /** @var ArrayCollection $customFields */
                $customFields = $options['customFields']->getChildren();
                if($options['is_prototype']===true || !$options['customFieldsData']){
                    $customFieldsData = new ArrayCollection();
                }else{
                    $customFieldsData = $options['customFieldsData']->getChildren();
                }
                break;
            default:
                throw new \Exception("Context is missing");
        }

        /** @var CustomFields $customField */
        foreach ($customFields as $customField){
            if(($customField->getParent() && $options['context']==ContentFieldsType::CONTEXT_CONTENT) || $customField->getFieldType()==="hidden"){
                continue ;
            }
            $formFieldId = $customField->getName();
            $fieldLabel = $customField->getLabel();
            $fieldType =  $this->contentService->getFieldType($customField);
            if(!$customField->getRepeatable()){
                if($options['is_prototype']===true){
                    $fieldData = null;
                }else{
                    $fieldData = $this->getCustomFieldData($customField, $customFieldsData) ? $this->getCustomFieldData($customField,$customFieldsData)[0] : null;
                }

                $builder->add($formFieldId, $fieldType,array(
                    "label"=> $fieldLabel,
                    "help"=> $customField->getDescription(),
                    "customField" => $customField,
                    "content" => $content,
                    "data"=> $fieldData,
                    "required" => $customField->getRequired()
                ));
            }else{
                $fieldData = $this->getCustomFieldData($customField,$customFieldsData);
                $builder->add($formFieldId,PolyCollectionType::class,array(
                    'types'=> array(
                        $fieldType
                    ),
                    'types_options'=>array(
                        $fieldType  => array(
                            "customField" => $customField,
                            "content" => $content,
                            "customFieldsData" => $fieldData
                        ),
                    ),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'mapped' => true,
                    "label" => $fieldLabel,
                    "help"=> $customField->getDescription(),
                    "data"=> $fieldData,
                    "required" => $customField->getRequired(),
                    "collapsed" => false
                ));
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'context' => ContentFieldsType::CONTEXT_CONTENT,
            'customFields' => null,
            'customFieldsData' => null,
            'content'   => null,
            'is_prototype' => false
        ));
    }

    public function getName()
    {
        return 'type_content_fields';
    }

    private function getCustomFieldData(CustomFields $customField, $customFieldsData){
        $dataToReturn = array();
        /** @var CustomFieldData $cfd */
        foreach ($customFieldsData as $cfd){
            $cfdName = $cfd->getCustomFields()->getName();
            if(!isset($dataToReturn[$cfdName])){
                $dataToReturn[$cfdName] = [];
            }
            $dataToReturn[$cfdName][] = $cfd;
        }
        return isset($dataToReturn[$customField->getName()]) ? $dataToReturn[$customField->getName()]  : null;
    }
}