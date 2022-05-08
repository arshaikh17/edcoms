<?php

namespace EdcomsCMS\AuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{
    public function loginAction($xhr) {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        
        return $this->render(
            "EdcomsCMSTemplatesBundle:Auth:login.html.twig",
            [
                'xhr'=>$xhr,
                'last_username'=>$lastUsername,
                'error'=>$error,
                'title'=>'Login'
            ]
        );
    }
    
    public function loginCheckAction() {
        
    }
}
