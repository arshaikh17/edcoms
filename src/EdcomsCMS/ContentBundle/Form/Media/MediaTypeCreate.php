<?php
namespace EdcomsCMS\ContentBundle\Form\Media;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

class MediaTypeCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id', null, ['required'=>false])
                ->add('filetype')
                ->add('compression', null, ['required'=>false])
                ->add('width', null, ['required'=>false])
                ->add('height', null, ['required'=>false])
                ->add('target')
                ->add('save', ButtonType::class);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>'EdcomsCMS\ContentBundle\Entity\MediaTypes'
        ]);
    }
    public function getName()
    {
        return 'MediaTypeCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}