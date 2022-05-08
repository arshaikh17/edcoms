<?php
/**
 * Class to listen for events relating to the CMS Ratings feature
 *
 * User: stevenduncan-brown
 * Date: 12/02/2017
 * Time: 18:13
 */

namespace EdcomsCMS\ContentBundle\EventListener;

use Doctrine\ORM\EntityManager;
use EdcomsCMS\ContentBundle\Event\RatingEvent;
use EdcomsCMS\ContentBundle\Entity\ActivityLog;

class RatingEventListener
{
    private $em;

    /**
     * RatingEventListener constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Listener for the rating.awarded event
     *
     * @param RatingEvent $event
     */
    public function onRatingAwarded(RatingEvent $event)
    {
        //get the rating obj
        $rating = $event->getRating();

        //add activity log details
        $activityLog = new ActivityLog();
        $activityLog->setAction('awarded');
        //add the detail as a json string to give the data more structure
        $detail = array(
            'structure_id' => $rating->getStructure()->getId(),
            'rating' => $rating->getRating()
        );
        $activityLog->setDetail(json_encode($detail));//add the detail as a json string
        $activityLog->setReferenceID($rating->getUser()->getId());
        $activityLog->setReferenceType('user');
        $activityLog->setUser($rating->getUser());
        $activityLog->setDate(new \DateTime());

        //persist activity log to the DB
        $this->em->persist($activityLog);
        $this->em->flush();
    }
}