<?php
/**
 * Created by PhpStorm.
 * User: stevenduncan-brown
 * Date: 05/10/2016
 * Time: 15:19
 */

namespace EdcomsCMS\BadgeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use EdcomsCMS\AuthBundle\Entity\cmsUsers;
use EdcomsCMS\BadgeBundle\Entity\BadgeBase;

/**
 * Class BadgeAward
 * @package EdcomsCMS\BadgeBundle\Entity
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\BadgeBundle\Entity\BadgeAwardRepository")
 */
class BadgeAward
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
     * User badge is awarded to
     * @var cmsUsers
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUsers")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    private $user;

    /**
     * Badge that has been awarded
     * @var BadgeBase
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\BadgeBundle\Entity\BadgeBase")
     * @ORM\JoinColumn(name="badgeID", referencedColumnName="id")
     */
    private $badge;

    /**
     * Date the badge was awarded
     * @var \DateTime
     *
     * @ORM\Column(name="dateAwarded", type="datetime", nullable=false)
     */
    private $dateAwarded;

    /**
     * Date the badge was achieved (could be different from awarded)
     * @var \DateTime
     *
     * @ORM\Column(name="dateAchieved", type="datetime", nullable=false)
     */
    private $dateAchieved;

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
     * Set dateAwarded
     *
     * @param \DateTime $dateAwarded
     *
     * @return BadgeAward
     */
    public function setDateAwarded($dateAwarded)
    {
        $this->dateAwarded = $dateAwarded;

        return $this;
    }

    /**
     * Get dateAwarded
     *
     * @return \DateTime
     */
    public function getDateAwarded()
    {
        return $this->dateAwarded;
    }

    /**
     * Set dateAchieved
     *
     * @param \DateTime $dateAchieved
     *
     * @return BadgeAward
     */
    public function setDateAchieved($dateAchieved)
    {
        $this->dateAchieved = $dateAchieved;

        return $this;
    }

    /**
     * Get dateAchieved
     *
     * @return \DateTime
     */
    public function getDateAchieved()
    {
        return $this->dateAchieved;
    }

    /**
     * Set user
     *
     * @param cmsUsers $user
     *
     * @return BadgeAward
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
     * Set badge
     *
     * @param BadgeBase $badge
     *
     * @return BadgeAward
     */
    public function setBadge(BadgeBase $badge = null)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * Get badge
     *
     * @return BadgeBase
     */
    public function getBadge()
    {
        return $this->badge;
    }

    public function toJSON($vars=[]) {
        unset($this->json);
        if (empty($vars)) {
            $this->json = get_object_vars($this);

            $this->json['user'] = (!is_array($this->json['user'])) ? $this->getUser()->toJSON() : $this->getUser();
            $this->json['badge'] = (!is_array($this->json['badge'])) ? $this->getBadge()->toJSON() : $this->getBadge();
            // remove anything with an _ \\
            $this->json = array_filter($this->json, array(&$this, 'json_filter'), ARRAY_FILTER_USE_BOTH);
            unset($this->json['dateAwarded']);
            unset($this->json['dateAchieved']);
            return $this->json;
        }
        $obj = [];
        foreach ($vars as $prop) {
            $obj[$prop] = (is_object($this->{$prop}) && method_exists($this->{$prop}, 'toJSON')) ? $this->{$prop}->toJSON() : $this->{$prop};
        }
        return $obj;
    }
    public function json_filter($val, $key) {
        if (!strstr($key, '__')) {
            return true;
        }
        return false;
    }
}
