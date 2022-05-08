<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Service\Content;

use Doctrine\ORM\Mapping\OneToOne;
use EdcomsCMS\ContentBundle\Annotation\CustomFieldDataEntity;
use EdcomsCMS\ContentBundle\Annotation\StructureContext;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\CustomFieldDataCheckboxType;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\CustomFieldDataContentArrayType;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\CustomFieldDataCheckboxArrayType;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\CustomFieldDataDateType;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\CustomFieldDataEntityType;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\CustomFieldDataFileType;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\CustomFieldDataGroupType;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\CustomFieldDataNumberType;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\CustomFieldDataRadioArrayType;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\CustomFieldDataRichTextAreaType;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\CustomFieldDataTextAreaType;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\CustomFieldDataTextType;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\CustomFieldDataVideoType;
use EdcomsCMS\ContentBundle\Form\Type\Structure\StructureSelectType;
use EdcomsCMS\ContentBundle\Model\Context\StructureContextItemConfig;
use EdcomsCMS\ContentBundle\Model\CustomField\CustomFieldTypeDefinition;
use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\ContentType;
use EdcomsCMS\ContentBundle\Entity\CustomFieldData;
use EdcomsCMS\ContentBundle\Entity\CustomFields;
use EdcomsCMS\ContentBundle\Service\EdcomsContentConfigurationService;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Class ContentService
 * @package EdcomsCMS\ContentBundle\Service\Content
 *
 * @TODO Tidy up. Break down to separate services
 */
class ContentService
{

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $directory;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * The Kernel root directory
     * @var string
     */
    private $rootDir;

    private $configService;

    /**
     * @var array
     */
    private $extraCustomDataEntities = [];

    private $structureContextEntities = [];

    /**
     * ContentService constructor.
     * @param EdcomsContentConfigurationService $configurationService
     * @param $namespace
     * @param $directory
     * @param $rootDir
     * @param Reader $annotationReader
     */
    public function __construct(EdcomsContentConfigurationService $configurationService, $namespace, $directory, $rootDir, Reader $annotationReader)
    {
        $this->namespace = $namespace;
        $this->annotationReader = $annotationReader;
        $this->directory = $directory;
        $this->rootDir = $rootDir;
        $this->configService = $configurationService;
        $this->discoverExtraCustomDataEntities();
        if($this->configService->isContextEnabled()){
            $this->discoverStructureContextEntities();
        }
    }

    /**
     * Discovers CustomDataEntities
     */
    private function discoverExtraCustomDataEntities() {
        $path = $this->rootDir . '/../src/' . $this->directory;
        $fs = new Filesystem();
        if($fs->exists($path)){
            $finder = new Finder();
            $finder->files()->in($path);

            /** @var SplFileInfo $file */
            foreach ($finder as $file) {
                if(strpos($file->getRelativePathname(),'~')!=false){
                    continue;
                }
                $class = $this->namespace . '\\' . str_replace('/','\\',substr($file->getRelativePathname(), 0, -4));
                $annotation = $this->annotationReader->getClassAnnotation(new \ReflectionClass($class), 'EdcomsCMS\ContentBundle\Annotation\CustomFieldDataEntity');
                if (!$annotation) {
                    continue;
                }

                /** @var CustomFieldDataEntity $annotation */
                $this->extraCustomDataEntities[$annotation->getName()] = [
                    'class' => $class,
                    'annotation' => $annotation,
                ];
            }
        }
    }

    /**
     * Discovers StructureContext entities
     */
    private function discoverStructureContextEntities() {
        $path = $this->rootDir . '/../src/' . $this->directory;
        $fs = new Filesystem();
        if($fs->exists($path)){
            $finder = new Finder();
            $finder->files()->in($path);

            /** @var SplFileInfo $file */
            foreach ($finder as $file) {
                if(strpos($file->getRelativePathname(),'~')!=false){
                    continue;
                }
                $class = $this->namespace . '\\' . str_replace('/','\\',substr($file->getRelativePathname(), 0, -4));
                $this->checkIfValisStructureContent($class);
            }
            foreach ($this->configService->getConfigContextClasses() as $class => $contextClassConfig){
                $this->checkIfValisStructureContent($contextClassConfig['context_class'], $contextClassConfig);
            }
        }
    }

