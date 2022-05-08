<?php

namespace EdcomsCMS\AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * cmsUserDashboard
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\AuthBundle\Entity\cmsUserDashboardRepository")
 */
class cmsUserDashboard
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
     * @var integer
     *
     * @ORM\Column(name="userID", type="integer")
     */
    private $userID;

    /**
     * @var integer
     *
     * @ORM\Column(name="dashboardID", type="integer")
     */
    private $dashboardID;

    /**
     * @var string
     *
     * @ORM\Column(name="item_position", type="decimal")
     */
    private $itemPosition;

    /**
     * @var string
     *
     * @ORM\Column(name="item_size", type="decimal")
     */
    private $itemSize;

    
    /**
     * @var object
     * @ORM\ManyToOne(targetEntity="cmsUsers", inversedBy="dashboard")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @var object 
     * @ORM\ManyToOne(targetEntity="dashboardItems")
     * @ORM\JoinColumn(name="dashboardID", referencedColumnName="id")
     */
    private $dashboard_item;
    
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
     * Set userID
     *
     * @param integer $userID
     * @return cmsUserDashboard
     */
    public function setUserID($userID)
    {
        $this->userID = $userID;

        return $this;
    }

    /**
     * Get userID
     *
     * @return integer 
     */
    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * Set dashboardID
     *
     * @param integer $dashboardID
     * @return cmsUserDashboard
     */
    public function setDashboardID($dashboardID)
    {
        $this->dashboardID = $dashboardID;

        return $this;
    }

    /**
     * Get dashboardID
     *
     * @return integer 
     */
    public function getDashboardID()
    {
        return $this->dashboardID;
    }

    /**
     * Set itemPosition
     *
     * @param string $itemPosition
     * @return cmsUserDashboard
     */
    public function setItemPosition($itemPosition)
    {
        $this->itemPosition = $itemPosition;

        return $this;
    }

    /**
     * Get itemPosition
     *
     * @return string 
     */
    public function getItemPosition()
    {
        return $this->itemPosition;
    }

    /**
     * Set itemSize
     *
     * @param string $itemSize
     * @return cmsUserDashboard
     */
    public function setItemSize($itemSize)
    {
        $this->itemSize = $itemSize;

        return $this;
    }

    /**
     * Get itemSize
     *
     * @return string 
     */
    public function getItemSize()
    {
        return $this->itemSize;
    }
    
    public function getDashboardItem() {
        return $this->dashboard_item;
    }

    /**
     * Set user
     *
     * @param \EdcomsCMS\AuthBundle\Entity\cmsUsers $user
     *
     * @return cmsUserDashboard
     */
    public function setUser(\EdcomsCMS\AuthBundle\Entity\cmsUsers $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \EdcomsCMS\AuthBundle\Entity\cmsUsers
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set dashboardItem
     *
     * @param \EdcomsCMS\AuthBundle\Entity\dashboardItems $dashboardItem
     *
     * @return cmsUserDashboard
     */
    public function setDashboardItem(\EdcomsCMS\AuthBundle\Entity\dashboardItems $dashboardItem = null)
    {
        $this->dashboard_item = $dashboardItem;

        return $this;
    }
}
