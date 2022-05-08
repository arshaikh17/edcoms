<?php

namespace EdcomsCMS\ContentBundle\Controller;

use EdcomsCMS\ContentBundle\Entity\Notification;
use EdcomsCMS\ContentBundle\Form\Notification\NotificationCreate;
use EdcomsCMS\ContentBundle\Helpers\NotificationHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{

    /**
     * Trigger the sending of a pre existing mass notification
     *
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function sendAction($id, Request $request)
    {
        //load notification from db
        $id = intval($id);
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $notificationRepository = $em->getRepository('EdcomsCMSContentBundle:Notification');
        $notification = $notificationRepository->find($id);

        /** RW CHANGE 24/03/17 - Moved the logic to detect notification type into helper **/
        $message = 'Notification not found';
        if ($notification) {
            $notificationHelper = $this->get('notificationhelper');
            $message = $notificationHelper->sendNotification($notification);
        }

        return $this->render('EdcomsCMSTemplatesBundle:notification:sent.html.twig',
            array(
                'message' => $message
            ));
    }

    /**
     * Get one or all Notifications
     * Default or -1 will return all
     *
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function getAction($id, Request $request)
    {
        $id = intval($id);
        $notificationRepository = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Notification');

        if ($id > 0) {

            //try to find in DB
            $notification = $notificationRepository->find($id);
            if (!$notification) {//not found display empty form
                $notification = new Notification();
            }
            $notifications = array($notification);

            //add form
            $form = $this->createForm(NotificationCreate::class, $notification, array(
                'action' => $this->generateUrl('notification_update', array(
                    'id' => $id
                ))
            ));

        } else {
            //get all and show blank form
            $notifications = $notificationRepository->findAll();
            $form = $this->createForm(NotificationCreate::class, new Notification());
        }

        return $this->render('EdcomsCMSTemplatesBundle:notification:display.html.twig', array(
            'notifications' => $notifications,
            'form' => $form->createView()
        ));
    }

    /**
     * Add a new notification
     *
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $notification = new Notification();
        $form = $this->createForm(NotificationCreate::class, $notification);
        $form->handleRequest($request);

        //check if form has been submitted
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('edcoms_cms');
            $em->persist($notification);
            $em->flush();

            //render success page
            return $this->render('EdcomsCMSTemplatesBundle:notification:display.html.twig', array(
                'notification' => $notification,
                'form' => $form->createView(),
                'message' => 'Notification created'
            ));
        }

        //render form
        return $this->render('EdcomsCMSTemplatesBundle:notification:display.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * Update an existing Notification
     *
     * @param $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateAction($id, Request $request)
    {
        //init params
        $id = intval($id);
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $notificationRepository = $em->getRepository('EdcomsCMSContentBundle:Notification');
        $message = 'ID is invalid';
        $form = null;

        if ($id > 0) {//a valid id has been submitted
            $message = 'Notification not found';

            $notification = $notificationRepository->find($id);
            if ($notification) {//notification is found in the DB
                $message = 'Form not submitted or not valid';

                //handle using form
                $form = $this->createForm(NotificationCreate::class, $notification);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {//submission is fine
                    $em->flush();//flush to DB
                    $message = 'Notification updated';
                }
            }
        }

        return new RedirectResponse($this->generateUrl('notification_get', array(
            'id' => $id
        )));
    }

    /**
     * Delete a notification by id
     *
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function deleteAction($id, Request $request)
    {
        $id = intval($id);
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $notificationRepository = $em->getRepository('EdcomsCMSContentBundle:Notification');

        if ($id > 0) {//look in DB using supplied id
            $notification = $notificationRepository->find($id);

            if ($notification) {//found in DB, so delete
                $em->remove($notification);
                $em->flush();
            }
        }

        return $this->render('EdcomsCMSTemplatesBundle:notification:display.html.twig', array(
            'message' => 'Notification deleted'
        ));
    }

    /**
     * Load a set of 'seen' notifications for the currently logged in user
     *
     * @param $offset
     * @param $limit
     * @return JsonResponse
     * @throws \EdcomsCMS\ContentBundle\Entity\Exception
     */
    public function loadAction($offset, $limit)
    {
        $offset = intval($offset);
        $limit = intval($limit);

        //get logged in user
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user)) {//fail if not logged in
            return new JsonResponse([
                'message' => 'Not logged in'
            ], 401);
        }

        //get instances
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $notificationInstanceRepository = $em->getRepository('EdcomsCMSContentBundle:NotificationInstance');

        //add one to limit to check if there are more to load
        $instances = $notificationInstanceRepository->findBy(
            array( 'user' => $user ),
            array( 'dateIssued' => 'Desc' ),
            ($limit +1),//+1 to test for more to load
            $offset);

        //convert to json
        $loadMore = false;
        $notifications = array();
        if (count($instances) > 0 ) {

            //check if there are more to load
            if (count($instances) > $limit) {
                $loadMore = true;
                $instances = array_slice($instances, 0, $limit);
            }

            foreach ($instances as $k => $instance) {
                $info = json_decode($instance->getNotification()->getInfo(), true);
                $data = $instance->getData();
                //the data field is intended to hold instance specific data
                //which is inserted in to the generic body of the notification
                if (count($data) > 0 && isset($info['body'])) {
                    $body = vsprintf($info['body'], $data);
                } else {
                    $body = isset($info['body'])? $info['body']: '';
                }

                //check if instance has been read
                $read = true;
                if (is_null($instance->getDateSeen())) {
                    $read = false;
                    //mark instance as read
                    $instance->setDateSeen(new \DateTime());
                }

                $notifications[] = array(
                    'id' => $instance->getId(),
                    'icon' => isset($info['icon'])? $info['icon']: '',
                    'body' => $body,
                    'date' => date_format($instance->getDateIssued(), 'd/m/y'),
                    'read' => $read
                );
            }
            $em->flush();//persist the seen date
        }

        return new JsonResponse([
            'data' => $notifications,
            'loadMore' => $loadMore
        ], 200);
    }
}

