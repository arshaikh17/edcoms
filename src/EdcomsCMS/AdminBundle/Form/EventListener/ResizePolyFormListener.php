<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\AdminBundle\Form\EventListener;

use Infinite\FormBundle\Form\EventListener\ResizePolyFormListener as InfiniteResizePolyFormListener;
use Doctrine\Common\Util\ClassUtils;
use Infinite\FormBundle\Form\Util\LegacyFormUtil;

class ResizePolyFormListener extends InfiniteResizePolyFormListener
{
    /** @var  string */
    protected $typeAttr;

    public function __construct(
        array $prototypes,
        array $options = array(),
        $allowAdd = false,
        $allowDelete = false,
        $typeFieldName = '_type',
        $indexProperty = null,
        $useTypesOptions = false,
        $typeAttr
    ) {
        parent::__construct(
            $prototypes,
            $options,
            $allowAdd,
            $allowDelete,
            $typeFieldName,
            $indexProperty,
            $useTypesOptions
        );
        $this->typeAttr = $typeAttr;
    }

    protected function getTypeForObject($object)
    {
        if($this->typeAttr){
            $type = call_user_func(array($object, sprintf('get%s',ucfirst($this->typeAttr))));

            if (array_key_exists($type, $this->typeMap)) {
                $type = $this->typeMap[$type];
            }else{
                $type = reset($this->typeMap);
            }
            return LegacyFormUtil::getType($type);
        }else{
            return parent::getTypeForObject($object);
        }
    }
}