<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\CustomFields;
use EdcomsCMS\ContentBundle\Entity\Structure;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value\CustomFieldDataValueChoiceType;
use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value\CustomFieldDataValueContentType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Class CustomFieldDataContentArrayType
 * @package EdcomsCMS\ContentBundle\Form\Type\CustomFieldData
 */
class CustomFieldDataContentArrayType extends CustomFieldDataType
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * CustomFieldDataContentArrayType constructor.
     * @param TokenStorage $tokenStorage
     * @param EntityManager $em
     */
    public function __construct(TokenStorage $tokenStorage, EntityManager $em)
    {
        parent::__construct($tokenStorage);
        $this->em = $em;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm( $builder, $options);
        $builder->add('_type',HiddenType::class,array(
            "data"=> 'content_array_type',
            "mapped"=> false
        ));


        /** @var CustomFields $customField */
        $customField = $options['customField'];
        if(!$customField){
            throw new \Exception('Customfield not found');
        }
        $contentArrayOptions = json_decode($customField->getOptions(),true);
        $multiple = $contentArrayOptions['isMultiple'] ? true : false;

        if($multiple){
            $choices = [];

            $structureChoices = $this->em
                                    ->getRepository(Structure::class)
                                    ->createQueryBuilder('structure')
                                    ->select('structure', 'context')
                                    ->leftJoin('structure.content','content')
                                    ->leftJoin('structure.context','context')
                                    ->leftJoin('content.contentType','contentType')
                                    ->where("contentType.id IN(:ids)")
                                    ->setParameter('ids',$contentArrayOptions['contentType'])
                                    ->getQuery()
                                    ->getResult()
                                ;
            foreach ($structureChoices as $sc){
                /** @var Structure $sc */
                $choices[$sc->__toString()] = $sc->getId();
            }

            $builder
                ->add('value', CustomFieldDataValueChoiceType::class, array(
                    "label" => false,
                    "required" => $customField->getRequired(),
                    "choices" => $choices,
                    "multiple" => $multiple,
                    "sortable" => true,
                    "by_reference" => false,
                    'attr'=>array(
                        'data-sonata-select2-maximumSelectionSize'=>$contentArrayOptions['restriction']
                    )
                ))
            ;

            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                if(isset($data['value']) && is_array($data['value'])){
                    $newValues = [];
                    foreach ($data['value'] as $d){
                        $newValues[] = reset($d);
                    }
                    $data['value'] = $newValues;
                    $event->setData($data);
                }
            });
        }else{
            $builder
                ->add('value', CustomFieldDataValueContentType::class, array(
                    "label" => false,
                    "required" => $customField->getRequired(),
                    "class" => Structure::class,
                    'query_builder' => function(EntityRepository $er) use($contentArrayOptions) {
                        return $er
                            ->createQueryBuilder('structure')
                            ->leftJoin('structure.content','content')
                            ->leftJoin('content.contentType','contentType')
                            ->where("contentType.id IN(:ids)")
                            ->setParameter('ids',$contentArrayOptions['contentType'])
                            ;
                    },
                    "multiple" => $multiple,
                    'attr'=>array(
                        'data-sonata-select2-maximumSelectionSize'=>$contentArrayOptions['restriction']
                    )
                ))
            ;
        }

    }
}