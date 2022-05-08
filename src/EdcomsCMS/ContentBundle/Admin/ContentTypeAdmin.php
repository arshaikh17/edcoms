<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Admin;

use EdcomsCMS\ContentBundle\Entity\ContentType;
use EdcomsCMS\ContentBundle\Form\EventListener\Content\ResizeContentFieldsListener;
use EdcomsCMS\ContentBundle\Form\Type\ContentType\ContentTypeFieldsType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldCheckboxArrayType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldCheckboxType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldContentArrayType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldDateType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldEntityType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldFileType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldGroupType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldHiddenType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldImageType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldNumberType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldRadioArrayType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldRichTextAreaType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldTextAreaType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldTextType;
use EdcomsCMS\ContentBundle\Form\Type\CustomField\CustomFieldVideoType;
use EdcomsCMS\ContentBundle\Service\Content\ContentService;
use EdcomsCMS\ContentBundle\Service\EdcomsContentConfigurationService;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ContentTypeAdmin extends AbstractAdmin
{

    /**
     * @var ContentService
     */
    private $contentService;

    public function __construct($code, $class, $baseControllerName, ContentService $contentService)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->contentService = $contentService;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var ContentType $contentType */
        $contentType = $this->getSubject();

        // Ability to  change the context of a ContentType is allowed only to new ContentType
        // or when there is no context set on a Structure for the corresponding ContentType
        $isContextEditable = $contentType->getId() ? false : true;
        if(!$isContextEditable){
            $context = $contentType->getContext();
            $contextEntities = $this->contentService->getStructureContextEntities();
            if(array_key_exists($context, $contextEntities)){
                $contextClass = $contextEntities[$context]->getClass();
                $em = $this->getConfigurationPool()->getContainer()->get('doctrine.orm.default_entity_manager');
                $result = $em
                            ->getRepository($contextClass)
                            ->createQueryBuilder('c')
                            ->getQuery()
                            ->getResult()
                            ;
                $isContextEditable = count($result) ? false : true;
            }
        }

        /** @var EdcomsContentConfigurationService $confService */
        $confService = $this->configurationPool->getContainer()->get('edcoms.content.service.configuration');

        $formMapper
            ->tab('Details')
                ->with("Basic", array('class'=>'col-md-6'))
                    ->add('name', 'text')
                    ->add('description', 'text')
            ;

        if($confService->isContextEnabled()){
            $formMapper
                ->add('contextEnabled')
                ->add('context',ChoiceType::class,array(
                    'choices'=> $this->contentService->getContextList(),
                    'required'=>false,
                    'disabled' => !$isContextEditable,
                    "placeholder"=>"No Context",
                    "help"=> "Links to a specific entity type"
                ))
                ->add('forceContextDropdown')
                ;
        }
        
        $formMapper
                    ->add('isPage', 'checkbox',array(
                        "required" => false,
                    ))
                    ->add('showChildren', 'checkbox',array(
                        "required" => false,
                    ))
                    ->add('isChild','checkbox',array(
                        "required"=>false,
                    ))
                ->end()
                ->with("Templates", array('class'=>'col-md-6'))
                    ->add('template_files', 'sonata_type_collection', array(
                    'type_options' => array(
                        // Prevents the "Delete" option from being displayed
                        'delete' => true,
                        'delete_options' => array(
                            // You may otherwise choose to put the field but hide it
                            'type'         => 'checkbox',
                            // In that case, you need to fill in the options as well
                            'type_options' => array(
                                'mapped'   => false,
                                'required' => false,
                            ),
                        ),
                    ),
                    ), array(
                        'edit' => 'inline',
                        'inline' => 'table',
                    ))
                ->end()
            ->end()
            ->tab('Advanced')
                ->with("Fields")
                    ->add('custom_fields',ContentTypeFieldsType::class,array(
                        'label'=> false,
                        'attr'=> array('class'=>'content-type-custom-fields'),
                        'type_attr' => 'fieldType',
                        'types' => array(
                            CustomFieldContentArrayType::class,
                            CustomFieldTextType::class,
                            CustomFieldTextAreaType::class,
                            CustomFieldRichTextAreaType::class,
                            CustomFieldCheckboxType::class,
                            CustomFieldRadioArrayType::class,
                            CustomFieldCheckboxArrayType::class,
                            CustomFieldDateType::class,
                            CustomFieldNumberType::class,
                            CustomFieldGroupType::class,
                            CustomFieldFileType::class,
                            CustomFieldImageType::class,
                            CustomFieldVideoType::class,
                            CustomFieldHiddenType::class,
                            CustomFieldEntityType::class
                        ),
                        'types_options' => array(
                            CustomFieldTextType::class => array(
                                'button_label' => 'Text'
                            ),
                            CustomFieldContentArrayType::class => array(
                                'button_label' => 'Content Array'
                            ),
                            CustomFieldTextAreaType::class => array(
                                'button_label' => 'Textarea'
                            ),
                            CustomFieldRichTextAreaType::class => array(
                                'button_label' => 'Rich Textarea'
                            ),
                            CustomFieldCheckboxType::class => array(
                                'button_label' => 'Checkbox'
                            ),
                            CustomFieldRadioArrayType::class => array(
                                'button_label' => 'Radio Array'
                            ),
                            CustomFieldCheckboxArrayType::class => array(
                                'button_label' => 'Checkbox Array'
                            ),
                            CustomFieldDateType::class => array(
                                'button_label' => 'Date'
                            ),
                            CustomFieldNumberType::class => array(
                                'button_label' => 'Number'
                            ),
                            CustomFieldGroupType::class => array(
                                'button_label' => 'Group',
                                'group_depth' => 0   /**
                                                    * DON'T increase the group depth (ability to add nested groups). ContentTypes supports nested Groups at the moment but there is an issue during the Content management.
                                                    * @TODO Debug and fix it in order to support nested Groups!
                                                    * @see \EdcomsCMS\ContentBundle\Form\EventListener\Content\ResizeContentFieldsListener
                                                    */
                            ),
                            CustomFieldFileType::class => array(
                                'button_label' => 'File'
                            ),
                            CustomFieldImageType::class => array(
                                'button_label' => 'Image'
                            ),
                            CustomFieldVideoType::class => array(
                                'button_label' => 'Video'
                            ),
                            CustomFieldHiddenType::class => array(
                                'button_label' => 'Hidden'
                            ),
                            CustomFieldEntityType::class => array(
                                'button_label' => 'Entity'
                            )
                        ),
                        'allow_add' => true,
                        'allow_delete' => true
                    ))
                ->end()
            ->end()
            ;

    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('name', 'edcoms_doctrine_orm_istring');
    }


    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('name');
    }

//    public function getFormTheme()
//    {
//        return array_merge(
//            parent::getFormTheme(),
//            array('EdcomsCMSContentBundle::Admin/ContentType/form_admin_fields.html.twig')
//        );
//    }
}