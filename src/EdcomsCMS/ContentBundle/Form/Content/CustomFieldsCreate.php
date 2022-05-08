<?php
namespace EdcomsCMS\ContentBundle\Form\Content;

use EdcomsCMS\ContentBundle\Entity\CustomFields;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class CustomFieldsCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id')
                ->add('name')
                ->add('description')
                ->add('label')
                ->add('defaultValue')
                ->add('required')
                ->add('options')
                ->add('adminOnly')
                ->add('order', null, ['required'=>false])
                ->add('fieldType')
                ->add('children')
                ->add('parent', EntityType::class, [
                    'class' => 'EdcomsCMSContentBundle:CustomFields',
                    'choice_label' => 'label',
                    'required'=>false
                ])
                ->add('repeatable');


        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if(isset($data['subfields']) && is_array($data['subfields'])){
                $subfields = $data['subfields'];
                unset($data['subfields']);
                foreach ($subfields as &$subfield){
                    if(isset($subfield['parent'])
                        && is_array($subfield['parent'])
                        && isset($subfield['parent']['id'])
                    ){
                        $subfield['parent'] = $subfield['parent']['id'];
                    }
                }
                $event->getForm()->add('children', CollectionType::class, [
                    'entry_type'=>CustomFieldsCreate::class,
                    'allow_add'=>true,
                    'allow_delete' => true,
                    'by_reference'=>false,
                    'delete_empty' => true
                ]);
                $data['children'] = $subfields;
                $event->setData($data);
            }
        });

    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>'EdcomsCMS\ContentBundle\Entity\CustomFields'
        ]);
    }
    public function getName()
    {
        return 'CustomFieldsCreate';
    }
    public function getBlockPrefix() {
        return $this->getName();
    }
}