<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\UserBundle\Service;

use Doctrine\ORM\EntityManager;
use EdcomsCMS\UserBundle\Entity\RTBFUserRecord;
use EdcomsCMS\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Class RTBFUserService
 *
 * @package EdcomsCMS\AuthBundle\Service
 */
class RTBFUserService implements RTBFUserServiceInterface {

    const IDENTIFIER_TYPE_ID = 'id';
    const IDENTIFIER_TYPE_EMAIL = 'email';

    private static $userIdentifierTypes = [
        RTBFUserService::IDENTIFIER_TYPE_ID,
        RTBFUserService::IDENTIFIER_TYPE_EMAIL
    ];

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * @var \EdcomsCMS\UserBundle\Service\RTBFUserExtensionPool
     */
    private $RTBFUserExtensionPool;

    /**
     * RTBFUserService constructor.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Symfony\Component\DependencyInjection\Container $container
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationChecker $authorizationChecker
     * @param \EdcomsCMS\UserBundle\Service\RTBFUserExtensionPool $RTBFUserExtensionPool
     */
    public function __construct(
        EntityManager $entityManager,
        Container $container,
        AuthorizationChecker $authorizationChecker,
        RTBFUserExtensionPool $RTBFUserExtensionPool
        ) {
        $this->entityManager = $entityManager;
        $this->container = $container;
        $this->authorizationChecker = $authorizationChecker;
        $this->RTBFUserExtensionPool = $RTBFUserExtensionPool;
    }

    /**
     * @return array
     */
    public function getUserIdentifierTypes() {
        return $this::$userIdentifierTypes;
    }

    /**
     * @param $userId
     * @param $userIdentifierType
     *
     * @return \EdcomsCMS\UserBundle\Entity\User
     */
    public function findUser($userId, $userIdentifierType) {
        $user = null;
        $userRepo = $this->entityManager->getRepository(User::class);
        switch ($userIdentifierType){
            case self::IDENTIFIER_TYPE_ID:
                $user = $userRepo->findOneBy(['id'=>$userId, 'rtbfApplied'=> null]);
                break;
            case self::IDENTIFIER_TYPE_EMAIL:
                $user = $userRepo->findOneBy(['username'=>$userId, 'rtbfApplied'=> null]);
                break;
        }
        return $user;
    }

    /**
     * @param \EdcomsCMS\UserBundle\Entity\User $user
     *
     * @return boolean
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function applyRTBF(User $user) {
        if($this->isRTBFAllowed($user)){

            $actionsOverview = $this->getRTBFActionsOverview($user);

            // Replace username, first name, last name
            $user->setUsername($this->lowerCaseAndHash($user->getUsername()));
            $user->setFirstName(' ');
            $user->setLastName(' ');
            $user->setEmail($this->lowerCaseAndHash($user->getEmail()));
            $user->setEmailCanonical($this->lowerCaseAndHash($user->getEmailCanonical()));
            $user->setPendingEmail('');
            $user->setPendingEmailCanonical('');
            $user->setPreviousEmails([]);
            $user->setEnabled(false);

            $user->setRtbfApplied(true);
            $user->setRtbfAppliedOn(new \DateTime());


            $RTBFUserRecord = new RTBFUserRecord();
            $RTBFUserRecord->setUsername($user->getUsername());
            $RTBFUserRecord->setActionsOverview($actionsOverview);
            $RTBFUserRecord->setUser($user);
            $RTBFUserRecord->setCreatedOn(new \DateTime());

            $this->entityManager->persist($RTBFUserRecord);

            foreach ($this->RTBFUserExtensionPool->getRTBFUserExtensions() as $RTBFUserExtension){
                $RTBFUserExtension->applyRTBF($user);
            }

            $this->entityManager->flush();
            return true;
        }
    }

    /**
     * @param \EdcomsCMS\UserBundle\Entity\User $user
     *
     * @return bool
     */
    public function isRTBFAllowed(User $user) {
        $isAllowed = true;
        $roles = $user->getRoles();
        /** @var \Symfony\Component\Security\Core\Role\RoleInterface $role */
        foreach ($roles as $role) {
            if ($role == "ROLE_SONATA_ADMIN") {
                $isAllowed = FALSE;
                break;
            }
        }

        foreach ($this->RTBFUserExtensionPool->getRTBFUserExtensions() as $RTBFUserExtension){
            $isAllowed = $RTBFUserExtension->isRTBFAllowed($user);
            if($isAllowed == false){
                break;
            }
        }

        if($user->isRtbfApplied()){
            $isAllowed = false;
        }

        return $isAllowed;
    }

    /**
     * @param \EdcomsCMS\UserBundle\Entity\User $user
     *
     * @return array
     */
    public function getUserOverview(User $user) {

        $overviewData = [
            'ID' => $user->getId(),
            'Username' => $user->getUsername(),
            'First name' => $user->getFirstName(),
            'Last name' => $user->getLastName(),
            'Email' => $user->getEmail(),
            'Registration date' => $user->getCreatedAt() ? $user->getCreatedAt()->format('d/m/y, H:i') : '',
            'Last log in date' => $user->getLastLogin() ? $user->getLastLogin()->format('d/m/y, H:i') : ''
        ];

        foreach ($this->RTBFUserExtensionPool->getRTBFUserExtensions() as $RTBFUserExtension){
            $overviewData = array_merge($overviewData, $RTBFUserExtension->getUserOverview($user));
        }
        return $overviewData;
    }

    /**
     * @param \EdcomsCMS\UserBundle\Entity\User $user
     *
     * @return array
     */
    public function getRTBFActionsOverview(User $user){

        $actionsOverviewData = [
            'Hash user' => 'YES'
        ];

        foreach ($this->RTBFUserExtensionPool->getRTBFUserExtensions() as $RTBFUserExtension){
            $actionsOverviewData = array_merge($actionsOverviewData, $RTBFUserExtension->getRTBFActionsOverview($user));
        }

        return $actionsOverviewData;
    }

    /**
     * @param string $email
     *
     * @return \EdcomsCMS\UserBundle\Entity\RTBFUserRecord|false
     */
    public function isRTBFApplied($email) {
        $RTBFUserRecord = $this
            ->entityManager
            ->getRepository(RTBFUserRecord::class)
            ->findOneBy(['username'=>$this->lowerCaseAndHash($email)],['createdOn'=>'DESC']);

        return $RTBFUserRecord ? $RTBFUserRecord : false;
    }

    /**
     * @param $data
     *
     * @return string
     */
    private function hashData($data){
        return hash('sha256', $data);
    }

    private function lowerCaseAndHash($username){
        return $this->hashData(strtolower($username));
    }

}
