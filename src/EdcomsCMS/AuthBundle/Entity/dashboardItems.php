<?php

namespace EdcomsCMS\AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * dashboard_items
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\AuthBundle\Entity\dashboardItemsRepository")
 */
class dashboardItems
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=128)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="refresh_rate", type="integer")
     */
    private $refreshRate;

    /**
     * @var string
     *
     * @ORM\Column(name="controller", type="string", length=80)
     */
    private $controller;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return dashboard_items
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return dashboardItems
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set refreshRate
     *
     * @param integer $refreshRate
     * @return dashboardItems
     */
    public function setRefreshRate($refreshRate)
    {
        $this->refreshRate = $refreshRate;

        return $this;
    }

    /**
     * Get refreshRate
     *
     * @return integer 
     */
    public function getRefreshRate()
    {
        return $this->refreshRate;
    }

    /**
     * Set controller
     *
     * @param string $controller
     * @return dashboardItems
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Get controller
     *
     * @return string 
     */
    public function getController()
    {
        return $this->controller;
    }
}
