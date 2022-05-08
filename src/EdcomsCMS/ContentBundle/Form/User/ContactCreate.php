<?php
namespace EdcomsCMS\ContentBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('type')
                ->add('title')
                ->add('value');
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>'EdcomsCMS\AuthBundle\Entity\Contact'
        ]);
    }
    public function getName()
    {
        return 'ContactCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}