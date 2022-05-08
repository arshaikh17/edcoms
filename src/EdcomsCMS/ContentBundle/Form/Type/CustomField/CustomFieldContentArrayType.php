<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomField;

use Doctrine\ORM\EntityManager;
use EdcomsCMS\ContentBundle\Entity\CustomFields;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value\CustomFieldDataValueNumberType;
use EdcomsCMS\ContentBundle\Service\Content\ContentService;
use EdcomsCMS\ContentBundle\Entity\ContentType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CustomFieldContentArrayType extends CustomFieldType
{

    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * CustomFieldContentArrayType constructor.
     * @param ContentService $contentService
     * @param EntityManager $entityManager
     */
    public function __construct(ContentService $contentService, EntityManager $entityManager)
    {
        $this->contentService = $contentService;
        $this->entityManager = $entityManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $contentTypes = $this->entityManager
                        ->getRepository(ContentType::class)
                        ->findBy([],["name"=>"DESC"]);

        $contenTypesChoices = [];
        foreach ($contentTypes as $ct){
            /** @var ContentType $ct */
            $contenTypesChoices[$ct->getName()] = $ct->getId();
        }

        parent::buildForm($builder,$options);
        $builder
            ->add('fieldType',HiddenType::class,array(
                'data'=> 'content_array',
                'label' => 'Field Type'
            ))
            ->add('contentTypes',"choice",array(
                'choices'=> $contenTypesChoices,
                'mapped' => false,
                'multiple' => true,
                'label' => 'Content Types',
                'required' => false
            ))
            ->add('isMultiple',CheckboxType::class,array(
                'mapped' => false,
                'label' => 'Is Multiple',
                'required' => false
            ))
            ->add('restriction',CustomFieldDataValueNumberType::class,array(
                'mapped' => false,
                'label' => 'Maximum choices',
                'required' => false
            ))
            ->add('options',HiddenType::class,array(
                'required' => false,
                'label' => 'Options'
            ))
            ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($contenTypesChoices) {
            /** @var CustomFields $data */
            $customField = $event->getData();
            $form = $event->getForm();

            if($customField && $customField->getFieldType()==='content_array'){
                $options = json_decode($customField->getOptions(),true);
                if(isset($options['isMultiple'])){
                    $form->get('isMultiple')->setData($options['isMultiple'] ? true : false);
                }
                if(isset($options['restriction'])){
                    $form->get('restriction')->setData($options['restriction']);
                }
                if(isset($options['contentType'])){
                    $form->get('contentTypes')->setData($options['contentType']);
                }
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($contenTypesChoices) {
            $data = $event->getData();
            if($data && $data['fieldType']==='content_array'){

                $options = [
                    'isMultiple' => isset($data['isMultiple']) && $data['isMultiple'] ? true : false,
                    'restriction' => isset($data['restriction']) ? $data['restriction'] : 0,
                    'contentType' => isset($data['contentTypes']) ? $data['contentTypes'] : []
                ];
                $event->setData(array_merge($data,['options'=>json_encode($options)]));
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'content_content_field_content_array';
    }
}