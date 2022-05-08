<?php

namespace EdcomsCMS\ContentBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as RootWebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class WebTestCase extends RootWebTestCase
{
    const BACK_OFFICE_USER = 1;
    const FRONT_OFFICE_USER = 2;

    /**
     * Creates a client and automatically logs in the test user specified in the 'parameters.yml' config.
     *
     * @return  Client      
     */
    protected function createNewClient($userType = null)
    {
        $client = self::createClient();
        $client->enableProfiler();

        if ($userType !== null) {
            $container = self::$kernel->getContainer();
            $session = $container->get('session');
            $username = $this->getUsernameFromContainerAndType($container, $userType);

            // get the test user.
            $user = $container
                ->get('doctrine')
                ->getManager('edcoms_cms')
                ->getRepository('EdcomsCMSAuthBundle:cmsUsers')
                ->findOneBy(['username' => $username]);

            if ($user === null) {
                throw new \Exception("User could not be found under the username of '$username'.");
            }

            // set the session with the found test user credentials.
            $token = new UsernamePasswordToken($user, null, 'registered_area', $user->getRoles());
            $session->set('_security_' . ($userType === self::BACK_OFFICE_USER ? 'cms_area' : 'registered_area'), serialize($token));
            $session->save();

            $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
        }

        return $client;
    }

    private function getUsernameFromContainerAndType(ContainerInterface $container, $userType = null)
    {
        $params = $container->getParameter('unit_test');
        $value = null;

        switch ($userType) {
            case self::BACK_OFFICE_USER:
                $value = $params['bo_username'];
                break;
            case self::FRONT_OFFICE_USER:
                $value = $params['fo_username'];
                break;
            default:
                break;
        }

        return $value;
    }
}
