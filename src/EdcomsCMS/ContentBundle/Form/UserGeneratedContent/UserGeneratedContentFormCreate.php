<?php
namespace EdcomsCMS\ContentBundle\Form\UserGeneratedContent;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UserGeneratedContentFormCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id')
                ->add('name')
                ->add('content', EntityType::class, [
                    'class'=>'EdcomsCMSContentBundle:Content',
                    'choice_label'=>'title',
                    'multiple'=>true
                ])
                ->add('entriesParent', EntityType::class, [
                    'class'=>'EdcomsCMSContentBundle:Structure',
                    'choice_label'=>'title',
                    'required' => false
                ])
                ->add('type')
                ->add('templateFile')
                ->add('entriesVisible')
                ->add('entryContentType', EntityType::class, [
                    'class'=>'EdcomsCMSContentBundle:ContentType',
                    'choice_label'=>'name',
                    'required' => false
                ])
                ->add('notification')
                ->add('groups', EntityType::class, [
                    'class'=>'EdcomsCMSAuthBundle:cmsUserGroups',
                    'choice_label'=>'name',
                    'expanded'=>true,
                    'multiple'=>true
                ])
                ->add('formBuilderElements', CollectionType::class, [
                    'entry_type'=>FormBuilderElementsCreate::class,
                    'allow_add'=>true,
                    'by_reference'=>false
                ])
                ->add('form_title');
    }
    public function getName()
    {
        return 'UserGeneratedContentFormCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}