<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomField;

use EdcomsCMS\ContentBundle\Entity\ContentType;
use EdcomsCMS\ContentBundle\Entity\CustomFields;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CustomFieldType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class,[
                'label' => 'Label',
            ])
            ->add('name', TextType::class,[
                'label' => 'Name',
            ])
            ->add('description', TextareaType::class,[
                'label' => 'Description',
            ])
            ->add('defaultValue',TextType::class, [
                'required' => false,
                'label' => 'Default Value',
            ])
            ->add('required',CheckboxType::class,[
                'required' => false,
                'label' => 'Required?',

           ])
            ->add('adminOnly',CheckboxType::class,[
                "required" => false,
                'label' => 'Admin Only?',
            ])
            ->add('repeatable',CheckboxType::class,[
                "required" => false
            ])
            ->add('order')
        ;


        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var CustomFields $customField */
            $customField = $event->getData();
            $form = $event->getForm();
            $parentFormData = $form->getParent()->getParent()->getData();
            if($parentFormData){
                switch(get_class($parentFormData)){
                    case ContentType::class:
                        /** @var ContentType $parentFormData */
                        $customField->setContentType($parentFormData);
                        break;
                    case CustomFields::class:
                        /** @var CustomFields $parentFormData */
                        $customField->setParent($parentFormData);
                        break;
                }
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var CustomFields $customField */
            $customField = $event->getData();
            if($customField && $customField->getChildren()->count()>0){
                /** @var CustomFields $child */
                foreach ($customField->getChildren() as $child){
                    $child->setContentType($customField->getContentType());
                }
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CustomFields::class,
            'model_class' => CustomFields::class,
            'button_label' => '',
            'is_prototype' => false
        ));
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['button_label'] = $options['button_label'];

        // Hide subfield from the collection
        /** @var CustomFields $customField */
        $customField = $form->getData();
        if($customField && $customField->getParent()){
            $parentForm =  $form->getParent()->getParent();
            $parentFormData =$parentForm->getData();
            if($parentFormData && get_class($parentFormData)==ContentType::class){
                $view->vars['attr']['class'] = 'hidden';
            }
        }
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'content_content_field';
    }
}