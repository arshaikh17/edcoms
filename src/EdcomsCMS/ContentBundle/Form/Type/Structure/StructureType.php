<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\Structure;

use Doctrine\ORM\EntityRepository;
use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\Structure;
use EdcomsCMS\ContentBundle\Form\Type\PageMetadataType;
use EdcomsCMS\ContentBundle\Service\EdcomsContentConfigurationService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use EdcomsCMS\ContentBundle\Service\Content\ContentService;

/**
 * Class StructureType
 * @package EdcomsCMS\ContentBundle\Form\Type\Structure
 */
class StructureType extends AbstractType
{

    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * @var EdcomsContentConfigurationService
     */
    private $configService;

    public function __construct(ContentService $contentService, EdcomsContentConfigurationService $configurationService)
    {
        $this->contentService = $contentService;
        $this->configService = $configurationService;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('parent',EntityType::class, [
                'required' => false,
                'placeholder' => 'Select parent content',
                'class' => Structure::class,
                'query_builder'=> function(EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('str')
                        ->select('str', 'context')
                        ->leftjoin('str.context','context')
                        ;
                }
            ])
            ->add('priority')
//            ->add('rateable')
//            ->add('visible')
            ;
        if($this->configService->getStructureVisibility()){
            $builder->add('visible');
        }


        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var Structure $structure */
            $structure= $event->getData();
            $structureExist = $structure && $structure->getId() ? true : false;
            $event->getForm()->add('link','text',array(
                "label"=>"Friendly URL",
                "help"  => "URL Slug",
                "disabled" => $structureExist ? true : false,
                'constraints' => array(
                    new NotNull(),
                    new NotBlank(),
                    new Regex(array(
                        'pattern' => '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                        'match'   => true,
                        'message' => 'The url should contain only alphanumeric characters and hyphens. No blank space is allowed',
                    ))
                )
            ));

            if($event->getForm()->getConfig()->getOptions()['is_symlink']==true){
                $event->getForm()->add('master', null, array(
                    "label" => 'Symlink'
                ));
            }

            $parentOptions = [
                'required' => false,
                'placeholder' => 'Select parent content'
            ];

            if ($structureExist) {
                $parentOptions['query_builder'] = function (EntityRepository $repository) use ($structure) {
                    return $repository
                        ->createQueryBuilder('s')
                        ->where('s != :structure')
                        ->setParameter('structure', $structure);
                };
            }

            $event->getForm()->add('parent',null, $parentOptions);

            $content = $event->getForm()->getParent()->getData();
            if($this->configService->isContextEnabled() && get_class($content)==Content::class){
                /** @var Content $content */

                $contextList = $this->contentService->getStructureContextEntities();
                $structureContext = $content->getContentType()->getContext();
                if($content->getContentType()->isContextEnabled() && array_key_exists($structureContext, $contextList)){
                    $event->getForm()->add('context', !$content->getContentType()->getForceContextDropdown() && $contextList[$structureContext]->getForm() ? EmbedStructureContextType::class : StructureContextType::class,array(
                        'context'=> $contextList[$structureContext],
                        'label' => false,
                        'data_class' => $contextList[$structureContext]->getClass(),
                    ));

                }
            }
            if($content && $content->getContentType()->isPage()){
                $event->getForm()->add('pageMetadata', PageMetadataType::class);
            }

        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var Structure $structure */
            $structure= $event->getData();
            if($structure && $structure->getContext()){
                $structure->getContext()->setStructure($structure);
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EdcomsCMS\ContentBundle\Entity\Structure',
            'is_symlink' => false
        ));
    }
}