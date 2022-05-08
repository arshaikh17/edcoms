<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidVideo extends Constraint
{

    public $message = 'The video "{{ videoString }}" is not a valid video.';

    public function validatedBy()
    {
        return VideoValidator::class;
    }
}