<?php
namespace EdcomsCMS\ContentBundle\Event;

use EdcomsCMS\ContentBundle\Entity\Rating;
use Symfony\Component\EventDispatcher\Event;

/**
 * RatingEvent object to encapsulate an event relating to the CMS Ratings feature.
 * RatingEvent object is immutable
 *
 * Created by stevenduncan-brown
 * Date: 12/02/2017
 * Time: 19:23
 */
class RatingEvent extends Event
{
    //The rating object
    private $rating;

    /**
     * RatingEvent constructor.
     * @param Rating $rating
     */
    public function __construct(Rating $rating)
    {
        $this->rating = $rating;
    }

    /**
     * Get the Rating object
     *
     * @return Rating
     */
    public function getRating()
    {
        return $this->rating;
    }
}
