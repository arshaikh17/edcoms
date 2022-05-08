<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use EdcomsCMS\AuthBundle\Entity\cmsUsers;
use EdcomsCMS\AuthBundle\Entity\cmsUserGroups;

/**
 * Notification
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\NotificationRepository")
 */
class Notification
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
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="info", type="string", length=2000)
     */
    private $info;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", length=50)
     */
    private $target;

    /**
     * @var string
     *
     * @ORM\Column(name="recipient", type="string", length=50)
     */
    private $recipient;

    /**
     * @var cmsUsers
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUsers")
     * @ORM\JoinColumn(name="userid", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @var cmsUserGroups
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUserGroups")
     * @ORM\JoinColumn(name="groupid", referencedColumnName="id", nullable=true)
     */
    private $group;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=250, nullable=true)
     */
    private $url;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime")
     */
    private $dateAdded;

    public function __construct()
    {
        $date = new \DateTime();
        $this->setDateAdded($date);
        $this->info = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Notification
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this->id;
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
     * Set name
     *
     * @param string $name
     *
     * @return Notification
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set info
     *
     * @param ArrayCollection $info
     *
     * @return Notification
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Get info
     *
     * @return ArrayCollection
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Add an item to the info array
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function addInfo($key, $value)
    {
        $this->info->set($key,$value);

        return $this;
    }

    /**
     * Check to see if an element exista at the given key
     *
     * @param $key
     * @return bool
     */
    public function hasInfoByKey($key)
    {
        return $this->info->containsKey($key);
    }

    /**
     * Get an element by key
     *
     * @param $key
     * @return mixed|null
     */
    public function getInfoByKey($key)
    {
        return $this->info->get($key);
    }

    /**
     * Remove an item of info by its index
     *
     * @param $key
     */
    public function removeInfoByKey($key)
    {
        $this->info->remove($key);
    }

    /**
     * Remove an item of info
     *
     * @param $value
     */
    public function removeInfo($value)
    {
        $this->info->removeElement($value);
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Notification
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

    /**
     * Set target
     *
     * @param string $target
     *
     * @return Notification
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set recipient
     *
     * @param string $recipient
     *
     * @return Notification
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get recipient
     *
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Notification
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
     * Set dateAdded
     *
     * @param \DateTime $dateAdded
     *
     * @return Notification
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * Set user
     *
     * @param cmsUsers $user
     *
     * @return Notification
     */
    public function setUser(cmsUsers $user = null)
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
     * Set group
     *
     * @param cmsUserGroups $group
     *
     * @return Notification
     */
    public function setGroup(cmsUserGroups $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return cmsUserGroups
     */
    public function getGroup()
    {
        return $this->group;
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
