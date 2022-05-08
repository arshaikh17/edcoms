<?php
namespace EdcomsCMS\ContentBundle\Helpers;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

use EdcomsCMS\ContentBundle\Entity\UserGeneratedContentForm;
use EdcomsCMS\ContentBundle\Entity\UserGeneratedContentEntry;
use EdcomsCMS\ContentBundle\Entity\UserGeneratedContentValues;
use EdcomsCMS\ContentBundle\Helpers\UserGeneratedContentValidator;

class UserGeneratedContentHelper {
    private $doctrine;
    private $form;
    private $formOptions;
    private $fields=[];
    private $rootDir;
    private $container;
    private $validator;
    
    public function __construct($doctrine, Container $container)
    {
        $this->doctrine = $doctrine;
        $this->container = $container;
        $this->validator = new UserGeneratedContentValidator();
    }
    
    public function validateEmail($email) {
        $validator = $this->container->get('validator');

        $constraints = array(
            new \Symfony\Component\Validator\Constraints\Email(),
            new \Symfony\Component\Validator\Constraints\NotBlank()
        );
        $valid = $validator->validate(strtolower($email), $constraints);
        if (count($valid) > 0) {
            return false;
        }
        return true;
    }
    public function loadForm($content, $rootDir, $user)
    {
        $forms = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:UserGeneratedContentForm');
        $form = $forms->findOneByContent($content);
        
        $this->loadFormDirectly($form, $rootDir, $user);
    }
    
    public function loadFormDirectly($form, $rootDir, $user)
    {
        $this->rootDir = $rootDir;
        $this->user = $user;
        
        if ($form) {
            $this->form = $form;
            $this->getFields($this->form->getType());
        }
    }
    
    public function getForm()
    {
        if ($this->form) {
            return $this->form;
        }
    }
    /**
     * Take an entryID and get the data for it
     * @param int $entryID
     */
    public function loadData($entryID)
    {
        $entries = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:UserGeneratedContentEntry');
        $entry = $entries->find($entryID);
        if ($entry) {
            return $entry;
        }
        return false;
    }
    public function setForm($form, $rootDir)
    {
        $this->rootDir = $rootDir;
        if ($form) {
            $this->form = $form;
            $this->getFields($this->form->getType());
        }
    }
    
    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param type $em
     * @param type $validator
     * @return boolean
     */
    public function handleForm(\Symfony\Component\HttpFoundation\Request $request, $em, $validator = false)
    {
        $err = 'not_post';
        $resp = ['errors' => $err];
        $notificationStr = '';
        if ($request->isMethod('POST') && $this->form && $this->fields) {
            $posted = [];
            $err = ['fields' => [], 'validatorResp' => true];
            
            if ($validator !== false) {
                $validatorErr = null;
                $validatorClass = $validator['class'];
                $validatorMethod = $validator['method'];
                $validatorController = new $validatorClass();
                $validatorController->setContainer($this->container);
                
                $validatorResp = $validatorController->{$validatorMethod}($request, $validatorErr);
                
                if (!$validatorResp) {
                    $err['validatorErr'] = $validatorErr;
                    $err['validatorResp'] = false;
                }
            }
            
            $entry = new UserGeneratedContentEntry();
            $entry->setUserGeneratedContentForm($this->form);
            $entry->setDate(new \DateTime());
            
            // get the fields to concat for the title \\
            $form_title = explode(',', $this->form->getFormTitle());
            $title = '';
            if (!is_null($this->user)) {
                $entry->setUser($this->user);
            } else {
                $entry->setUser(null);
            }
            foreach ($this->fields as $field=>$info) {
                $val = $request->get($field);
                if ($val) {
                    // validate the submitted value against the defined field type.
                    if ($this->validator->validateField($info->type, $val)) {
                        if (is_array($val)) {
                            $val = json_encode($val);
                        }
                        $values = new UserGeneratedContentValues();
                        $values->setField($field);
                        $values->setValue($val);
                        $values->setEntry($entry);
                        $posted[$field] = $values;
                        if (in_array($field, $form_title)) {
                            $title .= $val.' ';
                        }
                        $notificationStr .= '<p><strong>'.$field.'</strong>: '.$val.'</p>';
                    } else {
                        $err['fields'][$field] = 'validation_failed';
                    }
                } else if (isset($info->required) && $info->required) {
                    $err['fields'][$field] = 'required';
                }
            }
            if (strlen(rtrim($title)) > 250) {
                $title = substr(rtrim($title), 0, 250);
            }
            $entry->setTitle(rtrim($title));
            if (empty($err['fields']) && $err['validatorResp']) {
                if ($this->form->getNotification()) {
                    $emails = explode(',', $this->form->getNotification());
                    $message = \Swift_Message::newInstance()
                        ->setSubject($this->form->getName().' submission: '.$title)
                        ->setFrom($this->container->getParameter('edcoms_cms_content.email'))
                        ->setBody($notificationStr, 'text/html');
                    foreach ($emails as $email) {
                        if ($this->validateEmail($email)) {
                            $emailArr[] = $email;
                        }
                    }
                    $message->setTo($emailArr);
                    $this->container->get('mailer')->send($message);
                }
                // means it worked so lets persist this \\
                $em->persist($entry);
                foreach ($posted as $value) {
                    $em->persist($value);
                }
                
                $em->flush();
                
                // return an array with details of the form handle.
                // 'status' set as 'true' as we were successful handling the form and adding data into the database.
                // 'entry' set as the entry entity object for any additonal handling, should any be required.
                $resp = ['status'=>true, 'entry'=>$entry];
            } else {
                $resp = ['status'=>false, 'errors'=>$err];
            }
        }
        return $resp;
    }
    
