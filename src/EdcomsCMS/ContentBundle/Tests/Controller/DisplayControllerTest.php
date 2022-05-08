<?php

namespace Tests\EdcomsCMS\ContentBundle\Controller;

use EdcomsCMS\ContentBundle\Controller\DisplayController;
use EdcomsCMS\ContentBundle\Tests\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DisplayControllerTest extends WebTestCase
{
    CONST BACK_OFFICE_USER = 1;
    CONST FRONT_OFFICE_USER = 2;

    private $client = null;
    private $container = null;

    protected function setUp()
    {
        // set up the client and service container.
        $this->client = $this->createNewClient();
        $this->container = self::$kernel->getContainer();
    }

    /**
     * Searches for Content with the ContentType of 'Download'.
     * Then tests by performing a request to fetch the found Content.
     */
    public function testDownloadContent()
    {
        // fetch the 'Download' content type.
        $em = $this->container->get('doctrine')->getManager('edcoms_cms');
        $downloadContentType = $em
            ->getRepository('EdcomsCMSContentBundle:ContentType')
            ->findOneBy(['name' => 'Download']);

        if ($downloadContentType === null) {
            echo 'WARNING: cannot continue as the \'Download\' ContentType does not exist.';
            return;
        }

        // fetch all Content with the 'Download' ContentType.
        $downloadableContents = $em
            ->createQuery(
                'SELECT ' .
                    'c, ' .
                    'cfd, ' .
                    'cf, ' .
                    's ' .
                'FROM ' .
                    'EdcomsCMSContentBundle:Content c ' .
                    'JOIN c.structure s ' .
                    'LEFT JOIN c.custom_field_data cfd ' .
                    'LEFT JOIN cfd.custom_fields cf '.
                'WHERE ' .
                    'c.contentType = :contenttype ' .
                    'AND c.status = \'published\' ' .
                    'AND s.deleted = false'
            )
            ->setParameter('contenttype', $downloadContentType)
            ->getResult();

        if (empty($downloadableContents)) {
            echo 'WARNING: cannot continue as the \'Download\' ContentType does not exist.';
            return;
        }

        // find the first Content where one is behind login and one is not.
        $behindLoginContent = null;
        $frontOfLoginContent = null;

        foreach ($downloadableContents as $content) {
            $behindLogin = false;

            if ($content->behindLogin()) {
                if ($behindLoginContent === null) {
                    $behindLoginContent = $content;
                }
            } else {
                if ($frontOfLoginContent === null) {
                    $frontOfLoginContent = $content;
                }
            }

            if ($behindLoginContent !== null && $frontOfLoginContent !== null) {
                break;
            }
        }

        // use the same testing mechanism for both Content objects.
        $container = $this->container;
        $self = &$this;
        $testContentFunc = function ($content, $userType = null) use ($container, $em, $self) {
            $structure = $em
                ->getRepository('EdcomsCMSContentBundle:Structure')
                ->findWithAncestors($content->getStructure()->getId());

            // construct the URI.
            $path = '';

            while ($structure !== null) {
                $path = "{$structure->getLink()}/{$path}";
                $structure = $structure->getParent();
            }

            $path = rtrim($path, '/');

            // make the request.
            $client = $this->createNewClient($userType);
            $crawler = $client->request(Request::METHOD_GET, $container->get('router')->generate(
                'cms',
                ['path' => $path]
            ));

            // get the response and it's content.
            $response = $client->getResponse();

            // check type of response.
            $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        };

        // test the Content behind login.
        if ($behindLoginContent !== null) {
            $testContentFunc($behindLoginContent, self::FRONT_OFFICE_USER);
        } else {
            echo 'WARNING: cannot test download content behind login as one could not be found to test with.';
        }

        // test the Content that's not behind login.
        if ($frontOfLoginContent !== null) {
            $testContentFunc($frontOfLoginContent);
        } else {
            echo 'WARNING: cannot test download content in front of login as one could not be found to test with.';
        }
    }
}
