<?php
namespace EdcomsCMS\ContentBundle\Helpers;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\PersistentCollection;
use EdcomsCMS\ContentBundle\Controller\DisplayController;
use EdcomsCMS\ContentBundle\Entity\Structure;

/**
 * Description of Navigation
 *
 * @author richard
 */
class Navigation {
    private $structure;
    private $allStructures;
    private $nav = [];
    private $curNav = 0;
    private $controller;
    private $contentRepository;
    private $structureRepository;
    
    public function __construct(DisplayController $controller, EntityRepository $contentRepository, EntityRepository $structureRepository)
    {
        $this->controller = $controller;
        $this->contentRepository = $contentRepository;
        $this->structureRepository = $structureRepository;
    }
    
    public function createNav($parent = '', $level = 0, $visibility = false )
    {
        // reset the nav to start \\
        $this->nav = [];

        // get the ID of the parent \\
        $parentObj = $this->structureRepository->findOneByRoot($parent,$visibility);

        $this->structure = $this->structureRepository->findByParent($parentObj, $visibility);

        $structures = $this->structureRepository->findAllAndKeys(false,$visibility);
        $this->allStructures = $structures['structures'];
        $keys = $structures['keys'];
        $this->recurseStructures($this->structure);
        
        $allContent = $this->contentRepository->findByStructureArr($keys, 'published');
        $this->controller->allContent = $allContent;
        
        $this->recurseNav($this->structure, $this->nav, $this->curNav, $level);
        return $this->nav;
    }

    private function recurseStructures($structure)
    {
        if (is_array($structure)) {
            array_walk($structure, array(&$this, 'PrepChildren'));
        } else if (is_a($structure, 'Doctrine\ORM\PersistentCollection') || is_a($structure, 'Doctrine\Common\Collections\ArrayCollection')) {
            $structure->forAll(function($id, $item) {$this->PrepChildren($item, $id); return true;});
        }
    }
    private function PrepChildren($struct, $ind)
    {
        $children = $struct->getChildren();
        if (!empty($children)) {
            $children->forAll(function($id, $item) use ($struct) {
                $struct->setChildrenArr($item);
                // must return true to continue looping \\
                return true;
            });
            
            
            $this->recurseStructures($children);
        }
        $this->allStructures[$struct->getId()] = $struct;
    }
    private function recurseNav($structure, &$parent, $curNav, $level = 0)
    {
        if (!isset($parent)) {
            $parent = [];
        }
        array_walk($structure, array(&$this, 'PrepNav'), ['parent'=>&$parent, 'curNav'=>$curNav, 'level'=>$level]);
    }
    private function PrepNav($struct, $ind, $params)
    {
        $parent = &$params['parent'];
        $curNav = $params['curNav'];
        $level = $params['level'];
        if (!is_array($struct)) {
            $this->curNav = $curNav;
            $allContent = $this->controller->allContent;
            
            if (isset($allContent[$struct->getId()]) && isset($this->allStructures[$struct->getId()])) {
                $content = $allContent[$struct->getId()];
                if ($content) {
                    /* language detection for titles */
                    $langName = $this->controller->langName;
                    $title = ($this->controller->lang === $this->controller->defaultLang) ? $struct->getTitle() : $content->getCustomFieldData()->filter(function ($customField) use ($langName) {
                        return ($customField->getCustomFields()->getName() === $langName . '_title');
                    });
                    if (empty($title)) {
                        $title = $struct->getTitle();
                    }
                    $parent[$struct->getId()] = ['title' => $title, 'content_type' => $content->getContentType(), 'custom_fields' => $content->getCustomFieldData(), 'link' => '/' . $struct->getFullLink(true), 'children' => [], 'isActive' => (isset($this->rawpath[$this->curNav + 1]) && $struct->getLink() === $this->rawpath[$this->curNav + 1]) ? true : false];
                    if (!empty($this->allStructures[$struct->getId()]->getChildrenArr())) {
                        $this->curNav++;
                    }
                }
                if ($level === 0 || $level > $this->curNav) {
                    $this->recurseNav($this->allStructures[$struct->getId()]->getChildrenArr(), $parent[$struct->getId()]['children'], $this->curNav);
                }
            }
        }
    }
}
