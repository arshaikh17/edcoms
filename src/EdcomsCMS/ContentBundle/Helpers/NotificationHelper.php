<?php
namespace EdcomsCMS\ContentBundle\Helpers;
use EdcomsCMS\ContentBundle\Entity\Notification;
use EdcomsCMS\ContentBundle\Entity\NotificationInstance;

/**
 * Helper to assist with CMS notification activities
 *
 */
class NotificationHelper
{
    const NOTIFICATION_MASS_TYPE = 'mass';
    const NOTIFICATION_RECIPIENT_GROUP = 'group';
    const NOTIFICATION_RECIPIENT_USER = 'user';
    const NOTIFICATION_BATCH_LIMIT = 200;

    private $doctrine;
    private $temporaryUser;
    
    /**
     *
     * @var \EdcomsCMS\ContentBundle\Helpers\APIHelper
     */
    protected $APIHelper;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function setAPIHelper($APIHelper)
    {
        $this->APIHelper = $APIHelper;
    }

    /**
     * Create an instance of a notification
     *
     * @param $notificationName
     * @param $inserts
     * @param $user
     * @return bool
     */
    public function createNotificationInstance($notificationName, $inserts, $user)
    {
        $em = $this->doctrine->getManager('edcoms_cms');
        $notificationRepository = $em->getRepository('EdcomsCMSContentBundle:Notification');
        $notification = $notificationRepository->findOneBy( [ 'name' => $notificationName ] );
        $notificationInstance = new NotificationInstance();

        if ($notification) {
            $notificationInstance->setUser($user);
            $notificationInstance->setNotification($notification);
            $notificationInstance->setDateIssued(new \DateTime());
            $this->temporaryUser = $user;
            foreach ($inserts as &$insert) {
                // do some code swaps \\
                $insert = preg_replace_callback('/{([a-zA-Z0-9\/_]*)}/', [&$this, 'notificationTextReplace'], $insert);
            }
            $notificationInstance->setData($inserts);
            $em->persist($notificationInstance);
            $em->flush();
        }

        return ($notificationInstance->getId() !== null)? true: false;
    }
    
    public function createNotificationInstances($notificationName, $inserts, $users)
    {
        foreach ($users as $user) {
            $this->createNotificationInstance($notificationName, $inserts, $user);
        }
    }

    /**
     * Get the count of unread notifications for a user
     *
     * @param $user
     * @return mixed
     */
    public function getUnreadCountByUser($user)
    {
        $em = $this->doctrine->getManager('edcoms_cms');
        $notificationInstanceRepository = $em->getRepository('EdcomsCMSContentBundle:NotificationInstance');
        $unreadNotificationsCount = $notificationInstanceRepository->getUnreadCountByUser($user);

        return $unreadNotificationsCount;
    }
    
    /**
     * 
     * @param Notification $notification
     * @return string
     */
    public function sendNotification(Notification $notification)
    {
        $message = 'Notification cannot be sent to mass recipients';
        //check if a mass notification and create instances for all relevant users
        if ($notification->getType() === NotificationHelper::NOTIFICATION_MASS_TYPE) {


            $this->sendMassNotification($notification);
            $message = 'Notification sent';
        }
        return $message;
    }

    /**
     * Send a mass notification
     *
     * @param $notification
     */
    public function sendMassNotification(Notification $notification)
    {
        //check notification is correct type
        if ($notification->getType() === self::NOTIFICATION_MASS_TYPE) {
            $recipient = $notification->getRecipient();

            switch($recipient) {

                case self::NOTIFICATION_RECIPIENT_GROUP:
                    //send to a group

                    //get all users for the group
                    $em = $this->doctrine->getManager('edcoms_cms');
                    $usersRepository = $em->getRepository('EdcomsCMSAuthBundle:cmsUsers');
                    $group = $notification->getGroup();
                    $users = $usersRepository->findAllUsersByGroup($group);

                    //if we have any users
                    if (count($users) > 0) {

                        //create a new instance for each user
                        foreach ($users as $k => $user) {

                            //check to see if they already have this notification before sending a new one
                            if (!$this->userHasNotificationInstance($user, $notification)) {
                                $notificationInstance = new NotificationInstance();
                                $em->persist($notificationInstance);
                                $notificationInstance->setUser($user);
                                $notificationInstance->setNotification($notification);
                            }

                            //batch flush
                            if ($k > 0 && $k % self::NOTIFICATION_BATCH_LIMIT === 0) {
                                $em->flush();
                            }
                        }

                        //final flush
                        $em->flush();
                    }

                    return;

                case self::NOTIFICATION_RECIPIENT_USER:
                    //send to a user

                    $user = $notification->getUser();
                    $em = $this->doctrine->getManager('edcoms_cms');
                    //check to see if they already have this notification before sending a new one
                    if (!$this->userHasNotificationInstance($user, $notification)) {
                        $notificationInstance = new NotificationInstance();
                        $em->persist($notificationInstance);
                        $notificationInstance->setUser($user);
                        $notificationInstance->setNotification($notification);
                    }

                    return;

                default:

                    return;
            }
        }
    }

    /**
     * Check if a user has a notification instance
     *
     * @param $user
     * @param $notification
     * @return bool
     */
    public function userHasNotificationInstance($user, $notification)
    {
        $notificationInstanceRepository = $this->doctrine->getManager('edcoms_cms')
            ->getRepository('EdcomsCMSContentBundle:NotificationInstance');
        $notificationInstance = $notificationInstanceRepository->findBy(
            array(
                'user' => $user,
                'notification' => $notification
            )
        );

        return $notificationInstance? true: false;
    }
    
    /**
     * 
     * @param type $text
     * @param type $user
     */
    private function notificationTextReplace($text=[])
    {
        $output = '';
        switch ($text[0]) {
            case '{SIGNATURE}':
                $connector = $this->APIHelper->getConnector();
                $key = $connector->getKeys()->first();
                $output = urlencode(base64_encode($this->APIHelper->signData([$this->temporaryUser->getUsername()], $key->getPrivateKey())));
                break;
            case '{USER_ID}':
                $output = $this->temporaryUser->getId();
                break;
        }
        return $output;
    }
}