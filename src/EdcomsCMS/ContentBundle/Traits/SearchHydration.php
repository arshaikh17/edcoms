<?php

namespace EdcomsCMS\ContentBundle\Traits;

use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\Structure;

/**
 * SearchHydration contains the various methods required to hydrate and prepare an object for Search Indexing
 *
 * @author richard
 */
trait SearchHydration {
    public function contentIndex(Content $content)
    {
        /**
         * @var Structure
         */
        $structure = $content->getStructure();
        $link = $structure->getFullLink();
        $added = $structure->getAddedOn();
        $status = $content->getStatus();
        $output = [
            'id'=>$content->getId(),
            'structure'=>$structure->getId(),
            'status'=>$status,
            'title'=>$content->getTitle(),
            'url'=>(!empty($link)) ? implode('/', $link) : '/',
            'parent'=>(!is_null($structure->getParent())) ? $structure->getParent()->getId() : 0,
            'date_added'=>(!empty($added)) ? $added : new \DateTime(),
            'content_type'=>$content->getContentType()->getName(),
            'template_file'=>$content->getTemplateFile()->getTemplateFile(),
            'section'=>reset($link),
            'index_type'=>'content'
        ];
        $content->getCustomFieldData()->forAll(function($ind, $field) use (&$output) {
            if ($field->getCustomFields()->getRepeatable()) {
                // this is an array object \\
                if (!isset($output[$field->getCustomFields()->getName()])) {
                    $output[$field->getCustomFields()->getName()] = ['group', []];
                }
            } else if ($field->getCustomFields()->getParent() !== null) {
                if (!isset($output[$field->getCustomFields()->getName()])) {
                    $output[$field->getCustomFields()->getParent()->getName()] = ['group', []];
                }
                $output[$field->getCustomFields()->getParent()->getName()][1][] = [$field->getCustomFields()->getFieldType(), $field->getValue()];
            } else {
                $output[$field->getCustomFields()->getName()] = [$field->getCustomFields()->getFieldType(), $field->getValue()];
            }
            return true;
        });
        if(isset($output['link_parent']) && $output['link_parent'][1] == '1') {
            $link = $structure->getParent()->getParent()->getFullLink();
            $output['parent_url'] = (!empty($link)) ? implode('/', $link):'/';
        }

        return $output;
    }
}
