<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManager;

/**
 * Class CustomFieldDataValueEntityType
 * @package EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value
 */
class CustomFieldDataValueContentType extends EntityType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm( $builder, $options);
        $builder
            ->addModelTransformer(new CallbackTransformer(
                function ($entityIDsString) use($options){
                    $entityIDs = json_decode($entityIDsString, true);
                    if(!$entityIDs){
                        return [];
                    }
                    /** @var EntityManager $em */
                    $em = $options['em'];
                    if(isset($options['multiple']) && $options['multiple']==true){
                        $qb = $em->getRepository($options['class'])->createQueryBuilder('content');
                        $qb
                            ->where("content.id IN(:ids)")
                            ->setParameter('ids',$entityIDs);
                        return $qb->getQuery()->getResult();
                    }else{
                        if($entityIDs){
                            $entity =  $em->getRepository($options['class'])->find($entityIDs[0]);
                            return $entity ?: [];
                        }else{
                            return [];
                        }
                    }

                },
                function ($entities) use($options){
                    if(isset($options['multiple']) && $options['multiple']==true){
                        if($entities && ( (is_array($entities) && count($entities)!=0)) || (get_class($entities)==ArrayCollection::class && $entities->count()!=0)){
                            foreach ($entities as $entity){
                                $valueToReturn[] = $entity->getId();
                            }
                        }else{
                            $valueToReturn = [];
                        }
                    }else{
                        if($entities){
                            $valueToReturn[] = $entities->getId();
                        }else{
                            $valueToReturn = '';
                        }
                    }
                    return json_encode($valueToReturn);
                }
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if(!$data){
                $form->setData('');
            }
        });

    }
}