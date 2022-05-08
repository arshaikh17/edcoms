<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\EventListener\Content;

use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\CustomFieldData;
use EdcomsCMS\ContentBundle\Entity\CustomFields;
use EdcomsCMS\ContentBundle\Form\Type\Content\ContentFieldsType;
use EdcomsCMS\ContentBundle\Service\Content\ContentService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Class ResizeContentFieldsListener
 * @package EdcomsCMS\ContentBundle\Form\EventListener\Content
 */
class ResizeContentFieldsListener implements EventSubscriberInterface
{

    /** @var  ContentService */
    private $contentService;

    private $customFieldData;

    private $options;

    public function __construct(ContentService $contentService, array $options)
    {
        $this->contentService = $contentService;
        $this->options = $options;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'preSubmit',
            FormEvents::SUBMIT => array('onSubmit', 50),
        );
    }

    private function getCustomFieldData(CustomFields $customField, Content $content){
        if(!$this->customFieldData){
            $this->customFieldData = array();
            /** @var CustomFieldData $cfd */
            foreach ($content->getCustomFieldData() as $cfd){
                $cfdName = $cfd->getCustomFields()->getName();
                if(!isset($this->customFieldData[$cfdName])){
                    $this->customFieldData[$cfdName] = [];
                }
                $this->customFieldData[$cfdName][] = $cfd;
            }
        }
        return $this->customFieldData[$customField->getName()] ? $this->customFieldData[$customField->getName()]  : null;
    }

    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // Remove all empty rows
        foreach ($form as $name => $child) {
            if (!isset($data[$name])) {
                $form->remove($name);
            }else{
                if($form->all()){
                    $childData = $data[$name];
                    foreach ($child as $childName=>$subForm){
                        if (!isset($childData[$childName])) {
                            $subForm->remove($childName);
                        }
                    }
            }
            }
        }
    }

    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $submittedData = $event->getData();

        $dataToAdd = array();

        // Flat submitted CustomFieldData along with their children
        foreach ($submittedData as $key => $sData){
            if(!is_numeric($key)){
                if(is_array($sData)){
                    foreach ($sData as $rfield){
                        if(!in_array($rfield, $dataToAdd)){
                            $dataToAdd[] = $rfield;
                            if($rfield->getChildren()->count()!=0){
                                foreach ($rfield->getChildren() as $child){
                                    $dataToAdd[] = $child;
                                }
                            }
                        }
                    }
                }else{
                    if(!in_array($sData, $dataToAdd)){
                        $dataToAdd[] = $sData;
                        if($sData->getChildren()->count()!=0){
                            foreach ($sData->getChildren() as $child){
                                $dataToAdd[] = $child;
                            }
                        }
                    }
                }
            }
        }

        // Remove CustomFieldData that have been removed
        foreach ($submittedData as $key => $sData){
            if(is_numeric($key)){
                if(!in_array($sData,$dataToAdd)){
                    unset($submittedData[$key]);
                }
            }else{
                $submittedData[$key] = null;
                unset($submittedData[$key]);
            }
        }

        // Add new CustomFieldData to $submittedData
        if(is_array($submittedData)){
            foreach ($dataToAdd as $key => $sData){
                if(!in_array($sData,$submittedData)){
                    $submittedData[] = $sData;
                }
            }
        }else{
            foreach ($dataToAdd as $key => $sData){
                if(!$submittedData->contains($sData)){
                    $submittedData->add($sData);
                }
            }
        }

        $finalData = $this->options['context']==ContentFieldsType::CONTEXT_CONTENT ? $dataToAdd : $submittedData;
        $event->setData($finalData);
    }

}