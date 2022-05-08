<?php

namespace EdcomsCMS\ContentBundle\Traits;

use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\CustomFieldData;

/**
 * Description of ContentCustomFieldHydration
 *
 * @author richard
 */
trait ContentCustomFieldHydration {

    /**
     * This method returns the content as an entity ready for use
     * @param Content $content
     * @return Content
     */
    public function handleContent(Content $content)
    {
        // need to map the custom fields to the custom field data \\
        $fields = [];

        // Store Repeatable Groups names in order to apply some formatting later.
        $repeatableGroupsNames = [];

        foreach ($content->getCustomFieldData() as $customFieldData) {
            /** @var CustomFieldData $customFieldData */

            // Go through the root fields only
            if(!$customFieldData->getCustomFields()->getParent()){
                // If field is a Group
                if($customFieldData->getCustomFields()->getFieldType()=="group"){
                    if(!isset($fields[$customFieldData->getCustomFields()->getName()])){
                        $fields[$customFieldData->getCustomFields()->getName()] = [];
                    }
                    // If group is Repeatable
                    if($customFieldData->getCustomFields()->getRepeatable()){
                        $repeatableGroupsNames[] = $customFieldData->getCustomFields()->getName();
                        if(!isset($fields[$customFieldData->getCustomFields()->getName()][$customFieldData->getId()])){
                            $fields[$customFieldData->getCustomFields()->getName()][$customFieldData->getId()] = [];
                        }
                        // Go through children fields
                        foreach ($customFieldData->getChildren() as $child){
                            /** @var CustomFieldData $child */
                            // If child field is repeatable
                            if($child->getCustomFields()->getRepeatable()){
                                if(!isset($fields[$customFieldData->getCustomFields()->getName()][$customFieldData->getId()][$child->getCustomFields()->getName()] )) {
                                    $fields[$customFieldData->getCustomFields()->getName()][$customFieldData->getId()][$child->getCustomFields()->getName()] = [];
                                }
                                $fields[$customFieldData->getCustomFields()->getName()][$customFieldData->getId()][$child->getCustomFields()->getName()][] = $this->getFieldValue($child);
                            }else{
                                $fields[$customFieldData->getCustomFields()->getName()][$customFieldData->getId()][$child->getCustomFields()->getName()] = $this->getFieldValue($child);
                            }
                        }
                        // If group is not Repeatable
                    }else{
                        foreach ($customFieldData->getChildren() as $child){
                            /** @var CustomFieldData $child */
                            // If child field is repeatable
                            if($child->getCustomFields()->getRepeatable()){
                                if(!isset($fields[$customFieldData->getCustomFields()->getName()][$child->getCustomFields()->getName()] )) {
                                    $fields[$customFieldData->getCustomFields()->getName()][$child->getCustomFields()->getName()] = [];
                                }
                                $fields[$customFieldData->getCustomFields()->getName()][$child->getCustomFields()->getName()][] = $this->getFieldValue($child);
                            }else{
                                $fields[$customFieldData->getCustomFields()->getName()][$child->getCustomFields()->getName()] = $this->getFieldValue($child);
                            }
                        }
                    }
                    // If field is not a Group
                }else{
                    // If field is Repeatable
                    if($customFieldData->getCustomFields()->getRepeatable()){
                        if(!isset($fields[$customFieldData->getCustomFields()->getName()])){
                            $fields[$customFieldData->getCustomFields()->getName()] = [];
                        }
                        $fields[$customFieldData->getCustomFields()->getName()][] = $this->getFieldValue($customFieldData);
                    }else{
                        $fields[$customFieldData->getCustomFields()->getName()] = $this->getFieldValue($customFieldData);
                    }
                }
            }
        }

        foreach ($fields as $key => $field){
            if(in_array($key,$repeatableGroupsNames)){
                $fields[$key] = array_values($field);
            }
        }

        $content->setCustomFieldDataArr($fields);

        return $content;
    }

