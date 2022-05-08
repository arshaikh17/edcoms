<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\Media;

use EdcomsCMS\ContentBundle\Entity\Media;
use EdcomsCMS\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UploadedMediaType extends AbstractType
{

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('attachment', FileType::class,[
                'label' => false,
                'required' => $options['required'],
                'constraints' => new Assert\File([
                    'maxSize' => $options['maxSize'],
                    'mimeTypes' =>  $options['mimeTypes']
                ])
            ]);
        if($options['show_label']===true){
            $builder->add('label');
        }

        $tokenStorage = $this->tokenStorage;
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use($tokenStorage){
            $form = $event->getForm();
            $media = $event->getData();
            if($media && $media instanceof Media && $media->getAttachment()){
                $media->setPath($form->getConfig()->getOption('path'));
                $media->setTarget($form->getConfig()->getOption('target'));
                $media->setTitle($media->getAttachment()->getClientOriginalName());
                /** @var TokenStorage $tokenStorage */
                if($tokenStorage->getToken()->getUser() && $tokenStorage->getToken()->getUser() instanceof User){
                    $media->setAddedBy($tokenStorage->getToken()->getUser());
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
           'path', 'target'
        ]);
        $resolver->setDefaults([
            'data_class' => Media::class,
            'maxSize' => '10M',
            'mimeTypes' => [],
            'show_label' => false
        ]);
    }

}