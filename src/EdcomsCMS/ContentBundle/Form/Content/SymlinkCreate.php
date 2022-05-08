<?php
namespace EdcomsCMS\ContentBundle\Form\Content;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

use EdcomsCMS\ContentBundle\Form\Structure\StructureCreate;

class SymlinkCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id')
                ->add('status')
                ->add('structure', StructureCreate::class)
                ->add('title')
                ->add('save', ButtonType::class);
        foreach ($options['fields'] as $field) {
            $builder->add($field['name'], null, ['mapped'=>false, 'required'=>false]);
        }
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['fields', 'ContentType']);
    }
    public function getName()
    {
        return 'ContentCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}