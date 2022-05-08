<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;

class CustomFieldDataValueEntityType extends EntityType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm( $builder, $options);
        $builder
            ->addModelTransformer(new CallbackTransformer(
                function ($entityId) use($options){
                    if(!$entityId){
                        return null;
                    }
                    /** @var EntityManager $em */
                    $em = $options['em'];
                    $entity = $em->getRepository($options['class'])->find($entityId);
                    if(!$entity){
                        throw new \Exception(sprintf("Entity of class %s and id %s not exist",$options['class'], $entityId));
                    }
                    return $entity;
                },
                function ($entity) {
                    return $entity ? $entity->getId() : '';
                }
            ))
        ;
    }
}