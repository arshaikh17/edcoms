<?php
namespace EdcomsCMS\BadgeBundle\Helpers;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use EdcomsCMS\AuthBundle\Entity\cmsUsers;
use EdcomsCMS\BadgeBundle\Entity\BadgeAward;
use EdcomsCMS\BadgeBundle\Entity\BadgeBase;
use EdcomsCMS\BadgeBundle\Event\BadgeEvent;

class BadgeHelper {

    const BADGE_AWARDED_EVENT = 'badge.awarded';

    private $doctrine;
    private $container;

    public function __construct($doctrine, $container) {
        $this->doctrine = $doctrine;
        $this->container = $container;
    }

    /**
     * Get badges awards for a user
     *
     * @param $user - cmsUser
     * @param $em
     * @return array
     */
    public function getBadgeAwardsForUser(cmsUsers $user)
    {
        $badgeAwardRepo = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSBadgeBundle:BadgeAward');
        $badgeAwards = $badgeAwardRepo->findBy(['user'=>$user]);
        $badgeAwardsArray = [];
        if (count($badgeAwards) > 0) {
            foreach ($badgeAwards as $badgeAward) {
                $badgeAwardsArray[] = $badgeAward->toJSON();
            }
        }
        return $badgeAwardsArray;
    }

    /**
     * Get a list of BadgeAwards for a collection of users
     *
     * @param Collection $users - cmsUsers
     * @param $em - Entity Manager
     * @return mixed - Collection of BadgeAwards (empty if non)
     */
    public function getBadgeAwardsForCollection(Collection $users)
    {
        //get user ids for DB query
        $userIds = [];
        foreach ($users as $k => $user) {
            if (!is_a($user, 'EdcomsCMS\AuthBundle\Entity\cmsUsers')) {
                throw new \InvalidArgumentException('Collection passed as param 1 should only contain objects of type EdcomsCMS\AuthBundle\Entity\cmsUsers. Element at key '.$k.' is of type '.get_class($user));
            }
            $userIds[] = $user->getId();
        }

        //search for badgeAwards for the given users
        $badgeAwards = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSBadgeBundle:BadgeAward')->findBy([ 'user' => $userIds]);

        return $badgeAwards;
    }

    /**
     * Award a badge to a user
     *
     * @param $user - cmsUsers
     * @param $badge - BadgeBase
     * @param $dateAchieved - DateTime
     * @param $em - EntityManager
     * @return BadgeAward
     */
    public function awardBadgeToUser(cmsUsers $user, BadgeBase $badge, \DateTime $dateAchieved)
    {
        //create badge
        $badgeAward = new BadgeAward();
        $badgeAward->setUser($user);
        $badgeAward->setBadge($badge);
        $badgeAward->setDateAchieved($dateAchieved);
        $badgeAward->setDateAwarded(new \DateTime());

        //add to db and return badge award
        $em = $this->doctrine->getManager('edcoms_cms');
        $em->persist($badgeAward);
        $em->flush();

        //check badge notifications are enabled in the config
        if ($this->container->hasParameter('badges')) {
            $badgesConfig = $this->container->getParameter('badges');
            if (boolval($badgesConfig['notifications']['active'])) {

                //fire badge.awarded event
                $this->fireBadgeAwardEvent($badgeAward);
            }
        }

        return $badgeAward;
    }

    /**
     * Award all badges on site
     *
     * @param bool|false $recalculate - if true existing badge awards will be deleted before assessment
     * @return array
     */
    public function awardAllBadges($recalculate = false) {

        $em = $this->doctrine->getManager('edcoms_cms');
        //get all badges and loop through
        $badgeRepo = $em->getRepository('EdcomsCMSBadgeBundle:BadgeBase');
        $badges = $badgeRepo->findBy( [ 'isActive'=>true ] );

        $resp = [];
        foreach ($badges as $badge) {
            if ($recalculate) {//delete existing badge awards
                $badgeAwardsRepo = $em->getRepository('EdcomsCMSBadgeBundle:BadgeAward');
                $badgeAwardsRepo->deleteByBadge($badge);
            }

            if ($this->awardBadge($badge)) {
                $resp[$badge->getId()] = 'success';
            } else {
                $resp[$badge->getId()] = 'no users in group';
            }
        }
        return $resp;
    }

    /**
     * Award a badge based on the assessment of the criteria defined within the badge properties
     *
     * @param BadgeBase $badge - badge to assess
     * @param EntityManagerInterface $em
     * @return bool - true if badge is active, false is badge is inactive
     */
    public function awardBadge(BadgeBase $badge)
    {
        //check badge is active
        if ($badge->getIsActive()) {
            //get options
            $action = $badge->getAction();
            $target = $badge->getTarget();
            $multiplier = $badge->getMultiplier();
            $distinct = $badge->getIsDistinct();
            $userGroup = $badge->getCmsUserGroup();
            $users = $userGroup->getUser();

            if (count($users) > 0 ) {
                //group has users
                $activityLogRepo = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:ActivityLog');
                foreach ($users as $user) {

                    //check to see if badge already awarded
                    $badgeAwardRepo = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSBadgeBundle:BadgeAward');
                    $badgeAward = $badgeAwardRepo->findBy([
                        'user' => $user,
                        'badge' => $badge
                    ]);
                    if ($badgeAward) {
                        //badge already awarded
                        continue;
                    }

                    //get logs ordered by date
                    if ($distinct) {//distinct action required use custom repo function
                        $logs = $activityLogRepo->findDistinct($user, $action, $target);
                    } else {
                        $logs = $activityLogRepo->findBy([
                            'user' => $user,
                            'action' => $action,
                            'referenceType' => $target
                        ],
                            ['date' => 'ASC']);
                    }

                    if (count($logs) >= $multiplier) {
                        //number of occurrences meets requirements
                        $badgeAward = new BadgeAward();
                        $badgeAward->setUser($user);
                        $badgeAward->setBadge($badge);
                        $badgeAward->setDateAwarded(new \DateTime());
                        //get date from log entry at the point of achieving badge
                        $achievedLog = $logs[$multiplier - 1];
                        if (is_array($achievedLog)) {
                            //custom repo function will return an array so get the log obj where the badge was achieved
                            $achievedLog = $activityLogRepo->find($achievedLog['id']);
                        }
                        //date achieved is the date of the first qualifying instance in the log table
                        $badgeAward->setDateAchieved($achievedLog->getDate());
                        $em = $this->doctrine->getManager('edcoms_cms');
                        $em->persist($badgeAward);
                        $em->flush();

                        //check badge notifications are enabled in the config
                        if ($this->container->hasParameter('badges')) {
                            $badgesConfig = $this->container->getParameter('badges');
                            if (boolval($badgesConfig['notifications']['active'])) {

                                //fire badge.awarded event
                                $this->fireBadgeAwardEvent($badgeAward);
                            }
                        }
                    }
                }

                return true;
            }

        } else {
            return false;
        }
    }

    /**
     * Fire a new event to notify of a badge award
     *
     * @param $badgeAward
     */
    public function fireBadgeAwardEvent($badgeAward)
    {
        $event = new BadgeEvent();
        $event->setBadgeAward($badgeAward);
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(self::BADGE_AWARDED_EVENT, $event);
    }
}
