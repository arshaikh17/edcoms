<?php
namespace EdcomsCMS\ContentBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EdcomsCMS\AuthBundle\Entity\cmsUserGroups;

class UserCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id')
                ->add('person', PersonCreate::class)
                ->add('username')
                ->add('password', PasswordType::class, ['mapped'=>false, 'required'=>false])
                ->add('is_active', null, ['required'=>false])
                ->add('deleted', null, ['required'=>false])
                ->add('groups', EntityType::class, [
                    'class'=>cmsUserGroups::class,
                    'choice_label'=>'name',
                    'expanded'=>true,
                    'multiple'=>true
                ]);
    }
    public function getName()
    {
        return 'UserCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}