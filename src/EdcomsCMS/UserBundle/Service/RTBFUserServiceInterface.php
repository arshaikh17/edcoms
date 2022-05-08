<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\UserBundle\Service;

use EdcomsCMS\UserBundle\Entity\User;

interface RTBFUserServiceInterface {

    /**
     *
     * @return array
     */
    public function getUserIdentifierTypes();

    /**
     * @param $userId
     * @param $userIdentifierType
     *
     * @return \EdcomsCMS\UserBundle\Entity\User
     */
    public function findUser($userId, $userIdentifierType);

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
     * @return boolean
     */
    public function isRTBFAllowed(User $user);

    /**
     * @param \EdcomsCMS\UserBundle\Entity\User $user
     *
     * @return boolean
     */
    public function applyRTBF(User $user);

    /**
     * @param string $email
     *
     * @return true
     */
    public function isRTBFApplied($email);


}