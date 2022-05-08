<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationInstance
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\NotificationInstanceRepository")
 */
class NotificationInstance
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
     * @var cmsUsers
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUsers")
     * @ORM\JoinColumn(name="userid", referencedColumnName="id")
     */
    private $user;

    /**
     * @var Notification
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\Notification")
     * @ORM\JoinColumn(name="notificationid", referencedColumnName="id")
     */
    private $notification;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_issued", type="datetime")
     */
    private $dateIssued;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_seen", type="datetime", nullable=true)
     */
    private $dateSeen;

    /**
     * @var ArrayCollection
     *
     * @ORM\Column(name="data", type="json_array")
     */
    private $data;

    public function __construct()
    {
        $this->dateIssued = new \DateTime();
        $this->data = new ArrayCollection();
    }

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
     * Set dateIssued
     *
     * @param \DateTime $dateIssued
     *
     * @return NotificationInstance
     */
    public function setDateIssued($dateIssued)
    {
        $this->dateIssued = $dateIssued;

        return $this;
    }

    /**
     * Get dateIssued
     *
     * @return \DateTime
     */
    public function getDateIssued()
    {
        return $this->dateIssued;
    }

    /**
     * Set dateSeen
     *
     * @param \DateTime $dateSeen
     *
     * @return NotificationInstance
     */
    public function setDateSeen($dateSeen)
    {
        $this->dateSeen = $dateSeen;

        return $this;
    }

    /**
     * Get dateSeen
     *
     * @return \DateTime
     */
    public function getDateSeen()
    {
        return $this->dateSeen;
    }

    /**
     * Set user
     *
     * @param $user
     *
     * @return NotificationInstance
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return cmsUsers
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set notification
     *
     * @param Notification $notification
     *
     * @return NotificationInstance
     */
    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification
     *
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set data
     *
     * @param ArrayCollection $data
     *
     * @return SiteNotificationInstance
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Add an item to the data array
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function addData($key, $value)
    {
        $this->data->set($key,$value);

        return $this;
    }

    /**
     * Remove an item of data by its index
     *
     * @param $key
     */
    public function removeDataByKey($key)
    {
        $this->info->remove($key);
    }

    /**
     * Remove an item of data
     *
     * @param $value
     */
    public function removeData($value)
    {
        $this->info->removeElement($value);
    }

    /**
     * Return this object as a JSON friendly array
     *
     * @param array $vars
     * @return array
     */
    public function toJSON($vars = array())
    {
        //not properties were specificed
        if (empty($vars)) {
            $vars = get_object_vars($this);

            //remove any properties beginning with an underscore __
            $vars = array_filter($vars, array(&$this, 'jsonFilter'), ARRAY_FILTER_USE_BOTH);
        }

        $returnArray = [];
        foreach ($vars as $property => $value) {
            $returnArray[$property] = (is_object($this->{$property}) && method_exists($this->{$property}, 'toJSON')) ? $this->{$property}->toJSON() : $this->{$property};
        }

        return $returnArray;
    }

    /**
     * filter out array elements beginning with 2 underscores
     *
     * @param $value
     * @param $key
     * @return bool
     */
    private function jsonFilter($value, $key)
    {
        if (!strstr($key, '__')) {
            return true;
        }
        return false;
    }
}
