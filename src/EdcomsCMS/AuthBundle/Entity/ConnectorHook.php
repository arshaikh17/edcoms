<?php

namespace EdcomsCMS\AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConnectorHook
 *
 * @ORM\Table(name="ConnectorHook")
 * @ORM\Entity(repositoryClass="EdcomsCMS\AuthBundle\Entity\ConnectorHookRepository")
 */
class ConnectorHook
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=200, unique=true)
     */
    private $url;
    
    
    /**
     *
     * @var boolean
     * @ORM\Column(name="is_default", type="boolean")
     */
    private $default;
    
    /**
     *
     * @var string
     * @ORM\Column(name="type", type="string", length=20, nullable=true)
     */
    private $type;
    
    /**
     *
     * @var \EdcomsCMS\AuthBundle\Entity\Connector
     * @ORM\ManyToOne(targetEntity="Connector")
     */
    private $connector;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return ConnectorHook
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * 
     * @param \EdcomsCMS\AuthBundle\Entity\Connector $connector
     * @param bool $recurs
     * @return ConnectorHook
     */
    public function setConnector(Connector $connector, $recurs=false)
    {
        if ($recurs) {
            $connector->addHook($this);
        }
        $this->connector = $connector;
        return $this;
    }
    
    /**
     * 
     * @return \EdcomsCMS\AuthBundle\Entity\Connector
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * Set default
     *
     * @param boolean $default
     *
     * @return ConnectorHook
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Get default
     *
     * @return boolean
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return ConnectorHook
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