    public function getFields($type)
    {
        switch ($type) {
            case "custom":
                $this->fields = $this->loadFields($this->form->getFormBuilderElements());
                break;
            case "meta":
                $formData = $this->loadMeta($this->form->getTemplateFile());
                $this->fields = $formData->fields;
                $this->formOptions = property_exists($formData, 'options') ? $formData->options : new \stdClass();
                break;
        }
    }
    private function loadMeta($templateFile)
    {
        $jsonFile = str_replace('.html.twig', '.json', $templateFile);
        if (file_exists($this->rootDir.'/Resources/views/'.$jsonFile)) {
            $jsonData = json_decode(file_get_contents($this->rootDir.'/Resources/views/'.$jsonFile));
            if ($jsonData) {
                return $jsonData;
            }
        }
    }
    private function loadFields($fields)
    {
        $fieldsArr = [];
        if ($fields->count() > 0) {
            foreach ($fields as $field) {
                $fieldsArr[$field->getName()] = $field->getRequired();
            }
        }
        return $fieldsArr;
    }
    private function processJSON($jsonData)
    {
        $fields = [];
        if (isset($jsonData->fields)) {
            foreach ($jsonData->fields as $field=>$info) {
                $fields[$field] = $info;
            }
        }
        return $fields;
    }
    public function getInfo()
    {
        $info = [];
        
        if ($this->form) {
            $info['ugc_form'] = $this->form->getTemplateFile();
            $info['ugc_fields'] = $this->fields;
            $info['ugc_access'] = $this->form->getGroups();
            $info['ugc_notification'] = $this->form->getNotification();
            
            // default values for optional keys in form options.
            $info['ugc_form_authenticated'] = true;
            
            // handle authentication conditions set in the form JSON file.
            if (property_exists($this->formOptions, 'requiresAuth') && $this->formOptions->requiresAuth) {
                // set authenticated value depending on the user being logged in.
                $info['ugc_form_authenticated'] = $this->user !== null;
            }            
        }
        
        return $info;
    }
    public function associateForm(&$content)
    {
        $forms = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:UserGeneratedContentForm');
        $form = $forms->findOneByContent($content);
        if ($form) {
            $form->addContent($content);
        }
    }
}
