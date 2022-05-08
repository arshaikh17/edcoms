<?php

namespace EdcomsCMS\ContentBundle\Model;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use EdcomsCMS\ContentBundle\Controller\APIController as CMSAPIController;

use EdcomsCMS\ContentBundle\Entity\Notification;
use EdcomsCMS\ContentBundle\Form\Notification\NotificationCreate;
use EdcomsCMS\ContentBundle\Helpers\NotificationHelper;


use EdcomsCMS\ContentBundle\Helpers\APIHelper;
/**
 * This is the Abstract controller containing all default CMS methods
 * It also contains any abstract methods that MUST be implemented by the Site itself - such as sign-in procedures, as this is handled by a Site User Controller
 *
 * @author richard
 */
abstract class AbstractAPIController extends CMSAPIController {
    
    /**
     *
     * @var \EdcomsCMS\ContentBundle\Helpers\APIHelper
     */
    protected $APIHelper;
    
    abstract public function signInAction(Request $request);
    /**
     * Authenticate a user
     * @Route("/API/auth")
     * @Method({"POST"})
     * @param Request $request The page request
     * @return JsonResponse
     */
    abstract public function authAction(Request $request);
    
    
    /**
     * @Route("/API/{any}", requirements={"any"=".+"})
     * @Method({"OPTIONS"})
     */
    public function authenticateOptions()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, Edcoms-Connect-Api-Key');
        header('Access-Control-Allow-Methods: POST, PUT, GET, OPTIONS');
        return new Response('', 204);
    }
    
    public function getContentAction(Request $request, $path)
    {
        
    }
    
    public function updateContentAction(Request $request, $path)
    {
        
    }
    
    public function createContentAction(Request $request, $path)
    {
        
    }
    
    public function getContentTypesAction(Request $request)
    {
        
    }
    
    public function createContentTypeAction(Request $request)
    {
        
    }
    
    public function getContentTypeAction(Request $request, $id)
    {
        
    }
    
    public function updateContentTypeAction(Request $request, $id)
    {
        
    }
    
    public function getUsersAction(Request $request)
    {
        
    }
    
    public function createUserAction(Request $request)
    {
        
    }
    
    public function getUserAction(Request $request, $id)
    {
        
    }
    
    public function updateUserAction(Request $request)
    {
        
    }
    
    /**
     * Create a notification
     * @Route("/API/notification")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function notificationAction(Request $request)
    {
        $apiKey = $request->headers->get('Edcoms-Connect-Api-Key');
        $source = parse_url($request->headers->get('origin'));
        
        // verify access \\
        if (!$this->verifyHost($source, $apiKey)) {
            return new JsonResponse(['code'=>400, 'error'=>'invalid origin: '.$source['scheme'].'://'.$source['host'].' add it via /cms/API/connector/origin'], 400);
        }
        
        $tmpData = json_decode($request->getContent(), true);
        $tmpData['info'] = json_encode($tmpData['info']);
        if (isset($tmpData['group'])) {
            $tmpData['group'] = (int)$tmpData['group'];
        }
        if (isset($tmpData['user'])) {
            $tmpData['user'] = (int)$tmpData['user'];
        }
        
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $id = (isset($tmpData['id'])) ? $tmpData['id'] : null;
        $name = (isset($tmpData['name'])) ? $tmpData['name'] : null;
        $notificationRepository = $em->getRepository('EdcomsCMSContentBundle:Notification');
        $exists = false;
        if (empty($id)) {
            $notification = $notificationRepository->findOneBy(['name'=>$name]);
            if ($notification) {
                $exists = true;
            } else {
                $notification = new Notification();
            }
        } else {
            $notification = $notificationRepository->find(intval($id));
        }
        
        $form = $this->createForm(NotificationCreate::class, $notification);
        // remove the send field from the form to keep it valid \\
        $send = (isset($tmpData['send'])) ? $tmpData['send'] : false;
        if ($send) {
            $inlineData = (isset($tmpData['data'])) ? $tmpData['data'] : null;
        }

        unset($tmpData['send']);
        unset($tmpData['data']);
        $data = ['NotificationCreate'=>$tmpData];
        
        $request->request->replace($data);
        $form->handleRequest($request);

        $resp = [
            'code'=>400,
            'error'=>'invalid_form'
        ];
        $status = 400;
        //check if form has been submitted
        if (($form->isSubmitted() && $form->isValid()) || $exists) {
            $status = 200;
            if (!$exists) {
                // only persist if not an existing item \\
                $em->persist($notification);
                $em->flush();
                $resp = [
                    'status'=>'created',
                    'id'=>$notification->getId()
                ];
            } else {
                $resp = [
                    'status'=>'exists',
                    'id'=>$notification->getId()
                ];
            }
            if ($send) {
                $resp = [
                    'status'=>'sent',
                    'id'=>$notification->getId()
                ];
                $notificationHelper = $this->get('notificationhelper');
                $this->APIHelper = $this->get('APIHelper');
                $this->APIHelper->setConnector($this->getConnector());
                $notificationHelper->setAPIHelper($this->APIHelper);
                //check if a mass notification and create instances for all relevant users
                if ($notification->getType() === NotificationHelper::NOTIFICATION_MASS_TYPE) {
                    $notificationHelper->sendMassNotification($notification);
                } else {
                    $inserts = [];
                    if (is_array($inlineData)) {
                        $inserts = $inlineData;
                    }
                    $usersRepository = $em->getRepository('EdcomsCMSAuthBundle:cmsUsers');
                    if ($tmpData['recipient'] === 'group') {
                        $group = $notification->getGroup();
                        $users = $usersRepository->findAllUsersByGroup($group);
                    } else {
                        $users = [$notification->getUser()];
                    }
                    $notificationHelper->createNotificationInstances($tmpData['name'], $inserts, $users);
                }
            }
        }
        return new JsonResponse($resp, $status);
    }
    
    /**
     * @Route("/API/notification")
     * @Method({"PUT"})
     * @param Request $request
     * @return JsonResponse
     */
    public function sendNotificationAction(Request $request)
    {
        $apiKey = $request->headers->get('Edcoms-Connect-Api-Key');
        $source = parse_url($request->headers->get('origin'));
        
        // verify access \\
        if (!$this->verifyHost($source, $apiKey)) {
            return new JsonResponse(['code'=>400, 'error'=>'invalid origin: '.$source['scheme'].'://'.$source['host'].' add it via /cms/API/connector/origin'], 400);
        }
        $data = json_decode($request->getContent(), true);
        $resp = [
            'code'=>400,
            'error'=>'invalid_form'
        ];
        $status = 400;
        if (isset($data['id'])) {
            $resp = [
                'code'=>404,
                'error'=>'not_found'
            ];
            $status = 404;
            $id = intval($data['id']);
            $em = $this->getDoctrine()->getManager('edcoms_cms');
            $notificationRepository = $em->getRepository('EdcomsCMSContentBundle:Notification');
            $notification = $notificationRepository->find($id);
            if ($notification) {
                $resp = [
                    'status'=>'sent'
                ];
                $status = 200;
                $notificationHelper = $this->get('notificationhelper');
                //check if a mass notification and create instances for all relevant users
                if ($notification->getType() === NotificationHelper::NOTIFICATION_MASS_TYPE) {
                    $notificationHelper->sendMassNotification($notification);
                } else {
                    $inserts = [];
                    if (is_array($inlineData)) {
                        $inserts = $inlineData;
                    }
                    $usersRepository = $em->getRepository('EdcomsCMSAuthBundle:cmsUsers');
                    if ($notification->getRecipient() === 'group') {
                        $group = $notification->getGroup();
                        $users = $usersRepository->findAllUsersByGroup($group);
                    } else {
                        $users = [$notification->getUser()];
                    }
                    $notificationHelper->createNotificationInstances($notification->getName(), $inserts, $users);
                }
            }
        }
        return new JsonResponse($resp, $status);
    }
}
