<?php

namespace EdcomsCMS\AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Criteria;

/**
 * Connector
 *
 * @ORM\Table(name="Connector")
 * @ORM\Entity(repositoryClass="EdcomsCMS\AuthBundle\Entity\ConnectorRepository")
 */
class Connector
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
     * @ORM\Column(name="site", type="string", length=200, unique=true)
     */
    private $site;
    
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ConnectorHook", mappedBy="connector")
     */
    private $hooks;
    
    
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ConnectorKey", mappedBy="connector", cascade={"persist"})
     */
    private $keys;


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
     * Set site
     *
     * @param string $site
     *
     * @return Connector
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return string
     */
    public function getSite()
    {
        return $this->site;
    }
    
    /**
     * 
     * @return ArrayCollection
     */
    public function getHooks()
    {
        return $this->hooks;
    }
    
    /**
     * 
     * @param string $type
     * @return \EdcomsCMS\AuthBundle\Entity\ConnectorHook
     */
    public function getDefaultHook($type=null)
    {
        $criteria = Criteria::create()
                ->where(Criteria::expr()->eq('type', $type))
                ->andWhere(Criteria::expr()->eq('default', true));
        
        $hook = $this->getHooks()->matching($criteria);
        
        return $hook->first();
    }
    
    public function addHook(ConnectorHook $hook, $recurs=true)
    {
        $this->hooks->add($hook);
        if ($recurs) {
            $hook->setConnector($this, false);
        }
        return $this;
    }
    
    /**
     * 
     * @param ArrayCollection $hooks
     * @return Connector
     */
    public function setHooks($hooks)
    {
        foreach ($hooks as $hook) {
            $this->addHook($hook, true);
        }
        return $this;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hooks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->keys = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Remove hook
     *
     * @param \EdcomsCMS\AuthBundle\Entity\ConnectorHook $hook
     */
    public function removeHook(\EdcomsCMS\AuthBundle\Entity\ConnectorHook $hook)
    {
        $this->hooks->removeElement($hook);
    }
    
    /**
     * 
     * @return ArrayCollection
     */
    public function getKeys()
    {
        return $this->keys;
    }
    
    public function addKey(ConnectorKey $key, $recurs=true)
    {
        $this->keys->add($key);
        if ($recurs) {
            $key->setConnector($this, false);
        }
        return $this;
    }
    
    /**
     * 
     * @param ArrayCollection $keys
     * @return ConnectorHook
     */
    public function setKeys($keys)
    {
        foreach ($keys as $key) {
            $this->addKey($key, true);
        }
        return $this;
    }

    /**
     * Remove key
     *
     * @param \EdcomsCMS\AuthBundle\Entity\ConnectorKey $key
     */
    public function removeKey(\EdcomsCMS\AuthBundle\Entity\ConnectorKey $key)
    {
        $this->keys->removeElement($key);
    }
}
