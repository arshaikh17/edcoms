<?php

namespace EdcomsCMS\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use EdcomsCMS\AuthBundle\Entity\cmsUsers;
use EdcomsCMS\AuthBundle\Entity\Person;
use EdcomsCMS\AuthBundle\Entity\Contact;
use EdcomsCMS\AuthBundle\Entity\cmsUserGroups;
use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\TemplateFiles;
use EdcomsCMS\ContentBundle\Entity\ContentType;
use EdcomsCMS\ContentBundle\Entity\CustomFields;
use EdcomsCMS\ContentBundle\Entity\CustomFieldData;
use EdcomsCMS\ContentBundle\Entity\Structure;

class DefaultController extends Controller
{
    /**
     * For Development Only!
     * @Route("/template/{template}")
     */
    public function templateAction($template)
    {
        return $this->render('templates/'.$template.'.html.twig');
    }
    /**
     * @Route("/cms/setup")
     */
    public function setup_cmsAction()
    {
        $em = $this->getDoctrine()->getEntityManager('edcoms_cms');
        $user = $this->setup_user();
        $em->persist($user);
        $em->persist($this->setup_content($user));
        $em->flush();
        return $this->view('cms/setup.html.twig');
    }
    private function setup_user()
    {
        $user = new cmsUsers();
        $adminPass = '#zm"BaUrzyMD$~6x';
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $adminPass);
        $user->setPassword($encoded);
        $user->setUsername('admin');
        $user->setDeleted(0);

        $person = new Person();
        $person->setFirstName('Admin');
        $person->setLastName('User');
        $contacts = new Contact();
        $contacts->setTitle('Email');
        $contacts->setType('email');
        $contacts->setValue('alerts@edco.ms');
        $person->addContact($contacts);
        
        $user->setPerson($person);
        
        $group = new cmsUserGroups();
        $group->setName('cms_admin');
        $group->setDescription('CMS Administrator');
        $group->setDefaultValue(true);
        $user->addGroup($group);

        $user->setIsActive(true);
        return $user;
    }
    public function setup_content($user)
    {
        $content_type = new ContentType();
        $content = new Content();
        
        $custom_field = new CustomFields();
        $custom_field->setDescription('The landing page main content');
        $custom_field->setFieldType('richtextarea');
        $custom_field->setLabel('Main content');
        $custom_field->setName('body');
        $content_type->addCustomField($custom_field);
        $custom_field_data = new CustomFieldData();
        $custom_field_data->setCustomFields($custom_field);
        $custom_field_data->setValue('<h1>Hello World!</h1>');
        $custom_field_data->setAddedUser($user);
        $custom_field_data->setAddedOn(new \DateTime());
        $content->addCustomFieldData($custom_field_data);
        
        $custom_field = new CustomFields();
        $custom_field->setDescription('The date of the landing page');
        $custom_field->setFieldType('date');
        $custom_field->setLabel('Landing page date');
        $custom_field->setName('date');
        $content_type->addCustomField($custom_field);
        $custom_field_data = new CustomFieldData();
        $custom_field_data->setCustomFields($custom_field);
        $custom_field_data->setValue('04/09/2015');
        $custom_field_data->setAddedUser($user);
        $custom_field_data->setAddedOn(new \DateTime());
        $content->addCustomFieldData($custom_field_data);
        
        $content_type->setDescription('The landing page content type');
        $content_type->setName('Landing page');
        $content_type->setShowChildren(1);
        $content_type->setIsChild(0);
        $content_type->setThumbnail('thumbs/article.jpg');
        
        $templateFile = new TemplateFiles();
        $templateFile->setTemplateFile('Site/index.html.twig');
        $content_type->addTemplateFile($templateFile);
        
        $content->setAddedUser($user);
        $content->setAddedOn(new \DateTime());
        $content->setStatus('published');
        $content->setTitle('Home');
        $content->setContentType($content_type);
        $content->setTemplateFile($templateFile);
        
        $structure = new Structure();
        $structure->setLink('home');
        $structure->setDeleted(0);
        $structure->setPriority(0);
        $structure->setTitle('Home');
        $structure->setVisible(true);
        
        $content->setStructure($structure);
        return $content;
    }
}
