<?php

namespace EdcomsCMS\AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConnectorKey
 *
 * @ORM\Table(name="ConnectorKey")
 * @ORM\Entity(repositoryClass="EdcomsCMS\AuthBundle\Entity\ConnectorKeyRepository")
 */
class ConnectorKey
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
     * @ORM\Column(name="private_key", type="text")
     */
    private $privateKey;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key", type="text")
     */
    private $apiKey;

    /**
     * @var string
     *
     * @ORM\Column(name="access", type="string", length=12)
     */
    private $access;
    
    /**
     *
     * @var Connector
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
     * Set apiKey
     *
     * @param string $apiKey
     *
     * @return ConnectorKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set access
     *
     * @param string $access
     *
     * @return ConnectorKey
     */
    public function setAccess($access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * Get access
     *
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
    }
    
    /**
     * 
     * @param \EdcomsCMS\AuthBundle\Entity\Connector $connector
     * @param bool $recurs
     * @return ConnectorKey
     */
    public function setConnector(Connector $connector, $recurs=false)
    {
        if ($recurs) {
            $connector->addKey($this);
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
     * Set privateKey
     *
     * @param string $privateKey
     *
     * @return ConnectorKey
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * Get privateKey
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }
}
