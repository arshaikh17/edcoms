<?php

namespace EdcomsCMS\ContentBundle\Controller;

use EdcomsCMS\ContentBundle\Helpers\ContentHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\ContentType;
use EdcomsCMS\ContentBundle\Entity\CustomFields;
use EdcomsCMS\ContentBundle\Entity\CustomFieldData;

use EdcomsCMS\ContentBundle\Helpers\SymlinkHelper;

use EdcomsCMS\ContentBundle\Form\Content\ContentCreate;
use EdcomsCMS\ContentBundle\Form\Content\ContentTypeCreate;
use EdcomsCMS\ContentBundle\Form\Content\SymlinkCreate;

class ContentController extends Controller
{
    const STATUS_ERROR = 0;
    const STATUS_OK = 1;
    const STATUS_CONSTRAINT_ERROR = 2;

    public function indexAction()
    {
        return $this->render(
            'EdcomsCMSTemplatesBundle:Content:index.html.twig',
            [
                'title'=>'Content',
            ]
        );
    }
    public function updateAction($id, $content_typeID, Request $request)
    {
        $id = (int)$id;
        $content_typeID = (int)$content_typeID;
        $content_types = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:ContentType');
        $content_type = $content_types->find($content_typeID);
        if ($id === -1) {
            $content = new Content();
            if ($content_typeID === -1) {
                // means we can't continue as a content type is required \\
                return new JsonResponse(['errors'=>'content_type_required'], 404);
            }
            if ($content_typeID === -2) {
                // this is a symlink \\
                $symlink = $this->get('EdcomsCMSSymlinks');
                $content_type = $symlink->GetContentType();
            }
        } else {
            $contents = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Content');
            $content = $contents->find($id);
            if (!$content) {
                return new JsonResponse(['errors'=>'content_not_found'], 404);
            }
            if ($content_typeID === -1) {
                $content_type = $content->getContentType();
            } else {
                $content->setContentType($content_type);
            }
        }


        if (!$content_type) {
            return new JsonResponse(['errors'=>'content_type_not_found'], 404);
        }
        return $this->processForm($content, $content_type, $request);
    }
    public function UpdateFieldAction(Request $request, $type, $id)
    {
        $contents = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Content');
        $content = $contents->find($id);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $updated = false;
        switch ($type) {
            case 'status':
                $content->setStatus($request->get('value'));
                $updated = true;
                break;
            case 'approve':
                $content->setStatus('published');
                $content->setApprovedOn(new \DateTime());
                $content->setApprovedUser($user);
                $updated = true;
                break;
        }
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $em->persist($content);
        $em->flush();
        return new JsonResponse(['status'=>$updated]);
    }
    public function getAction($structure) {
        $structures = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure');
        $structureEntity = $structures->findOneBy(['id'=>$structure,'deleted'=>false]);
        $contents = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Content');
        $content = $contents->findBy(['structure'=>$structureEntity], ['addedOn'=>'DESC']);

        $contentTypes = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:ContentType');
        $contentType = $contentTypes->findAll();
        if ($contentType) {
            foreach ($contentType as $type) {
                $typeArr[] = $type->toJSON();
            }
        }
        if ($content) {
            // this means multiple were found so we want to choose them from a list \\
            $contArr = [];
            foreach ($content as $contentItem) {
                $contArr[] = $contentItem->toJSON(['id', 'addedUser', 'approvedUser', 'title', 'contentType', 'addedOn', 'approvedOn']);
            }
            $resp = new JsonResponse(['data'=>$contArr, 'content_types'=>$typeArr, 'structure'=>$structureEntity->toJSON(['id', 'link', 'parent', 'priority', 'title', 'content', 'addedOn', 'children', 'master'])]);
        } else {
            $resp = new JsonResponse(['success'=>false, 'message'=>'Item not found']);
        }
        return $resp;
    }

    // content types \\
    public function type_indexAction()
    {
        return $this->render(
            'EdcomsCMSTemplatesBundle:Content:type_index.html.twig',
            [
                'title'=>'Content Type',
            ]
        );
    }

