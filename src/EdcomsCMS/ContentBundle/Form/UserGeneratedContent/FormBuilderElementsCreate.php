<?php
namespace EdcomsCMS\ContentBundle\Form\UserGeneratedContent;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormBuilderElementsCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('label')
                ->add('type')
                ->add('default_value');
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>'EdcomsCMS\ContentBundle\Entity\FormBuilderElements'
        ]);
    }
    public function getName()
    {
        return 'FormBuilderElementsCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}