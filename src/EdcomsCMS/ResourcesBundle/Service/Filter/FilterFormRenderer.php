<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Service\Filter;

use Doctrine\ORM\EntityManager;
use EdcomsCMS\ResourcesBundle\Model\Filter\FilterElement;
use EdcomsCMS\ResourcesBundle\Model\Filter\FilterForm;

class FilterFormRenderer
{

    /** @var EntityManager  */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function render(FilterForm $filterForm){
        /** @var FilterElement $element */
        foreach ($filterForm->getElements() as $element){
            if($element->isEntityFilter()){
                $elementChoices= $this->em->getRepository($element->getDataSourceClass()->getName())->findBy(
                    ['useAsFilter' => true]
                );
                $choices = [];
                foreach ($elementChoices as $elementChoice){
                    $choices[$elementChoice->getFilterValue()] = $elementChoice->getFilterLabel();
                }
                $element->setChoices($choices);
            }
        }

        return $filterForm;
    }
}