    public function type_getAction() {
        $contentTypes = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:ContentType');
        $contentTypeList = $contentTypes->findByNotSystem();
        $contentTypeJSON = [];
        foreach ($contentTypeList as $contentType) {
            $contentTypeJSON[] = $contentType->toJSON();
        }
        // also get the SYMLINK content type (this is a SYSTEM content type) \\
        $symlink = $this->get('EdcomsCMSSymlinks');
        $symlinkCT = $symlink->GetContentType();
        $contentTypeJSON[] = $symlinkCT->toJSON();

        //prep any custom fields for display
        $contentHelper = new ContentHelper(
            $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Content'),
            $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure'),
            $this->getDoctrine(),
            //this would be a user entity but as it's not required for this use case it's not passed
            //be careful if using this instance of the content helper in the future
            null
        );
        foreach ($contentTypeJSON as &$contentType) {
            $contentType['custom_fields'] = $contentHelper->prepareCustomFieldsForDisplay($contentType['custom_fields']);
        }
        return new JsonResponse(['content_types'=>$contentTypeJSON]);
    }

    public function type_updateAction($id, Request $request) {
        $id = (int)$id;
        if ($id === -1) {
            $content_type = new ContentType();
        } else {
            $content_types = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:ContentType');
            $content_type = $content_types->find($id);
        }
        return $this->processTypeForm($content_type, $request);
    }

