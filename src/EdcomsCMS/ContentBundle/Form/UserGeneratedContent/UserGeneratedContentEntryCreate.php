<?php
namespace EdcomsCMS\ContentBundle\Form\UserGeneratedContent;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class UserGeneratedContentEntryCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id')
                ->add('status');
        foreach ($options['fields'] as $field=>$info) {
            $builder->add($field, null, ['mapped'=>false, 'required'=>$info->required]);
        }
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['fields']);
    }
    public function getName()
    {
        return 'UserGeneratedContentEntryCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}