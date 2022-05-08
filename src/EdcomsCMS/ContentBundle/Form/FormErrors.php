<?php
namespace EdcomsCMS\ContentBundle\Form;

class FormErrors
{
    public function getArray(\Symfony\Component\Form\Form $form)
    {
        return $this->getErrors($form);
    }

    private function getErrors($form)
    {
        $errors = [];

        if ($form instanceof \Symfony\Component\Form\Form) {

            foreach ($form->getErrors() as $error) {

                $errors[] = $error->getMessage();
            }

            foreach ($form->all() as $key => $child) {
                /** @var $child \Symfony\Component\Form\Form */
                if ($err = $this->getErrors($child)) {
                    $errors[$key] = $err;
                }
            }
        }
        
        return $errors;
    }
}