    private function processForm($content_exist, ContentType $content_type, $request) {
        $accessControl = $this->get('AccessControl');
        $content = clone $content_exist;
        $form_fields = $content_type->getCustomFields();
        $fields = [];
        $tempFields= [];
        $subFields = [];
        foreach ($form_fields as $field) {
            if (!$field->getAdminOnly() || ($accessControl->has_group('cms_admin') && $field->getAdminOnly())) {
                $tempFields[] = $field->toJSON();
//                if (!is_null($field->getParent())) {
//                    //if field has a parent add to sub fields
//                    $subFields[$field->getParent()->getId()][] = $field->toJSON();
//                } else {
//                    //else add to fields
//                    $fields[$field->getId()] = $field->toJSON();
//                }
            }
        }

        $contentHelper = $this->contentHelper = new ContentHelper(
            $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Content'),
            $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure'),
            $this->getDoctrine(),
            //this would be a user entity but as it's not required for this use case it's not passed
            //be careful if using this instance of the content helper in the future
            null
        );

        $tempFields = $contentHelper->prepareCustomFieldsForDisplay($tempFields);
        foreach ($tempFields as $fld){
            $fields[$fld['id']] = $fld;
            $childrenArray = [];
            foreach ($fld['children'] as $child){
                $childrenArray[$child->getId()] = $child;
            }
        }

        //update any sub fields
//        if (count($subFields)>0) {
//            foreach ($subFields as $k => $subField) {
//                if (in_array($k, array_keys($fields))) {//subfields and fields are keyed with the same id
//                    $fields[$k]['subfields'] = $subField;
//                }
//            }
//        }

        // get the symlink system content type \\
        $symlink = $this->get('EdcomsCMSSymlinks');
        $symlinkCT = $symlink->GetContentType();

        $isSystem = false;

        if ($symlinkCT->getId() === $content_type->getId()) {
            // this is a symlink \\
            $form = $this->createForm(SymlinkCreate::class, $content, ['fields'=>$fields, 'ContentType'=>$content_type]);
            $isSystem = true;
            $type = 'symlink';
            $formname = 'SymlinkCreate';
        } else {
            $form = $this->createForm(ContentCreate::class, $content, ['fields'=>$fields, 'ContentType'=>$content_type]);
            $formname = 'ContentCreate';
        }
        if ($request->isMethod('POST')) {
            $jsondata = json_decode($request->getContent(), true);

            $request->request->replace($jsondata);
            if (!$isSystem && $form->get('id')->getData() !== $content->getId()) {
                $data = ['status'=>self::STATUS_ERROR, 'errors'=>'ID_mismatch'];
                $status = 400;
            } else {
                $form->handleRequest($request);
                $tmpdata = $request->request->all();
                if ($form->isValid()) {
                    $user = $this->get('security.token_storage')->getToken()->getUser();
                    $content->setAddedUser($user);
                    $content->setAddedOn(new \DateTime());
                    $content->setContentType($content_type);
                    $content->getStructure()->setTitle($content->getTitle());

                    $structure = $content->getStructure();
                    $structure->setDeleted(0);
                    if (is_null($structure->getAddedOn())) {
                        $structure->setAddedOn(new \DateTime());
                    }
                    if ($isSystem) {
                        switch ($type) {
                            case 'symlink':
                                if ($content->getStatus() === 'published') {
                                    // need to update the current live version to make it previous if the new status is live \\
                                    $this->updateStatus($structure);
                                }
                                // return an array of ['data'=>(array), 'status'=>(int)] \\
                                extract($symlink->ProcessContent($content, $structure, $request->request->all(), $fields, $user));
                                break;
                        }
                    } else {

                        // form work \\
                        $ugc = $this->get('EdcomsCMSUGC');
                        $ugc->associateForm($content);
                        if ($content->getStatus() === 'published') {
                            // need to update the current live version to make it previous if the new status is live \\
                            $this->updateStatus($structure);
                        }
                        $this->processFields($request->request->all(), $fields, $content);
                        // always create a new item for versioning (but not a new structure) \\
                        $em = $this->getDoctrine()->getManager('edcoms_cms');
                        $em->persist($content);
                        $em->flush();

                        $data = ['status'=>self::STATUS_OK, 'data'=>$content->toJSON()];
                        $status = 200;
                    }
                } else {
                    // TODO: uncomment line below to use constant value for error status.
                    // $data = ['errors' => $this->get('form_errors')->getArray($form), 'status' => self::STATUS_ERROR];
                    $data = ['errors'=>$this->get('form_errors')->getArray($form), 'status'=>0];
                    $status = 400;
                }
            }
            $resp = new JsonResponse($data, $status);
        } else {
            // need to get the info for the content type \\

            $customData = $content->getCustomFieldData()->toArray();
            $fields_data_return = [];
            if (count($customData) > 0) {
                //collect the subfields data keyed by parent id
                $subFieldData = [];
                $fieldsData = [];
                foreach ($customData as $fieldData) {
//                    if (!is_null($fieldData->getParent())) {
//                        $subFieldData[$fieldData->getParent()->getId()][$fieldData->getCustomFields()->getId()] = $fieldData->getValue();
//                    } else {
//                        $fieldsData[] = $fieldData;
//                    }
                    if (is_null($fieldData->getParent())) {
                        $fieldsData[] = $fieldData;
                    }
                }

                //add subfields to parent if any
//                if (count($subFieldData) > 0) {
//                    foreach ($fieldsData as &$fieldData) {
//                        if (in_array($fieldData->getId(), array_keys($subFieldData))) {
//                            $fieldData->subfieldData = $subFieldData[$fieldData->getId()];
//                        }
//                    }
//                }


                // Format FieldData structure
                // Loop through all CustomFieldData (except the nested ones)
                foreach ($fieldsData as $fieldData) {
                    /** @var CustomFieldData $fieldData */
                    if (!$fieldData->getCustomFields()->getAdminOnly() || ($accessControl->has_group('cms_admin') && $fieldData->getCustomFields()->getAdminOnly())) {

                        $customFieldId = $fieldData->getCustomFields()->getId();

                        // Check whether it is a Repeatable field..
                        if ($fieldData->getCustomFields()->getRepeatable()) {
                            //  .. and whether the corresponding key exists in the returned data
                            if(!isset($fields_data_return[$customFieldId])){
                                // If it doesn't exist, create an array
                                $fields_data_return[$customFieldId] = [];
                            }

                            // Check whether the Repeatable field has subfields ()
                            if($fieldData->getCustomFields()->getFieldType()!="group"){
                                // If it doesn't have subfields, just append its value
                                $fields_data_return[$customFieldId][] = $fieldData->getValue();
                            }else{
                                // If it does have children, we need to group all the children data in an array.
                                // One array per Repeatable block
                                $repeatedGroupFieldData = [];
                                foreach ( $fieldData->getChildren() as $repeatedGroupFieldChild){
                                    $childCustomFieldId = $repeatedGroupFieldChild->getCustomFields()->getId();
                                    if($repeatedGroupFieldChild->getCustomFields()->getRepeatable()){
                                        // Below we handle a Repeatable child - meaning a subfield that it is Repeatable
                                        if(!isset($repeatedGroupFieldData[$childCustomFieldId])){
                                            $repeatedGroupFieldData[$childCustomFieldId] = [];
                                        }
                                        $repeatedGroupFieldData[$childCustomFieldId][] = $repeatedGroupFieldChild->getValue();
                                    }else{
                                        // Below we handle a non-Repeatable child.
                                        $repeatedGroupFieldData[$childCustomFieldId] = $repeatedGroupFieldChild->getValue();
                                    }
                                }
                                // Append the children data in the corresponding Parent CustomField data
                                $fields_data_return[$customFieldId][] = $repeatedGroupFieldData;
                            }
                        } else {
                            // Handle any non-repeatable field
                            $fields_data_return[$customFieldId] = $fieldData->getCustomFields()->getFieldType()=="group" ? [] : $fieldData->getValue();
                            $parentKey = $fieldData->getCustomFields()->getId();
                            // Check for subfields
                            if ($fieldData->getChildren()->count()>0) {

                                foreach ($fieldData->getChildren() as $child){
                                    // If subfield is Repeatable
                                    if($child->getCustomFields()->getRepeatable()){
                                        $key = $child->getCustomFields()->getId();
                                        if(!isset($fields_data_return[$parentKey][$key])){
                                            $fields_data_return[$parentKey][$key] = [];
                                        }
                                        $fields_data_return[$parentKey][$key][] = $child->getValue();
                                    }else{
                                        $data = $contentHelper->prepareCustomFieldsDataForDisplay([$child]);
                                        if(!isset($fields_data_return[$parentKey]) || !is_array($fields_data_return[$parentKey])){
                                            $fields_data_return[$parentKey] = [];
                                        }
                                        foreach ($data as $key=>$d){
                                            $fields_data_return[$parentKey][$key] = $d;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // get the template files for the chosen content type \\
            $templateFiles = $content_type->getTemplateFiles();
            $template_files = [];
            foreach ($templateFiles as $file) {
                $template_files[] = $file->toJSON();
            }

            $elems = $form->all();
            $required = [];
            foreach ($elems as $elem) {
                if ($elem->isRequired()) {
                    $required[$elem->getName()] = true;
                }
            }

            $csrf = $this->get('security.csrf.token_manager');
            $token = $csrf->refreshToken('ContentCreate');
            $status = 200;

            $editable = false;
            // if it's a new piece of content, or a user with cms_admin group or a user with content_type set to true \\
            if ($accessControl->has_group('cms_admin') || $accessControl->has_permission('content', 'content_type') || is_null($content->getId())) {
                $editable = true;
            }

            //update content here to add 'subfields'
            $contentJSON = $content->toJSON(['id', 'title', 'status', 'templateFile', 'contentType']);
            if (is_array($contentJSON['contentType']) && $contentJSON['contentType']>0) {
                if (is_array($contentJSON['contentType']['custom_fields']) && $contentJSON['contentType']['custom_fields']>0) {
//                    $contentHelper = new ContentHelper(
//                        $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Content'),
//                        $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure'),
//                        $this->getDoctrine(),
//                        //this would be a user entity but as it's not required for this use case it's not passed
//                        //be careful if using this instance of the content helper in the future
//                        null
//                    );
                    $contentJSON['contentType']['custom_fields'] = $contentHelper->prepareCustomFieldsForDisplay($contentJSON['contentType']['custom_fields']);
                }
            }

            //do 'fields' need to be updated with subfields?

            //update fielddata to better represent the grouped fields

            $data = [
                'content'=>$contentJSON,
                'fields'=>$fields,
                'template_files'=>$template_files,
                'field_data'=>$fields_data_return,
                'required'=>$required,
                'token'=>$token->__toString(),
                'content_type_editable'=>$editable,
                'is_system_content_type'=>$isSystem,
                'content_type'=>$content_type->getName(),
            ];
            
            // send the master Structure object as well as joining the master Structure object if symlinked.
            if (isset($type) && $type === 'symlink') {
                $structure = $content->getStructure();
                $structureJSON = null;
                
                if ($structure !== null) {
                    $structureJSON = $structure->toJSON(['id']);
                    $structureJSON['master'] = null;
                    
                    $masterStructure = $structure->getMaster();
                    
                    // only progress if a master Structure object exists and is not marked as deleted.
                    if (isset($masterStructure) && !$masterStructure->getDeleted()) {
                        // fetch a limited amount of properties as we want to send as skinny response as possible.
                        $masterStructureJSON = $masterStructure->toJSON(['id', 'title', 'link', 'priority']);
                        $masterContent = $masterStructure->getContent()->last();
                        
                        // only progress if the master Structure object holds at least one Content object.
                        if (isset($masterContent)) {
                            // fetch a limited amount of properties as we want to send as skinny response as possible.
                            // send back the last Content object along with it's ContentType as the IDs will be used by the front end to generate URLs.
                            $masterStructureJSON['last_content'] = $masterContent->toJSON(['id', 'title', 'status']);
                            $masterStructureJSON['last_content']['contentType'] = $masterContent->getContentType()->toJSON(['id']);
                            
                            $structureJSON['master'] = $masterStructureJSON;
                        }
                    }
                }
                
                $data['structure'] = $structureJSON;
            }
            
            //return new Response($token);
//            return $this->render('EdcomsCMSTemplatesBundle:test:form.html.twig',
//                    [
//                        'form'=>$form->createView()
//                    ]);
            $resp = new JsonResponse($data, $status);
        }
        return $resp;
    }

    private function processTypeForm($contentType, $request) {
        $form = $this->createForm(ContentTypeCreate::class, $contentType);
        $resp = new JsonResponse([]);
        if ($request->isMethod('POST')) {
            $jsondata = json_decode($request->getContent(), true);
            //check custom fields for any subfields if there are subfields pull out
            //and add to fields array in their own right
//            if (isset($jsondata['ContentTypeCreate']['custom_fields']) && count($jsondata['ContentTypeCreate']['custom_fields'])>0) {
//                foreach ($jsondata['ContentTypeCreate']['custom_fields'] as &$customField) {
//                    if (isset($customField['subfields']) && $customField['subfields']>0) {
//                        $jsondata['ContentTypeCreate']['custom_fields'] = array_merge($jsondata['ContentTypeCreate']['custom_fields'], $customField['subfields']);
//                        unset($customField['subfields']);
//                    }
//                }
//            }
            $request->request->replace($jsondata);
            if ($form->get('id')->getData() !== $contentType->getId()) {
                // TODO: uncomment line below to use constant value for error status.
                //$data = ['status' => self::STATUS_ERROR, 'errors' => 'ID_mismatch'];
                $data = ['status'=>0, 'errors'=>'ID_mismatch'];
                $status = 400;
            }
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager('edcoms_cms');
                $em->persist($contentType);
                
                try {
                    $em->flush();
                    
                    $data = ['status'=>self::STATUS_OK, 'data'=>$contentType->toJSON()];
                    $status = 200;
                } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
                    // take the IDs of the CustomFields that are not being deleted from the ContentType.
                    $customFieldIDs = array_map(function ($customFieldArr) {
                        return $customFieldArr['id'];
                    }, $jsondata['ContentTypeCreate']['custom_fields']);
                    
                    // find all of the IDs of the ContentType's CustomFields that are being used by CustomFieldData records.
                    $constrainedCustomFieldsIDs = [];
                    $constrainedCustomFields = $em->getRepository('EdcomsCMSContentBundle:CustomFields')->findConstrainedCustomFieldsByContentType($contentType);
                    
                    foreach ($constrainedCustomFields as $customField) {
                        $constrainedCustomFieldsIDs[] = $customField->getId();
                    }
                    
                    // get the difference between submitted IDs and constrained IDs,
                    // we are left over with the IDs of the CustomFields that are constrained and have been attempted to be deleted by the user.
                    $erroredIDs = array_diff($constrainedCustomFieldsIDs, $customFieldIDs);
                    
                    // send this data back to the application.
                    $data = [
                        'data' => [
                            'message' => 'Unable to remove field as it is being used.',
                            'constrained_ids' => $erroredIDs,
                        ],
                        'errors' => 'constaint_error',
                        'status' => self::STATUS_ERROR,
                    ];
                    
                    $status = 409; // conflict.
                }
                
            } else {
                $data = ['errors'=>$this->get('form_errors')->getArray($form), 'status'=>self::STATUS_ERROR];
                $status = 400;
            }
            $resp = new JsonResponse($data, $status);
        } else {
            $elems = $form->all();
            $required = [];
            foreach ($elems as $elem) {
                if ($elem->isRequired()) {
                    $required[$elem->getName()] = true;
                }
            }
            $csrf = $this->get('security.csrf.token_manager');
            $token = $csrf->refreshToken('ContentTypeCreate');

            //check contenttype not null
            //then check for customfields, and reformat to handle parent associations
            if (!is_null($contentType)) {
                $contentTypeReturn = $contentType->toJSON();
                if (isset($contentTypeReturn['custom_fields']) && ($contentTypeReturn['custom_fields']>0)) {
                    //find fields that have a parent set and move them to be a sub-field of their parent
                    $contentHelper = new ContentHelper(
                        $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Content'),
                        $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure'),
                        $this->getDoctrine(),
                        //this would be a user entity but as it's not required for this use case it's not passed
                        //be careful if using this instance of the content helper in the future
                        null
                    );
                    $contentTypeReturn['custom_fields'] = $contentHelper->prepareCustomFieldsForDisplay($contentTypeReturn['custom_fields']);
                }
            } else {
                $contentTypeReturn = null;
            }

            $resp = new JsonResponse([
                'data'=>['content_type'=>$contentTypeReturn, 'required'=>$required],
                'token'=>$token->__toString(),
            ], 200);
        }
        return $resp;
    }

    /**
     * Approve a content item
     * @param int $id
     */
    public function approveAction($id) {

    }
    private function updateStatus($structure)
    {
        $contents = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Content');
        $contentList = $contents->findBy(['structure'=>$structure, 'status'=>'published']);
        foreach ($contentList as $content) {
            $content->setStatus('previous');
        }
    }

    private function processFields($data, $fields, &$content) {
        $custom_fieldsRepo = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:CustomFields');
        $custom_fields = $custom_fieldsRepo->findByContentType($content->getContentType());
        // set the current customFieldData to an empty ArrayCollection first \\
        $content->resetCustomFieldData();

        // Loop through every expected field
        foreach ($fields as $field) {
            if (isset($data['ContentCreate'][$field['name']])) {
                // Check if it is a group field.
                if($field['fieldType']=="group"){
                    $rootCustomField = $this->isRootCustomGroupField($field['name'],$custom_fields);
                    if($rootCustomField){
                        // If it is Repeatable
                        if($rootCustomField->getRepeatable()){
                            // The following part handles the case where a CustomField is Repeatable and it is of Group type.
                            // Get submitted groups, ...
                            $repeatableGroupFieldData = $data['ContentCreate'][$field['name']];
                            // ..loop through them and create the appropriate entities
                            foreach ($repeatableGroupFieldData as $repeatableGroup){
                                $this->addGroupFieldToContent($content, $custom_fields[$field['name']], $repeatableGroup);
                            }
                        }else{
                            // if it isn't repeatable but it is of Group Type
                            $this->addGroupFieldToContent($content, $custom_fields[$field['name']], $data['ContentCreate'][$field['name']]);
                        }
                    }
                }else{
                    // Handles CustomField that is neither Repeatable nor of Group Type.
                    $this->addFieldToContent($content, $custom_fields[$field['name']], $data['ContentCreate'][$field['name']]);
                }
            }
        }
    }

    /**
     * @param Content $content
     * @param CustomFields $customFields
     * @param array $value
     */
    private function addGroupFieldToContent(Content $content, CustomFields $customFields, array $value){
        $children = $customFields->getChildren()->count()>0 ? $customFields->getChildren() : false;
        if($children){
            // Create GroupCustomField
            $groupCustomField = $this->addFieldToContent($content, $customFields, '');
            // Create nested CustomFields
            foreach ($children as $child){
                /** @var $child CustomFields */
                $this->addFieldToContent($content, $child, $value[$child->getName()], $groupCustomField);
            }
        }
    }

    /**
     * @param Content $content
     * @param CustomFields $customFields
     * @param $value
     * @param CustomFieldData|null $parent
     * @return CustomFieldData
     */
    private function addFieldToContent(Content $content, CustomFields $customFields, $value, CustomFieldData $parent=null){
        if($customFields->getRepeatable() && is_array($value) && $customFields->getFieldType()!="group"){
            foreach ($value as $repeatableField){
                $this->addFieldToContent($content, $customFields, $repeatableField, $parent);
            }
        }else{
            $newCustomFieldData = new CustomFieldData();
            $newCustomFieldData->setAddedOn(new \DateTime());
            $newCustomFieldData->setAddedUser($content->getAddedUser());
            $newCustomFieldData->setCustomFields($customFields);
            // If it is a Group type field then leave its value empty, otherwise set the submitted value
            $newCustomFieldData->setValue($customFields->getFieldType()=="group" ? '' : $value);
            $newCustomFieldData->setContent($content);
            if($parent){
                $newCustomFieldData->setParent($parent);
                $parent->addChild($newCustomFieldData);
            }
            $content->addCustomFieldData($newCustomFieldData);

            return $newCustomFieldData;
        }

    }

    /**
     * @param $name
     * @param array $customFields
     * @return bool|CustomFields
     */
    private function isRootCustomGroupField($name, array $customFields){
        /** @var CustomFields $field */
        foreach ($customFields as $field){
            if($field->getFieldType()=="group" && $field->getName()==$name){
                return $field;
            }
        }
        return false;
    }

}
