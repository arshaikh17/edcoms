<?php
namespace EdcomsCMS\BadgeBundle\Form;

use EdcomsCMS\AuthBundle\Entity\cmsUserGroups;
use EdcomsCMS\ContentBundle\Entity\Media;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BadgeSimpleCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name', TextType::class, ['constraints' => new NotBlank()])
                ->add('slug', TextType::class, ['constraints' => new NotBlank()])
                ->add('description', TextType::class, ['required'=>false])
                ->add('image', EntityType::class, [
                    'class'=>Media::class,
                    'choice_label'=>'title'
                    ], ['required'=>false])
                ->add('isActive', null, ['required'=>false])
                ->add('cms_user_group', EntityType::class, [
                    'class'=>cmsUserGroups::class,
                    'choice_label'=>'name'
                ], ['required'=>false])
                ->add('action', TextType::class, ['required'=>false])
                ->add('target', TextType::class, ['required'=>false])
                ->add('multiplier', IntegerType::class, ['required'=>false])
                ->add('isDistinct', null, ['required'=>false])
                ->add('save', SubmitType::class);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>'EdcomsCMS\BadgeBundle\Entity\BadgeSimple'
        ]);
    }
    public function getName()
    {
        return 'BadgeSimpleCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}