    /**
     * @param CustomFieldData $customFieldData
     * @return mixed|string
     */
    private function getFieldValue(CustomFieldData $customFieldData){
        if (strstr($customFieldData->getCustomFields()->getFieldType(), 'array') === false && strstr($customFieldData->getCustomFields()->getFieldType(), 'files') === false) {
            $resp = (is_null(json_decode($customFieldData->getValue()))) ? $customFieldData->getValue() : json_decode($customFieldData->getValue());
        } else {
            $resp = $customFieldData->getValue();
        }
        return $resp;
    }

//    /**
//     *
//     * The method has been replaced by the method above. However it has been extended to support Groups and Repeatable fields. Keep it for reference until QA completed
//     * This method returns the content as an entity ready for use
//     * @param Content $content
//     * @return Content
//     */
//    public function handleContent(Content $content)
//    {
//        // need to map the custom fields to the custom field data \\
//        $fields = [];
//
//        foreach ($content->getCustomFieldData() as $customFieldData) {
//            /** @var CustomFieldData $customFieldData */
//            // RW UPDATE - 07/10/16 - allowed for the same field to exist multiple times \\
//            if (strstr($customFieldData->getCustomFields()->getFieldType(), 'array') === false && strstr($customFieldData->getCustomFields()->getFieldType(), 'files') === false) {
//                $resp = (is_null(json_decode($customFieldData->getValue()))) ? $customFieldData->getValue() : json_decode($customFieldData->getValue());
//            } else {
//                $resp = $customFieldData->getValue();
//            }
//            if ($customFieldData->getCustomFields()->getFieldType() === 'group') {
//                if (!isset($fields[$customFieldData->getCustomFields()->getName()])) {
//                    $fields[$customFieldData->getCustomFields()->getName()] = [];
//                }
//            } else if (!is_null($customFieldData->getParent())) {
//                if (!isset($fields[$customFieldData->getParent()->getCustomFields()->getName()])) {
//                    $fields[$customFieldData->getParent()->getCustomFields()->getName()] = [];
//                }
//                $hasRepeatableParent = false;
//                if($customFieldData->getParent()->getCustomFields()->getRepeatable()){
//                    $hasRepeatableParent = true;
//                    if(!isset($fields[$customFieldData->getParent()->getCustomFields()->getName()][$customFieldData->getParent()->getId()])){
//                        $fields[$customFieldData->getParent()->getCustomFields()->getName()][$customFieldData->getParent()->getId()] = [];
//                    }
//                }
//                if($customFieldData->getCustomFields()->getRepeatable()){
//                    if($hasRepeatableParent){
//                        if(!isset($fields[$customFieldData->getParent()->getCustomFields()->getName()][$customFieldData->getParent()->getId()][$customFieldData->getCustomFields()->getName()]) || !is_array($fields[$customFieldData->getParent()->getCustomFields()->getName()][$customFieldData->getParent()->getId()][$customFieldData->getCustomFields()->getName()])){
//                            $fields[$customFieldData->getParent()->getCustomFields()->getName()][$customFieldData->getParent()->getId()][$customFieldData->getCustomFields()->getName()] = [];
//                        }
//                        $fields[$customFieldData->getParent()->getCustomFields()->getName()][$customFieldData->getParent()->getId()][$customFieldData->getCustomFields()->getName()][] = $resp;
//                    }else{
//                        if(!isset($fields[$customFieldData->getParent()->getCustomFields()->getName()][$customFieldData->getCustomFields()->getName()]) || !is_array($fields[$customFieldData->getParent()->getCustomFields()->getName()][$customFieldData->getCustomFields()->getName()])){
//                            $fields[$customFieldData->getParent()->getCustomFields()->getName()][$customFieldData->getCustomFields()->getName()] = [];
//                        }
//                        $fields[$customFieldData->getParent()->getCustomFields()->getName()][$customFieldData->getCustomFields()->getName()][] = $resp;
//                    }
//
//                }else{
//                    if($hasRepeatableParent){
//                        $fields[$customFieldData->getParent()->getCustomFields()->getName()][$customFieldData->getParent()->getId()][$customFieldData->getCustomFields()->getName()] = $resp;
//                    }else{
//                        $fields[$customFieldData->getParent()->getCustomFields()->getName()][$customFieldData->getCustomFields()->getName()] = $resp;
//                    }
//                }
//
//            } else {
//                if($customFieldData->getCustomFields()->getRepeatable()){
//                    if(!isset($fields[$customFieldData->getCustomFields()->getName()]) || !is_array($fields[$customFieldData->getCustomFields()->getName()])){
//                        $fields[$customFieldData->getCustomFields()->getName()] = [];
//                    }
//                    $fields[$customFieldData->getCustomFields()->getName()][] = $resp;
//                }else{
//                    $fields[$customFieldData->getCustomFields()->getName()] = $resp;
//                }
//
//            }
//        }
//        foreach ($fields as $key => $field){
//            if(is_array($field)){
//                $fields[$key] = array_values($field);
//            }
//        }
//        $content->setCustomFieldDataArr($fields);
//
//        return $content;
//    }
}
