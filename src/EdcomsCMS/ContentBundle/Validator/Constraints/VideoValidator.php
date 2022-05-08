<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class VideoValidator extends ConstraintValidator
{

    /** @var EntityManager  */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        if($value && !is_numeric($value)){
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ videoString }}', $value)
                ->addViolation();
            return;
        }
        if ($value && $video=$this->em->getRepository('EdcomsCMSContentBundle:Media')->find($value)) {
            if(!$video || !$video->getVideoId()){
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ videoString }}', $value)
                    ->addViolation();
            }
        }
    }
}