<?php

namespace EdcomsCMS\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use EdcomsCMS\ContentBundle\Entity\UserGeneratedContentForm;
use EdcomsCMS\ContentBundle\Entity\UserGeneratedContentEntry;
use EdcomsCMS\ContentBundle\Entity\UserGeneratedContentValues;
use EdcomsCMS\ContentBundle\Entity\FormBuilderElements;
use EdcomsCMS\ContentBundle\Entity\FormBuilderElementTypes;

use EdcomsCMS\ContentBundle\Entity\Structure;
use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\CustomFields;
use EdcomsCMS\ContentBundle\Entity\CustomFieldData;

use EdcomsCMS\ContentBundle\Entity\Media;

use EdcomsCMS\ContentBundle\Form\Content\ContentCreate;
use EdcomsCMS\ContentBundle\Form\UserGeneratedContent\UserGeneratedContentFormCreate;

class UserGeneratedContentController extends Controller
{
    /**
     * 
     * @Route("/cms/user-generated-content", name="user_generated_content")
     */
    public function indexAction()
    {
        return $this->render(
            'EdcomsCMSTemplatesBundle:UserGeneratedContent:index.html.twig',
            [
                'title'=>'User Generated Content'
            ]
        );
    }
    /**
     * 
     * @Route("/cms/user-generated-content/update/{id}", defaults={"id"=-1})
     * @Method({"GET", "POST"})
     * @param integer $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAction($id, Request $request)
    {
        $id = (int)$id;
        if ($id === -1) {
            $userGeneratedContentForm = new UserGeneratedContentForm();
            
        } else {
            $userGeneratedContentForms = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:UserGeneratedContentForm');
            $userGeneratedContentForm = $userGeneratedContentForms->find($id);
            if (!$userGeneratedContentForm) {
                return new JsonResponse(['errors'=>'form_not_found'], 404);
            }
        }
        return $this->processForm($userGeneratedContentForm, $request);
    }
    
    /**
     * @Route("/cms/user-generated-content/get")
     * @return JsonResponse
     */
    public function getAction() {
        $userGeneratedContentForms = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:UserGeneratedContentForm');
        $userGeneratedContentFormList = $userGeneratedContentForms->findAll();
        $userGeneratedContentFormJSON = [];
        foreach ($userGeneratedContentFormList as $userGeneratedContentForm) {
            $userGeneratedContentFormJSON[] = $userGeneratedContentForm->toJSON();
        }
        return new JsonResponse(['user_generated_content_forms'=>$userGeneratedContentFormJSON]);
    }
    private function processForm(UserGeneratedContentForm $userGeneratedContentForm, $request) {
        $form = $this->createForm(UserGeneratedContentFormCreate::class, $userGeneratedContentForm);
        $resp = new JsonResponse([]);
        if ($request->isMethod('POST')) {
            $jsondata = json_decode($request->getContent(), true);
            $request->request->replace($jsondata);
            if ($form->get('id')->getData() !== $userGeneratedContentForm->getId()) {
                $data = ['status'=>0, 'errors'=>'ID_mismatch'];
                $status = 400;
            }
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager('edcoms_cms');
                $em->persist($userGeneratedContentForm);
                $em->flush();
                $data = ['status'=>1, 'data'=>$userGeneratedContentForm->toJSON()];
                $status = 200;
            } else {
                $data = ['errors'=>$this->get('form_errors')->getArray($form), 'status'=>0];
                $status = 400;
            }
            $resp = new JsonResponse($data, $status);
        } else {
            $listings = $this->get('EdcomsCMSLists');
            $groupList = $listings->getList('EdcomsCMSAuthBundle:cmsUserGroups');
            $contentList = $listings->getList('EdcomsCMSContentBundle:Content', ['id', 'title', 'structureID'], ['structure'=>'ASC', 'id'=>'ASC'], ['version'=>'getStructure']);
            $contentTypeList = $listings->getList('EdcomsCMSContentBundle:ContentType', ['id', 'name']);
            $structureList = $listings->getList('EdcomsCMSContentBundle:Structure', ['id', 'title'], ['title'=>'ASC'],[]);
            
            $elems = $form->all();
            $required = [];
            foreach ($elems as $elem) {
                if ($elem->isRequired()) {
                    $required[$elem->getName()] = true;
                }
            }
            $csrf = $this->get('security.csrf.token_manager');
            $token = $csrf->refreshToken('UserGeneratedContentFormCreate');
            
            $resp = new JsonResponse([
                'data'=>[
                    'user_generated_content_form'=>(!is_null($userGeneratedContentForm)) ? $userGeneratedContentForm->toJSON() : null,
                    'required'=>$required,
                    'groups'=>$groupList,
                    'content'=>$contentList,
                    'contentTypes'=>$contentTypeList,
                    'structure'=>$structureList
                ],
                'token'=>$token->__toString()
            ], 200);
//            return $this->render('EdcomsCMSTemplatesBundle:test:form.html.twig',
//                    [
//                        'form'=>$form->createView()
//                    ]);
        }
        return $resp;
    }
    
    /**
     * Finds and presents a summary list of the form with the ID of '$formID' along with all child entries.
     * Returns a status of 1 if the form is found and 0 if otherwise.
     * 
     * @Route("/cms/user-generated-content/list/{formID}/{status}", defaults={"status"=null})
     * @param   integer         $formID     ID of the UGCForm to list the child entries from.
     * @param   string          $status     If not null, only return child entries with the status set as '$status'.
     * @return  JsonResponse                Form data complete with child entries.
     */
    public function listAction($formID, $status = null)
    {
        $jsonResponse = ['status' => 0];
        
        // find form.
        $formRepository = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:UserGeneratedContentForm');
        $form = $formRepository->find($formID);
        
        if ($form !== null) {
            // form exists, so return good status.
            $jsonResponse['status'] = 1;
            $jsonResponse['editable'] = $form->getEntriesVisible();
            
            // find entries in found form.
            $entries = $formRepository->findFormWithEntriesList($formID, $status);
            $jsonResponse['user_generated_content_entries'] = $entries;
        }
        
        // return data.
        return new JsonResponse($jsonResponse);
    }
    
    /**
     * @Route("/cms/user-generated-content/entry/{entryID}")
     * @Method({"GET", "POST"})
     * @param integer $entryID
     * @param Request $request
     * @return JsonResponse
     */
    public function entryAction($entryID, Request $request)
    {
        $entries = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:UserGeneratedContentEntry');
        $entry = $entries->findAllValuesByEntry($entryID);
        if (!$entry) {
            return new JsonResponse(['errors'=>'entry_not_found'], 404);
        }
        return $this->processEntryForm($entry, $request);
    }
    
    /**
     * @Route("/cms/user-generated-content/entry/status/{entryID}")
     * @param integer $entryID
     * @param Request $request
     * @return JsonResponse
     */
    public function statusAction($entryID, Request $request)
    {
        if ($request->isMethod('POST')) {
            $jsondata = json_decode($request->getContent(), true);
            $request->request->replace($jsondata);
            $status = $request->get('status');
            $entries = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:UserGeneratedContentEntry');
            $entry = $entries->find($entryID);
            if (!$entry) {
                return new JsonResponse(['errors'=>'entry_not_found'], 404);
            }
            
            // find an existing content item - if exists, set the status to match \\
            $contents = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Content');
//          $content = $contents->findOneByEntry($entry); This throw an error
            $content = $contents->findOneById($entry);

            $entry->setStatus($status);
            $em = $this->getDoctrine()->getManager('edcoms_cms');
            
            if ($content) {
                $content->setStatus($status);
                $em->persist($content);
            }
            $em->persist($entry);
            $em->flush();
            return new JsonResponse(['status'=>1]);
        }
        return new JsonResponse(['status'=>0, 'error'=>'not_post']);
    }
    
    private function processEntryForm(UserGeneratedContentEntry $entry, Request $request)
    {
        $user = (!is_null($entry->getUser())) ? $entry->getUser() : $this->get('security.token_storage')->getToken()->getUser();
        
        if ($entry->getUserGeneratedContentForm()->getEntriesVisible()) {
            return $this->processEntryContentForm($entry, $request, $user);
        } else {
            return $this->processEntryNormal($entry, $request, $user);
        }
    }
    
    private function processEntryContentForm(UserGeneratedContentEntry $entry, Request $request, \EdcomsCMS\AuthBundle\Entity\cmsUsers $user)
    {
        $contents = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Content');
        $content_exists = $contents->findByEntry($entry);
        
        $content_type = $entry->getUserGeneratedContentForm()->getEntryContentType();
        $form_fields = $content_type->getCustomFields();
        $custom_fields = [];
        foreach ($form_fields as $form_field) {
            $custom_fields[$form_field->getName()] = $form_field;
        }
        if (!$content_exists) {
            $content = new Content();
            if (!$request->isMethod('POST')) {
                // get it from the form values.
                $ugcValues = $this->getValuesWithMediaLinksFromEntry($entry);
                
                foreach ($ugcValues as $ugcValue) {
                    $field = $ugcValue['field'];
                    $value = $ugcValue['value'];
                    
                    // need to map each value to the content type \\
                    if (isset($custom_fields[$field])) {
                        $customField = $custom_fields[$field];
                        $customFieldType = $customField->getFieldType();
                        
                        if (isset($ugcValue['medias'])) {
                            $value = json_encode($ugcValue['medias']);
                        }
                        $custom_field_data = new CustomFieldData();
                        $custom_field_data->setCustomFields($customField);
                        $custom_field_data->setValue($value);
                        $custom_field_data->setAddedOn($entry->getDate());
                        $custom_field_data->setAddedUser($user);
                        $content->addCustomFieldData($custom_field_data);
                    }
                }
                
                // need to set the entryID \\
                $entryID = new CustomFieldData();
                $entryID->setCustomFields($custom_fields['entryID']);
                $entryID->setValue($entry->getId());
                $entryID->setAddedOn($entry->getDate());
                $entryID->setAddedUser($user);
                $content->addCustomFieldData($entryID);
                $content->setAddedUser($user);

                $content->setStatus($entry->getStatus());
                $content->setAddedOn($entry->getDate());
                $content->setContentType($entry->getUserGeneratedContentForm()->getEntryContentType());
                $content->setTitle($entry->getTitle());

                $structure = new Structure();
                $structure->setParent($entry->getUserGeneratedContentForm()->getEntriesParent());
                $structure->setTitle($entry->getTitle());
                $content->setStructure($structure);
            }
        } else {
            // clone it \\
            $content = clone $content_exists[0];
        }
                
        $fields = [];
        foreach ($form_fields as $field) {
            $fields[$field->getId()] = $field->toJSON();
        }
        $form = $this->createForm(ContentCreate::class, $content, ['fields'=>$fields, 'ContentType'=>$content_type]);
        if ($request->isMethod('POST')) {
            $jsondata = json_decode($request->getContent(), true);
            $request->request->replace($jsondata);
            if ($form->get('id')->getData() !== $content->getId()) {
                $data = ['status'=>0, 'errors'=>'ID_mismatch'];
                $status = 400;
            }
            $form->handleRequest($request);
            if ($form->isValid()) {
                $content->setTitle($request->get('ContentCreate')['title']);
                $content->setAddedUser($user);
                $content->setAddedOn(new \DateTime());
                $content->setContentType($content_type);
                $content->getStructure()->setTitle($content->getTitle());
                
                $structure = $content->getStructure();
                
                // form work \\
                $ugc = $this->get('EdcomsCMSUGC');
                $ugc->associateForm($content);
                $entry->setStatus($content->getStatus());
                $entry->setTitle($content->getTitle());
                if ($content->getStatus() === 'published') {
                    // need to update the current live version to make it previous if the new status is live \\
                    $this->updateStatus($structure);
                }
                $this->processFields($request->request->all(), $fields, $content);
                // always create a new item for versioning (but not a new structure) \\
                $em = $this->getDoctrine()->getManager('edcoms_cms');
                $em->persist($content);
                $em->persist($entry);
                $em->flush();
                
                $data = ['status'=>1, 'data'=>$content->toJSON()];
                $status = 200;
            } else {
                $data = ['errors'=>$this->get('form_errors')->getArray($form), 'status'=>0];
                $status = 400;
            }
            $resp = new JsonResponse($data, $status);
        } else {
            // need to get the info for the content type \\
            
            $custom_data = $content->getCustomFieldData();
            $fields_data = [];
            foreach ($custom_data as $field_data) {
                $fields_data[$field_data->getCustomFields()->getId()] = $field_data->getValue();
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
            $data = [
                'data'=>[
                    'content'=>$content->toJSON(['id', 'title', 'status', 'templateFile', 'contentType', 'structure']),
                    'fields'=>$fields,
                    'template_files'=>$template_files,
                    'field_data'=>$fields_data,
                    'required'=>$required
                ],
                'editable'=>true,
                'token'=>$token->__toString(),
                'status'=>1
            ];
            //return new Response($token);
//            return $this->render('EdcomsCMSTemplatesBundle:test:form.html.twig',
//                    [
//                        'form'=>$form->createView()
//                    ]);
            $resp = new JsonResponse($data, $status);
        }
        return $resp;
    }
    
    private function processEntryNormal(UserGeneratedContentEntry $entry)
    {
        $ugcValues = $this->getValuesWithMediaLinksFromEntry($entry);
        
        $status = 200;
        $data = [
            'data' => $ugcValues,
            'editable' => false,
            'status' => 1
        ];
        
        $resp = new JsonResponse($data, $status);
        return $resp;
    }
    
    /**
     * Retrieves all of the fields from '$entry' and compiles them along with their values into an array.
     * If the field is a type where Media is concerned, the media path is fetched and is then added to the field array.
     * 
     * @param   UserGeneratedContentEntry $entry    Entry object to retrieve values from.
     * 
     * @return  array                               Compiled list of fields with values and media paths if present.
     */
    private function getValuesWithMediaLinksFromEntry(UserGeneratedContentEntry $entry)
    {
        // get the fields from the parent form of '$entry'.
        $form = $entry->getUserGeneratedContentForm();
        $ugc = $this->get('EdcomsCMSUGC');
        $ugc->loadFormDirectly($form, $this->get('kernel')->getRootDir(), $this->getUser());
        $fields = (array)$ugc->getInfo()['ugc_fields'];
        
        // set up the arrays.
        // we'll use '$media' to initially store the IDs to fetch,
        // then replace the value with the fetched Media URL.
        $media = [];
        $ugcValuesResult = [];
        
        // fetch the UGC values and iterate through them.
        $ugcValues = $entry->getUserGeneratedContentValues();
        
        foreach ($ugcValues as $item) {
            $itemJSON = (array)$item->toJSON();
            
            // if the field is a 'file_array', store the ID to fetch later.
            if (isset($fields[$itemJSON['field']])) {
                $fieldType = $fields[$itemJSON['field']]->type;
                
                if ($fieldType === 'file_array' || $fieldType === 'file') {
                    $value = json_decode($itemJSON['value'], true);
                    $itemJSON['medias'] = [];
                    
                    foreach ($value as $mediaID) {
                        // we're using pointers so that when we change the value to the media URL later,
                        // we won't then have to re-iterate through this array again.
                        $mediaValue = null;
                        $media[$mediaID] = &$mediaValue;
                        $itemJSON['medias']["$mediaID"] = &$mediaValue;
                        
                        // remove pointer so that we don't end up pointing to the same thing.
                        unset($mediaValue);
                    }
                }
            }
            
            $ugcValuesResult[] = $itemJSON;
        }
        // need to get the info for the content type \\

        // fetch all of the medias referened in the values.
        if (!empty($media)) {
            $mediaRepository = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Media');
            $medias = $mediaRepository->findBy(['id' => array_keys($media)]);
                        
            foreach ($medias as &$foundMedia) {
                $mediaID = $foundMedia->getId();
                $mediaTitle = $foundMedia->getTitle();
                
                $mediaRecord = [
                    'path' => $this->generateUrl('media_view', ['file' => $foundMedia->getPath() .'/'. $mediaTitle]),
                    'title' => $mediaTitle
                ];
                
                // by changing the value via the pointer,
                // we don't have to iterate through the '$ugcValues' array again.
                $mediaPointer = &$media["$mediaID"];
                $mediaPointer = $mediaRecord;
            }
        }
        
        return $ugcValuesResult;
    }
    
    private function updateStatus(Structure $structure)
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
        foreach ($fields as $field) {
            if (isset($data['ContentCreate'][$field['name']])) {
                $custom_data = new CustomFieldData();
                $custom_data->setAddedOn(new \DateTime());
                $custom_data->setAddedUser($content->getAddedUser());
                $custom_data->setCustomFields($custom_fields[$field['name']]);
                $custom_data->setValue((is_array($data['ContentCreate'][$field['name']])) ? json_encode($data['ContentCreate'][$field['name']]) : $data['ContentCreate'][$field['name']]);
                $custom_data->setContent($content);
                $content->addCustomFieldData($custom_data);
            }
        }
    }
}
