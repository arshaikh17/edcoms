<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData;

use EdcomsCMS\ContentBundle\Entity\CustomFieldData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class CustomFieldDataType extends AbstractType
{

    /** @var TokenStorage
     */
    private $tokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var CustomFieldData $customFieldData */
            $customFieldData = $event->getData();
            $form = $event->getForm();
            if($customFieldData){
                $customFieldData->setContent($form->getConfig()->getOptions()['content']);
                $customFieldData->setCustomFields($form->getConfig()->getOptions()['customField']);
                if(!$customFieldData->getId()){
                    $customFieldData->setAddedBy($this->tokenStorage->getToken()->getUser());
                }else{
                    $customFieldData->setUpdatedBy($this->tokenStorage->getToken()->getUser());
                }
            }
        });
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CustomFieldData::class,
            'button_label'=> null,
            'customField' => null,
            'content' => null,
            'customFieldsData' => null,
            'is_prototype' => false
        ));
    }
}