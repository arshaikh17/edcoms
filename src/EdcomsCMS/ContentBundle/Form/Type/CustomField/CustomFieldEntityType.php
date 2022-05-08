<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomField;

use Doctrine\ORM\EntityManager;
use EdcomsCMS\ContentBundle\Entity\ContentType;
use EdcomsCMS\ContentBundle\Service\Content\ContentService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomFieldEntityType extends CustomFieldType
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

        $entityChoices = [];
        foreach ($this->contentService->getExtraCustomDataEntities() as $e){
            $entityChoices[$e['annotation']->getLabel()] = $e['annotation']->getName();
        }

        parent::buildForm($builder,$options);
        $builder
            ->add('fieldType',HiddenType::class,array(
                'data'=>'entity'
            ))
            ->add('options',ChoiceType::class,array(
                'choices'=> $entityChoices,
                'multiple' => false,
                'label' => 'Entity',
                'required' => true
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }
}