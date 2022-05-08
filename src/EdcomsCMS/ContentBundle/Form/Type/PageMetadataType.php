<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type;

use EdcomsCMS\AdminBundle\Form\Type\Layout\GroupType;
use EdcomsCMS\ContentBundle\Entity\PageMetadata;
use EdcomsCMS\ContentBundle\Form\Type\Media\MediaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PageMetadataType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $seo = $builder->create('seoGroup', GroupType::class, array(
            'group_label' => 'SEO Details',
            'description' => 'Meta tags are used to provide information to search engines.',
            'size' => GroupType::FIVECOLS
        ));

        $seo
            ->add('seoTitle',TextType::class,[
                'label' => 'SEO title'
            ])
            ->add('seoDescription',TextareaType::class,[
                'label' => 'SEO description',
                'attr' => array('rows' => '4'),
            ])
            ->add('seoKeywords',TextType::class,[
                'label' => 'SEO Keywords',
                'help' => 'Comma separated keywords'
            ])
            ->add('hideFromSearchEngines',CheckboxType::class,[
                'label' => 'Hide from search engines?',
                'required' => false
            ])
            ->add('seoPriority', ChoiceType::class,[
                'required' => false,
                'empty_data' => '0.3',
                'placeholder' => 'Choose an option (Default 0.3)',
                'choices' => [
                    '0.1' => 0.1,
                    '0.2' => 0.2,
                    '0.3' => 0.3,
                    '0.4' => 0.4,
                    '0.5' => 0.5,
                    '0.6' => 0.6,
                    '0.7' => 0.7,
                    '0.8' => 0.8,
                    '0.9' => 0.9,
                    '1' => 1,
                ]
            ])
        ;

        $opengraph = $builder->create('ogGroup', GroupType::class, array(
            'group_label' => 'Open Graph Details',
            'description' => 'Open graph tags enable any web page to become a rich object in a social graph. More information at <a href="http://ogp.me/" target="_blank">Open graph protocol</a>',
            'size' => GroupType::SEVENCOLS
        ));

        $opengraph
            ->add('title',TextType::class,[
                'label' => 'Open graph Title',
            ])
            ->add('description',TextareaType::class,[
                'label' => 'Open graph Description',
                'attr' => array('rows' => '4'),
            ])
            ->add('image',MediaType::class,[
                'media_object'=> true,
                'label' => 'Open graph Image',
                'required' => false
            ]);

        $builder
            ->add($seo)
            ->add($opengraph)
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PageMetadata::class
        ));
    }
}