<?php
namespace EdcomsCMS\ContentBundle\Traits;

/**
 * Description of EntityHydration
 *
 * @author richard
 */
trait EntityHydration {
    public function __construct($hydration=[])
    {
        if (!empty($hydration) && is_array($hydration)) {
            foreach ($hydration as $key=>$value) {
                $method = 'set'.str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
                if (method_exists($this, $method)) {
                    $this->$method($value);
                }
            }
        }
    }
    public function fromArray($data=array())
    {
        foreach ($data as $name=>$val) {
            if (method_exists($this, 'set'.ucfirst($name))) {
                $method = 'set'.ucfirst($name);
                $this->{$method}($val);
            }
        }
    }
    public function toJSON()
    {
        
    }
}