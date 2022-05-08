<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\UserBundle\Entity;

/**
 * RTBFUserRecord
 */
class RTBFUserRecord {

    /**
     * @var integer
     *
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var \DateTime
     */
    private $createdOn;

    /**
     * @var array
     */
    private $actionsOverview;

    /**
     * @var \EdcomsCMS\UserBundle\Entity\User
     */
    private $user;

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
     * Set username
     *
     * @param string $username
     *
     * @return RTBFUserRecord
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set actionsOverview
     *
     * @param array $actionsOverview
     *
     * @return RTBFUserRecord
     */
    public function setActionsOverview($actionsOverview)
    {
        $this->actionsOverview = $actionsOverview;

        return $this;
    }

    /**
     * Get actionsOverview
     *
     * @return array
     */
    public function getActionsOverview()
    {
        return $this->actionsOverview;
    }

    /**
     * Set createdOn
     *
     * @param \DateTime $createdOn
     *
     * @return RTBFUserRecord
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * Get createdOn
     *
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @return \EdcomsCMS\UserBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param \EdcomsCMS\UserBundle\Entity\User $user
     */
    public function setUser(\EdcomsCMS\UserBundle\Entity\User $user) {
        $this->user = $user;
    }


}
