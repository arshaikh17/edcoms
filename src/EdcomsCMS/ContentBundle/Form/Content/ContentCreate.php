<?php
namespace EdcomsCMS\ContentBundle\Form\Content;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

use EdcomsCMS\ContentBundle\Form\Structure\StructureCreate;

class ContentCreate extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id')
                ->add('title')
                ->add('structure', StructureCreate::class)
                ->add('status')
                ->add('templateFile', EntityType::class, [
                    'class'=>'EdcomsCMSContentBundle:TemplateFiles',
                    'choices'=>$options['ContentType']->getTemplateFiles(),
                    'choice_label'=>'templateFile'
                ])
                ->add('save', ButtonType::class);
        $customFieldNames = $this->getCustomFieldsNames($options['fields'], 2);
        foreach ($customFieldNames as $customFieldName) {
            $builder->add($customFieldName, null, ['mapped'=>false, 'required'=>false]);
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

    /**
     * Return an array of all the CustomField names (up to nth level defined by $depth parameter)
     * @param array $fields
     * @param int $depth
     *
     * @return array
     */
    private function getCustomFieldsNames(array $fields, $depth = 1){
        $customFieldNames= [];
        foreach ($fields as $field){
            if(!is_array($field)){
                $field = $field->toJSON();
            }
            if(isset($field['name']) && !in_array($field['name'],$customFieldNames)){
                $customFieldNames[] = $field['name'];
            }
            if($depth>1 && isset($field['children']) && count($field['children'])>0){
                $customFieldNames = array_merge($customFieldNames, $this->getCustomFieldsNames($field['children']->toArray(), $depth-1));
            }
        }
        return $customFieldNames;
    }
}