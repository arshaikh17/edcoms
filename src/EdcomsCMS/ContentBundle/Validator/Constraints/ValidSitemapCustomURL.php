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
class ValidSitemapCustomURL extends Constraint
{

    public $message = 'The video "{{ videoString }}" is not a valid video.';

    public function validatedBy()
    {
        return SitemapCustomURLValidator::class;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}