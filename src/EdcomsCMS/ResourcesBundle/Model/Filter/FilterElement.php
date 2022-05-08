<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Model\Filter;


class FilterElement
{

    const FILTER_TYPE_LIST = 'list';

    /** @var string */
    private $label;

    /** @var string */
    private $slug;

    /** @var string */
    private $type;

    private $choices;

    /** @var \ReflectionClass  */
    private $dataSourceClass;

    /** @var  FilterConfig */
    private $config;

    /** @var array */
    private $selectedValues=[];

    public function __construct($label, $slug, $type, $dataSource)
    {
        $this->label = $label;
        $this->slug = $slug;
        $this->setType($type);
        $this->config = new FilterConfig();
        if(is_array($dataSource)){
            $this->choices = $dataSource;
        }elseif (is_string($dataSource)){
            $dataSourceClass = new \ReflectionClass($dataSource);
            if(!$dataSourceClass->implementsInterface(FilterableEntityInterface::class)){
                throw new \Exception(sprintf('Filter "%s" is not valid. Class "%s" must implement "%s"',$label, $dataSource, FilterableEntityInterface::class));
            }
            $this->dataSourceClass = $dataSourceClass;
        }else{
            throw new \Exception(sprintf('Datasource for Filter "%s" is not valid. It must be either an array or a Doctrine entity that implements "%s"', $label, FilterableEntityInterface::class));
        }
    }

    public function setConfigDisplayAll(bool $flag){
        $this->config->setDisplayAll($flag);
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @throws \Exception
     */
    public function setType(string $type)
    {
        if(!in_array($type,[self::FILTER_TYPE_LIST])){
            throw new \Exception(sprintf('Filter type %s not exist',$type));
        }
        $this->type = $type;
    }

    public function isEntityFilter(){
        return $this->dataSourceClass ? true : false;
    }

    /**
     * @param array $choices
     */
    public function setChoices(array $choices)
    {
        $this->choices = $choices;
    }

    /**
     * @return \ReflectionClass
     */
    public function getDataSourceClass()
    {
        return $this->dataSourceClass;
    }

    /**
     * @return array
     */
    public function getSelectedValues()
    {
        return $this->selectedValues;
    }

    /**
     * @param array $selectedValues
     */
    public function setSelectedValues(array $selectedValues)
    {
        $this->selectedValues = $selectedValues;
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @return FilterConfig
     */
    public function getConfig()
    {
        return $this->config;
    }



}