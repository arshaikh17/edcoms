<?php

namespace EdcomsCMS\ContentBundle\Helpers;

class UserGeneratedContentValidator
{
    /**
     * Returns boolean value to determine if '$value' can be validated against a validation method derived by '$type'.
     * 'true' is returned by default if no validation method has been defined.
     * @param   string      $type   The type of field. Used to derived the validation method to call.
     * @param   mixed       $value  Value of the field to validate.
     * @return  boolean             'true' if the value is valid for the type defined.
     */
    public function validateField($type, $value)
    {
        $result = true;
        $validateMethod = 'validate' . ucfirst($type);
        
        if (method_exists($this, $validateMethod)) {
            $result = $this->{$validateMethod}($value);
        }
        
        return $result;
    }
    
    /**
     * Returns 'true' if '$value' is a valid number.
     * @param   type        $value  Value of the field to validate.
     * @return  boolean             'true' if the value is valid number.  
     */
    private function validateNumber($value)
    {
        return is_numeric($value);
    }
}
