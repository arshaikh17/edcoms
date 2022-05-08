<?php
namespace EdcomsCMS\AuthBundle\Security;

/**
 * Authentication Handler onAuthenticationSuccess is called when a successful auth attempt is made
 *
 * Created by PhpStorm.
 * User: stevenduncan-brown
 * Date: 01/08/2016
 * Time: 10:45
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use EdcomsCMS\ContentBundle\Entity\ActivityLog;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(RouterInterface $router, ContainerInterface $container)
    {
        $this->router = $router;
        $this->container = $container;
        $this->session = $this->container->get('session');
    }

    /**
     * Actions to take on successful login
     * Add the login
     *
     * @param Request $request
     * @param TokenInterface $token
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {

        //get the redirect path
        $key = '_security.registered_area.target_path';
        if ($this->session->has($key)) {
            $url = $this->session->get($key);
            $this->session->remove($key);
        } else {
            //if path not set
            if(!is_null($request->get('_target_path'))){
                $url = $request->getBasePath() . $request->get('_target_path');
            }else {
                $url = $request->getBasePath() . '/';
            }
        }

        //Get user with full contacts added
        $em = $this->container->get('doctrine')->getManager('edcoms_cms');
        $user = $token->getUser();
        $em->refresh($user);

        //Log the successful log in in the activity log
        $logging = new ActivityLog();
        $logging->setAction('login');
        $logging->setDetail('successful');
        $logging->setDate(new \DateTime());
        $logging->setReferenceType('site');
        $logging->setReferenceID($user->getID());
        $logging->setUser($user);

        $em->persist($logging);
        $em->flush();

        try {
            //detect if SPIRIT is installed & load it
            $spirit = null;
            if ($this->container->has('SPIRITRegistration')) {
                $spirit = $this->container->get('SPIRITRegistration');
            }
            if (!is_null($spirit)) {
                //if SPIRIT loaded get users SPIRIT ID
                $spiritID = $user->getPerson()->getContacts()->filter(function ($type) {
                    return ($type->getType() === 'spirit_id');
                });
                //if SPIRIT ID exists record login in
                if (count($spiritID) === 1) {
                    $name = 'record login';
                    $type = 'success';
                    $event = 'login';
                    $item = 'user';
                    $date = \DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
                    $spirit->registerActivity($spiritID->first()->getValue(), $name, $type, $event, $item, $date);
                }
            }
        } catch (\Exception $e) {
            //SPIRIT not available so not logged
        }

        return new RedirectResponse($url);
    }
}