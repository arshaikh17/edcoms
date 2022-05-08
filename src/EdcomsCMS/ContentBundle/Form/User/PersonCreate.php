<?php
namespace EdcomsCMS\ContentBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PersonCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id')
                ->add('firstName')
                ->add('lastName')
                ->add('contacts', CollectionType::class, ['entry_type'=>ContactCreate::class, 'allow_add'=>true, 'by_reference'=>false]);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>'EdcomsCMS\AuthBundle\Entity\Person'
        ]);
    }
    public function getName()
    {
        return 'PersonCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}