<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\UserBundle\Form\Type;

use EdcomsCMS\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RTBFUserType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('userId', HiddenType::class,[
                'data' => $options['userId'],
                'mapped' => false
            ])
            ->add('rtbf_user', SubmitType::class, [
                'label' => 'Apply RTBF'
            ]
        );

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $submittedData = $event->getData();
            /** @var User $user */
            $user = $event->getForm()->getData();
            if($submittedData['userId']!=$user->getId()){
                $event->getForm()->addError(new FormError(sprintf('An unexpected error occurred while RTBF user with email %s', $user->getEmail())));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefault('data_class',User::class);
        $resolver->setDefault('csrf_token_id', 'id');
        $resolver->setRequired('userId');
    }

}
