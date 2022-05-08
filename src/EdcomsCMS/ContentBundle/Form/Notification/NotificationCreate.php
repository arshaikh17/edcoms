<?php
namespace EdcomsCMS\ContentBundle\Form\Notification;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EdcomsCMS\AuthBundle\Entity\cmsUserGroups;
use EdcomsCMS\AuthBundle\Entity\cmsUsers;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EdcomsCMS\ContentBundle\Entity\Notification;

class NotificationCreate extends AbstractType {

    /**
     * Build notification form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id', HiddenType::class)
                ->add('name')
                ->add('info')
                ->add('type')
                ->add('target')
                ->add('recipient')
                ->add('user', EntityType::class, [
                    'class' => cmsUsers::class,
                    'choice_label' => 'username',
                    'placeholder' => 'Non selected',
                    'required' => false ])
                ->add('group', EntityType::class, [
                    'class' => cmsUserGroups::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Non selected',
                    'required' => false ])
                ->add('url', null, [
                    'required' => false ])
                ->add('save', SubmitType::class);
    }

    /**
     * Get the name of the form
     *
     * @return string
     */
    public function getName()
    {
        return 'NotificationCreate';
    }

    /**
     * Get the block prefix of form
     *
     * @return string
     */
    public function getBlockPrefix() {
        return $this->getName();
    }

    /**
     * Configure the form class
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        //set the data class
        $resolver->setDefaults(array(
            'data_class' => Notification::class,
            'csrf_protection' => false
        ));
    }
}