<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Validator\Constraints;

use AppBundle\Entity\Survey\WUYF\WUYFSurvey;
use Doctrine\ORM\EntityManager;
use EdcomsCMS\ContentBundle\Entity\URLRedirect;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidRedirectValidator extends ConstraintValidator
{

    public function validate($redirect, Constraint $constraint)
    {
        /** @var URLRedirect $redirect */
        if ($redirect->getUrl()==$redirect->getRedirectPath() ) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ error }}', 'Redirect URL  cannot match with the destination URL')
                ->atPath('collectionDate')
                ->addViolation();

        }
    }
}