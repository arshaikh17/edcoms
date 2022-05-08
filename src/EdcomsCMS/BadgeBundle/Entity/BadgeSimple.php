<?php
/**
 * Created by PhpStorm.
 * User: stevenduncan-brown
 * Date: 05/10/2016
 * Time: 15:19
 */

namespace EdcomsCMS\BadgeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BadgeSimple
 * @package EdcomsCMS\BadgeBundle\Entity
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\BadgeBundle\Entity\BadgeSimpleRepository")
 */
class BadgeSimple extends BadgeBase
{
    /**
     * Action to take to get badge
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255)
     */
    private $action;

    /**
     * The target to apply action to to get badge
     * @var string
     *
     * @ORM\Column(name="target", type="string", length=255)
     */
    private $target;

    /**
     * The number of times action must be applied to target to get badge
     * @var integer
     *
     * @ORM\Column(name="multiplier", type="integer")
     */
    private $multiplier;

    /**
     * Whether the action is to be counted against distinct targets.
     * e.g. if badge is for playing 10 videos, should this be for 10 distinct videos,
     * or can the same video count more than once.
     * @var boolean
     *
     * @ORM\Column(name="isDistinct", type="boolean", nullable=true)
     */
    private $isDistinct;

    /**
     * Set action
     *
     * @param string $action
     *
     * @return BadgeSimple
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set target
     *
     * @param string $target
     *
     * @return BadgeSimple
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
     * Set multiplier
     *
     * @param integer $multiplier
     *
     * @return BadgeSimple
     */
    public function setMultiplier($multiplier)
    {
        $this->multiplier = $multiplier;

        return $this;
    }

    /**
     * Get multiplier
     *
     * @return integer
     */
    public function getMultiplier()
    {
        return $this->multiplier;
    }

    /**
     * Set isDistinct
     *
     * @param boolean $isDistinct
     *
     * @return BadgeSimple
     */
    public function setIsDistinct($isDistinct)
    {
        $this->isDistinct = $isDistinct;

        return $this;
    }

    /**
     * Get isDistinct
     *
     * @return boolean
     */
    public function getIsDistinct()
    {
        return $this->isDistinct;
    }
}
