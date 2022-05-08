<?php
namespace EdcomsCMS\BadgeBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Created by PhpStorm.
 * User: stevenduncan-brown
 * Date: 12/02/2017
 * Time: 19:23
 */
class BadgeEvent extends Event
{
    private $badgeAward;

    public function setBadgeAward($badgeAward)
    {
        $this->badgeAward = $badgeAward;
    }

    public function getBadgeAward()
    {
        return $this->badgeAward;
    }
}