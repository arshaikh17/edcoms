<?php

namespace EdcomsCMS\ContentBundle\Helpers;
use EdcomsCMS\AuthBundle\Entity\cmsUsers;
use EdcomsCMS\ContentBundle\Entity\ActivityLog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Reuseable activity tasks
 *
 * @author Steve DB
 */
class ActivityHelper {

    private $message = '';
    private $status = 0;
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Check if the activity denoted in the POST vars in a request are allowed to be logged on current site
     *
     * @param Request $request
     * @return bool
     */
    public function checkActivityAllowed(Request $request)
    {
        //check data
        $allowed = $this->container->getParameter('activity_logging');//allowed activity types, actions & details
        //Is type allowed
        $type = $request->get('type');
        if (empty($type)) {
            $this->message = 'type of activity must be supplied';
            $this->status = JsonResponse::HTTP_BAD_REQUEST;
            return false;

        } elseif (!array_key_exists($type, $allowed)) {
            $this->message = 'site is not configured to log this activity type';
            $this->status = JsonResponse::HTTP_FORBIDDEN;
            return false;
        }

        //Is action allowed for this type
        $action = $request->get('action');
        if (empty($action)) {
            $this->message = 'action must be supplied';
            $this->status = JsonResponse::HTTP_BAD_REQUEST;
            return false;

        } elseif (!in_array($action, $allowed[$type])) {
            $this->message = 'site is not configured to log this action for this activity type';
            $this->status = JsonResponse::HTTP_FORBIDDEN;
            return false;
        }

        //Is detail supplied e.g. video name
        $detail = $request->get('detail');
        if (empty($detail)) {
            $this->message = 'detail must be supplied';
            $this->status = JsonResponse::HTTP_BAD_REQUEST;
            return false;
        }

        return true;
    }

    /**
     * Add an entry to the site logging table.
     * If site has spirit enabled also send activity to spirit
     *
     * @param $type
     * @param $action
     * @param $detail
     * @param $resourceId
     * @param $user
     * @return bool
     */
    public function recordActivity($type, $action, $detail, $resourceId, cmsUsers $user)
    {
        //all good add log entry to database
        $logging = new ActivityLog();
        $logging->setReferenceType($type);
        $logging->setAction($action);
        $logging->setDetail($detail);
        $logging->setDate(new \DateTime());
        $logging->setReferenceID($resourceId);
        $logging->setUser($user);

        $em = $this->container->get('doctrine')->getManager('edcoms_cms');
        $em->persist($logging);
        $em->flush();

        if ($logging->getId()) {
            //if all good add log entry to SPIRIT (if SPIRIT is loaded)
            //do not send to spirit if local db issue
            $spirit = null;
            if ($this->container->has('SPIRITRegistration')) {
                $spirit = $this->container->get('SPIRITRegistration');
            }

            if (!is_null($spirit)) {
                //if SPIRIT loaded get users SPIRIT ID
                $spiritID = $user->getPerson()->getContacts()->filter(function ($contactType) {
                    return ($contactType->getType() === 'spirit_id');
                });

                //if SPIRIT ID exists record login in
                if (count($spiritID) === 1) {
                    $name = 'activity';
                    $type = $type;
                    $event = $action;
                    $item = $detail;
                    $date = \DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
                    $spirit->registerActivity($spiritID->first()->getValue(), $name, $type, $event, $item, $date);
                }
            }
        }

        return $logging->getId() ? true: false;
    }

    /**
     * Get the message to be returned to the FE
     * used if there is an error when checking
     * for an allowed activity
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the HTTP status
     * used if there is an error when checking
     * for an allowed activity
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
}
