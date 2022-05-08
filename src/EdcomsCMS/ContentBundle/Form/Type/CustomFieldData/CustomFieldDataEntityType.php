<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData;

use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value\CustomFieldDataValueEntityType;
use EdcomsCMS\ContentBundle\Service\Content\ContentService;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Constraints\Valid;
use EdcomsCMS\ContentBundle\Entity\CustomFields;

class CustomFieldDataEntityType extends CustomFieldDataType
{

    /**
     * @var ContentService
     */
    private $contentService;

    public function __construct(TokenStorage $tokenStorage, ContentService $contentService)
    {
        parent::__construct($tokenStorage);
        $this->contentService = $contentService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm( $builder, $options);
        $builder->add('_type',HiddenType::class,array(
            "data"=> 'entity_type',
            "mapped"=> false
        ));

        /** @var CustomFields $customField */
        $customField = $options['customField'];
        $class = $this->contentService->getExtraCustomDataEntities()[$customField->getOptions()]['class'];
        $builder->add('value', CustomFieldDataValueEntityType::class,
            array(
                "label" => false,
                "required" => false,
                "class" => $class
            ) );
    }
}