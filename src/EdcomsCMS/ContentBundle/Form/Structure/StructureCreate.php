<?php
namespace EdcomsCMS\ContentBundle\Form\Structure;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class StructureCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id')
                ->add('priority')
                ->add('parent', EntityType::class, [
                    'class'=>'EdcomsCMSContentBundle:Structure',
                    'choice_label'=>'title'
                ])
                ->add('link')
                ->add('rateable')
                ->add('visible');
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>'EdcomsCMS\ContentBundle\Entity\Structure'
        ]);
    }
    public function getName()
    {
        return 'StructureCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}