    private function checkIfValisStructureContent($class, $contextClassConfig=null){
        $annotation = $this->annotationReader->getClassAnnotation(new \ReflectionClass($class), 'EdcomsCMS\ContentBundle\Annotation\StructureContext');
        if (!$annotation) {
            return ;
        }
        $reflectionProperty = new \ReflectionProperty($class,'context');
        $contextAnnotations = $this->annotationReader->getPropertyAnnotations($reflectionProperty);
        $contextClass = $contextClassConfig ? $contextClassConfig['context'] : null;
        $annotationName = $contextClassConfig ? $contextClassConfig['name'] : $annotation->getName();
        if(!$contextClass){
            foreach ($contextAnnotations as $contextAnnotation){
                if(is_object($contextAnnotation) && get_class($contextAnnotation)==OneToOne::class){
                    /** @var OneToOne $contextAnnotation */
                    $contextClass = $contextAnnotation->targetEntity;
                }
            }
        }

        if(!$contextClass){
            throw new \Exception(sprintf('Doctrine annotations for property "context" not defined'));
        }

        /** @var StructureContext $annotation */

        $itemConfig = new StructureContextItemConfig();
        $itemConfig->setClass($class);
        $itemConfig->setContextClass($contextClass);
        $itemConfig->setForm($annotation->getForm());
        $itemConfig->setLabel($annotation->getLabel());
        $itemConfig->setName($annotationName);

        if($contextClassConfig){
            if($contextClassConfig['label']){
                $itemConfig->setLabel($contextClassConfig['label']);
            }
            if($contextClassConfig['name']){
                $itemConfig->setName($contextClassConfig['name']);
            }
            if($contextClassConfig['form']){
                $itemConfig->setForm($contextClassConfig['form']);
            }
        }
        $this->structureContextEntities[$itemConfig->getName()] = $itemConfig;
    }

    const customFieldTypesFormMappings = [
        "text" => CustomFieldDataTextType::class,                       // done
        "textarea" => CustomFieldDataTextAreaType::class,               // done
        "richtextarea" => CustomFieldDataRichTextAreaType::class,       // done
        "checkbox" => CustomFieldDataCheckboxType::class,               // done
        "radio_array" => CustomFieldDataRadioArrayType::class,          // done
        "checkbox_array" => CustomFieldDataCheckboxArrayType::class,    // done
        "number" => CustomFieldDataNumberType::class,                   // done
        "date" => CustomFieldDataDateType::class,                       // done
        "file" =>  CustomFieldDataFileType::class,                      // done
        "image" =>  CustomFieldDataFileType::class,                     // done
        "video" =>  CustomFieldDataVideoType::class,                     // done
        "hidden" => HiddenType::class,
        "content_array" => CustomFieldDataContentArrayType::class,      // done
        "file_array" =>  CustomFieldDataTextType::class,
        "group" => CustomFieldDataGroupType::class,                     // done
        "entity" => CustomFieldDataEntityType::class,
        "contentselector" => false
    ];


    /**
     * @param ContentType $contentType
     * @param Content $content
     * @return  Content
     */
    public function initContent(ContentType $contentType, Content $content){
        $content->setAddedOn(new \DateTime());
        $content->setContentType($contentType);
        $fields = $this->getFields($contentType, $content);
        foreach ($fields as $field){
            /** @var CustomFieldTypeDefinition $field */
            $this->addNewCustomFieldData($field->getCustomFields(), $content);
        }
        return $content;
    }

    /**
     * @param Content $content
     * @return  Content
     */
    public function syncContentFields(Content $content){
        $existingFieldsData = array();
        foreach ($content->getCustomFieldData() as $cfd){
            /** @var CustomFieldData $cfd */
            $existingFieldsData[$cfd->getCustomFields() ? $cfd->getCustomFields()->getName() : ''] = $cfd;
        }
        $allFields = $content->getContentType()->getCustomFields();
        foreach ($allFields as $field){
            /** @var CustomFields $field */
            if(!isset($existingFieldsData[$field->getName()])){
                $this->addNewCustomFieldData($field,$content);
            }
        }
        return $content;
    }

    /**
     * @param CustomFields $customFields
     * @return mixed
     * @throws \Exception
     */
    public function getFieldType(CustomFields $customFields){
        if(isset(ContentService::customFieldTypesFormMappings[$customFields->getFieldType()])){
            return ContentService::customFieldTypesFormMappings[$customFields->getFieldType()];
        }elseif(array_key_exists($customFields->getName(),$this->extraCustomDataEntities)){
            return CustomFieldDataEntityType::class;
        }else{
            throw new \Exception(sprintf('Field type "%s" not exist', $customFields->getFieldType()));
        }
    }

