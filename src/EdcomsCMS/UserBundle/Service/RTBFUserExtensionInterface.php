<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\UserBundle\Service;


use EdcomsCMS\UserBundle\Entity\User;

interface RTBFUserExtensionInterface {

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager();

    /**
     * @param \EdcomsCMS\UserBundle\Entity\User $user
     *
     * @return mixed
     */
    public function applyRTBF(User $user);

    /**
     * Returns a key/value array including the user's properties
     * @param \EdcomsCMS\UserBundle\Entity\User $user
     *
     * @return array
     */
    public function getUserOverview(User $user);

    /**
     * Returns a key/value array summarizing the data that RTBF will be applied to
     * @param \EdcomsCMS\UserBundle\Entity\User $user
     *
     * @return array
     */
    public function getRTBFActionsOverview(User $user);

    /**
     * @param \EdcomsCMS\UserBundle\Entity\User $user
     *
     * @return bool
     */
    public function isRTBFAllowed(User $user);
}
