<?php
namespace EdcomsCMS\ContentBundle\Helpers;

use EdcomsCMS\BadgeBundle\Event\RatingEvent;
use EdcomsCMS\BadgeBundle\Event\RatingEvents;
use EdcomsCMS\ContentBundle\Entity\Structure;
use EdcomsCMS\AuthBundle\Entity\cmsUsers;
use EdcomsCMS\ContentBundle\Entity\Rating;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class RatingHelper
{
    //Needs to be refactored to be site config, currently not possible to do as this figure is needed in other helpers
    //minimum number of ratings a structure must have before average is displayed on front office
    const RATINGS_MINIMUM_LIMIT = 5;

    // initialise a new Rating object with a specific Structure object \\
    /**
     *
     * @var Structure
     */
    protected $structure;
    protected $ratings;
    /**
     *
     * @var cmsUsers
     */
    protected $user;
    protected $em;
    public function __construct($doctrine, Structure $structure)
    {
        $this->structure = $structure;
        $this->em = $doctrine->getManager('edcoms_cms');
        $ratings = $this->em->getRepository('EdcomsCMSContentBundle:Rating');
        $this->ratings = $ratings->findBy(['structure'=>$structure]);
    }

    public function GetAverage($mode = 'mean')
    {
        $total = 0;
        $count = count($this->ratings);
        $average = 0;
        foreach ($this->ratings as $rating) {
            if ($mode === 'mean') {
                $total += $rating->getRating();
            }
        }
        if ($mode === 'mean') {
            if ($count !== 0) {
                $average = $total / $count;
            }
        }
        return $average;
    }

    public function GetMyRatings(AdvancedUserInterface $user)
    {
        $this->user = $user;
        return array_filter($this->ratings, [&$this, 'MyRatings']);
    }

    /**
     * Add a Rating to the Structure defined in this constructor.
     * If User has already rated this Structure the old rating will be overwritten.
     *
     * @param int $rating
     * @param string $user
     * @return Rating
     */
    public function AddRating($rating, $user = 'anon.')
    {
        //check to see if user has already rated this structure
        //if rating already exists we update provious, if not
        //a new rating should be created
        if ($user !== 'anon.') {
            $ratings = $this->em->getRepository('EdcomsCMSContentBundle:Rating');
            $ratingObj = $ratings->findOneByUserAndStructure($user, $this->structure);
            if (!$ratingObj) {
                $ratingObj = new Rating();
                $ratingObj->setUser($user);
                $ratingObj->setStructure($this->structure);
            }
        }


        $ratingObj->setRating($rating);
        $ratingObj->setAddedOn(new \DateTime());
        // append this new rating to the stored one here \\
        $this->ratings[] = $ratingObj;
        $this->em->persist($ratingObj);
        $this->em->flush();

        return $ratingObj;
    }

    private function MyRatings(Rating $rating)
    {
        if ($rating->getUser() !== null && $this->user !== null) {
            return $rating->getUser()->getId() === $this->user->getId();
        }
        
        return false;
    }

    /**
     * Get the count of the number of ratings for the currently set Structure
     *
     * @return int
     */
    public function getNumberOfRatings()
    {
        return count($this->ratings);
    }
}
