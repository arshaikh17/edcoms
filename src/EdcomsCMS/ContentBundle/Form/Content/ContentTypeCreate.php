<?php
namespace EdcomsCMS\ContentBundle\Form\Content;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class ContentTypeCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id')
                ->add('name')
                ->add('description')
                ->add('thumbnail', null, ['required'=>false] )
                ->add('showChildren', null, ['required'=>false])
                ->add('template_files', CollectionType::class, [
                    'entry_type'=>TemplateFileCreate::class,
                    'allow_add'=>true,
                    'allow_delete' => true,
                    'by_reference'=>false,
                    'delete_empty' => true,
                    'required'=>false
                ])
                ->add('isChild', null, ['required'=>false])
                ->add('custom_fields', CollectionType::class, [
                    'entry_type'=>CustomFieldsCreate::class,
                    'allow_add'=>true,
                    'allow_delete' => true,
                    'by_reference'=>false,
                    'delete_empty' => true
                ]);
    }
    public function getName()
    {
        return 'ContentTypeCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}