    /**
     * @param CustomFields $customFields
     * @return bool|mixed
     */
    public function getEntityType(CustomFields $customFields){
        if(array_key_exists($customFields->getLabel(),$this->extraCustomDataEntities)){
            return $this->extraCustomDataEntities[$customFields->getLabel()];
        }
        return false;
    }

    public function isEntityDataType(CustomFieldData $customFieldData){
        if(array_key_exists($customFieldData->getCustomFields()->getLabel(),$this->extraCustomDataEntities)){
            return $this->extraCustomDataEntities[$customFieldData->getCustomFields()->getLabel()];
        }
        return false;
    }

    public function getContextList(){
        $contextList = array();

        foreach ($this->structureContextEntities as $e){
            $contextList[$e->getLabel()] = $e->getName();
        }

        return $contextList;
    }

    public function getFieldTypes(){
        $fieldTypes = array();

        foreach (ContentService::customFieldTypesFormMappings as $key=>$type){
            $fieldTypes[ucfirst(str_replace("_"," ",$key))] =  $key;
        }

        foreach ($this->extraCustomDataEntities as $e){
            $fieldTypes[$e['annotation']->getLabel()] = $e['annotation']->getName();
        }

        return $fieldTypes;
    }


    /**
     * @param CustomFields $customFields
     * @param Content $content
     * @return CustomFieldData
     */
    private function addNewCustomFieldData(CustomFields $customFields, Content $content){
        $newCustomFieldData = new CustomFieldData();
        $newCustomFieldData->setAddedOn(new \DateTime());
        // TODO set user
//            $newCustomFieldData->setAddedUser($content->getAddedUser());
        $newCustomFieldData->setCustomFields($customFields);
        $newCustomFieldData->setContent($content);
        $newCustomFieldData->setValue('');
        $content->addCustomFieldData($newCustomFieldData);
        return $newCustomFieldData;
    }

    /**
     * @param ContentType $contentType
     * @param Content $content
     * @return CustomFieldTypeDefinition[]
     * @throws \Exception
     */
    private function getFields(ContentType $contentType, Content $content){

        // Flat customFieldData of Content
        $fieldData = [];
        foreach ($content->getCustomFieldData() as $data){
            /** @var CustomFieldData $data */
            $fieldData[$data->getCustomFields()->getName()] = $data->getValue();
        }

        $fields = [];
        foreach ($contentType->getCustomFields() as $customField){
            /** @var CustomFields $customField */
            // TODO check adminOnly flag?
            if(isset($this::customFieldTypesFormMappings[$customField->getFieldType()])){
                if(!$this::customFieldTypesFormMappings[$customField->getFieldType()]){
                    continue ;
                }
                $fieldDefinition = new CustomFieldTypeDefinition();
                $fieldDefinition->setName($customField->getName());
                $fieldDefinition->setLabel($customField->getLabel());
                $fieldDefinition->setDescription($customField->getDescription());
                $fieldDefinition->setFormType($this::customFieldTypesFormMappings[$customField->getFieldType()]);
                // If content exist then pre-populate the value of the field
                if($content->getId()){
                    switch($customField->getFieldType()){
                        case 'checkbox':
                            $value = isset($fieldData[$customField->getName()]) &&  $fieldData[$customField->getName()]? true : false;
                            break;
                        default:
                            $value = isset($fieldData[$customField->getName()]) ? $fieldData[$customField->getName()] : '';
                    }
                    $fieldDefinition->setValue($value);
                }
                $fieldDefinition->setIsAdmin($customField->getAdminOnly());
                $fieldDefinition->setRequired($customField->getRequired());
                $fieldDefinition->setCustomFields($customField);
                $fields[] = $fieldDefinition;
            }else{
                throw new \Exception(sprintf("Field type %s of Content type %s not exist",$customField->getFieldType(), $content->getContentType()->getName()));
            }
        }

        return $fields;
    }

    /**
     * @return array|StructureContextItemConfig
     */
    public function getStructureContextEntities()
    {
        return $this->structureContextEntities;
    }

    /**
     * @return array
     */
    public function getExtraCustomDataEntities()
    {
        return $this->extraCustomDataEntities;
    }



}