<?php
namespace EdcomsCMS\ContentBundle\Form\Content;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateFileCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id')
                ->add('templateFile');
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>'EdcomsCMS\ContentBundle\Entity\TemplateFiles'
        ]);
    }
    public function getName()
    {
        return 'TemplateFileCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}