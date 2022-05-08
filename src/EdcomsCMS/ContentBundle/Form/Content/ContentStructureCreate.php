<?php
namespace EdcomsCMS\ContentBundle\Form\Content;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ContentStructureCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('title')
                ->add('link')
                ->add('parentID');
    }
    public function getName()
    {
        return 'ContentStructureCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}