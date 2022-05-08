<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Admin;

use EdcomsCMS\ContentBundle\Service\Content\ContentService;
use EdcomsCMS\ContentBundle\Entity\ContentType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class CustomFieldAdmin extends AbstractAdmin
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
        $contentTypes = $this->modelManager->findBy(ContentType::class);

        $contenTypesChoices = array();
        foreach ($contentTypes as $ct){
            /** @var ContentType $ct */
            $contenTypesChoices[$ct->getName()] = $ct->getId();
        }
        $choices = $this->contentService->getFieldTypes();
        $formMapper
            ->add('label', 'text')
            ->add('name', 'text')
            ->add('description', 'textarea')
            ->add('fieldType',"choice",array(
                "choices"=> $choices
            ))
            ->add('contentTypes',"choice",array(
                "choices"=> $contenTypesChoices,
                "mapped" => false,
                "multiple" => true
            ))
            ->add('options','text',array(
                "required" => false
            ))
            ->add('defaultValue','text',array(
                "required" => false
            ))
            ->add('required','checkbox',array(
                "required" => false
            ))
            ->add('adminOnly','checkbox',array(
                "required" => false
            ))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('label');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('label');
    }
}