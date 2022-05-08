<?php

namespace EdcomsCMS\ContentBundle\Helpers;

use EdcomsCMS\ContentBundle\Entity\ContentType;
use EdcomsCMS\ContentBundle\Entity\CustomFields;
use EdcomsCMS\ContentBundle\Entity\CustomFieldData;
use EdcomsCMS\ContentBundle\Entity\Structure;
use EdcomsCMS\ContentBundle\Entity\Content;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;

/**
 * SymlinkHelper is used to handle the Symlink content type, and additional setting methods
 *
 * @author richard
 */
class SymlinkHelper {
    protected $ContentType;
    protected $service;
    protected $container;
    protected $em;
    public function __construct(Container $container, $doctrine)
    {
        $this->container = $container;
        $this->doctrine = $doctrine;
        $this->em = $this->doctrine->getManager('edcoms_cms');
        // need to get the symlink content type if it exists \\
        $contentTypes = $this->em->getRepository('EdcomsCMSContentBundle:ContentType');
        $this->ContentType = $contentTypes->findOneBy(['name'=>'System Symlink']);
        
        if (!$this->ContentType) {
            $this->ContentType = new ContentType();
            $this->ContentType->setDescription('The Symlink System Content Type');
            $this->ContentType->setName('System Symlink');
            $this->ContentType->setShowChildren(false);
            $this->ContentType->setIsChild(false);
            $customField = new CustomFields();
            $customField->setFieldType(CustomFields::TYPE_CONTENTSELECTOR);
            $customField->setLabel('Content');
            $customField->setName('content');
            $customField->setDescription('Content Selector');
            $customField->setOptions('single');
            $customField->setRequired(true);
            $this->ContentType->addCustomField($customField);
            $this->em->persist($this->ContentType);
            $this->em->flush();
        }
    }
    /**
     * @return ContentType Symlink Content Type
     */
    public function GetContentType()
    {
        return $this->ContentType;
    }
    public function ProcessContent(Content $content, Structure $structure, $request, $fields, $user)
    {
        $data = $request['ContentCreate']['structure'];
        $structures = $this->em->getRepository('EdcomsCMSContentBundle:Structure');
        $data['parent'] = $structures->find($data['parent']);
        $data['master'] = $structures->find($request['ContentCreate']['content']);
        $data['title'] = $request['ContentCreate']['title'];
        $data['deleted'] = false;
        $structure->fromArray($data);
        if (!is_null($structure->getId())) {
            if ($structure->getId() === -1) {
                $structure->setId(null);
                
//                $content->setContentType($this->ContentType);
//                $content->setAddedOn(new \DateTime());
//                $content->setAddedUser($user);
//                $content->setStatus($request['ContentCreate']['status']);
//                $content->setTitle($data['title']);
                $structure->setAddedOn($content->getAddedOn());
            }
            $content->setStructure($structure);
            $customFields = $this->ContentType->getCustomFields();
            foreach ($customFields as $customField) {
                $customFieldData = new CustomFieldData();
                $customFieldData->setCustomFields($customField);
                $customFieldData->setAddedOn($content->getAddedOn());
                $customFieldData->setAddedUser($user);
                $customFieldData->setContent($content);
                $customFieldData->setValue($request['ContentCreate'][$customField->getName()]);
                $content->addCustomFieldData($customFieldData);
            }
            $this->em->persist($content);
            $this->em->flush();
            $status = 200;
            $data = ['status'=>1, 'data'=>$structure->toJSON()];
        } else {
            $status = 400;
            $data = ['status'=>0, 'errors'=>'Incorrect_Structure'];
        }
        return ['data'=>$data, 'status'=>$status];
    }
}
