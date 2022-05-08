<?php

namespace Tests\EdcomsCMS\ContentBundle\Controller;

use EdcomsCMS\ContentBundle\Controller\APIController;
use EdcomsCMS\ContentBundle\Entity\LinkBuilder;
use EdcomsCMS\ContentBundle\Tests\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class APIControllerTest extends WebTestCase
{
    private $addedLinkBuilders = [];
    private $client = null;
    private $container = null;

    protected function setUp()
    {
        // set up the client and service container.
        $this->client = $this->createNewClient(self::BACK_OFFICE_USER);
        $this->container = self::$kernel->getContainer();
    }

    /**
     * Tests to see if the short URL generator works in with external link.
     * Also tests to see if the short URL is redirecting to the intended URL.
     */
    public function testCreateExternalShortURL()
    {
        // create the link builder.
        $link = 'http://www.example.com?agetparameter=value';
        $linkBuilder = $this->createLinkBuilder($link);
        $this->assertEquals($linkBuilder->getLink(), $link);
        $this->assertNull($linkBuilder->getStructure());
    
        $this->checkLinkBuilder($linkBuilder, $link);
    }

    /**
     * Tests to see if the short URL generator works in with internal link.
     * Also tests to see if the short URL is redirecting to the intended URL.
     */
    public function testCreateInternalShortURL()
    {
        $em = $this
            ->container
            ->get('doctrine')
            ->getManager('edcoms_cms');

        // fetch the 'home' and first of it's child Structure entities.
        $structureRepo = $em->getRepository('EdcomsCMSContentBundle:Structure');
        $childStructure = $structureRepo->findByParent($structureRepo->findByLink('home')[0])[0];

        // create the link builder.
        $context = $this->container->get('router')->getContext();
        $link = "{$context->getScheme()}://{$context->getHost()}/{$childStructure->getLink()}";
        $linkBuilder = $this->createLinkBuilder($link);
        $this->assertNull($linkBuilder->getLink(), "LinkBuilder of ID {$linkBuilder->getId()} has a link of '{$linkBuilder->getLink()}'.");

        // get and assert the associated Structure.
        $this->assertEquals($linkBuilder->getStructure(), $childStructure);

        $this->checkLinkBuilder($linkBuilder, $link);
    }

    /**
     * Asserts to see if the redirected URL matches the value of '$link'.
     * It grabs the 'friendlyLink' from '$linkBuilder' and tests the API call.
     */
    protected function checkLinkBuilder(LinkBuilder $linkBuilder, $link)
    {
        // make the request.
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->container->get('router')->generate(
                'short_url',
                ['friendlyLink' => $linkBuilder->getFriendlyLink()]
            )
        );

        // get the response and it's content.
        $response = $this->client->getResponse();

        // check type of response.
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);

        // check URL to redirect to.
        $this->assertEquals(rtrim($response->getTargetUrl(), '/'), rtrim($link, '/'));
    }

    /**
     * Tests the API call to create a short URL with the target URL of '$link'.
     */
    protected function createLinkBuilder($link)
    {
        $crawler = $this->client->request(
            Request::METHOD_POST,
            $this->container->get('router')->generate('create_short_url'),
            [],
            [],
            [],
            json_encode([
                'link' => $link,
            ])
        );

        // get the response and it's content.
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        // check for 200 (OK) response.
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // check for JSON error messages.
        $this->assertEquals(json_last_error(), JSON_ERROR_NONE);
        $this->assertNotNull($responseContent);

        // check for returned status.
        $this->assertArrayHasKey('status', $responseContent);
        $this->assertEquals($responseContent['status'], APIController::STATUS_OK);

        // get the friendly link.
        $this->assertArrayHasKey('data', $responseContent);
        $friendlyLink = ltrim($responseContent['data'], '/');
        $this->assertFalse(strlen($friendlyLink) === 0);

        // fetch the link builder.
        // assertion checked within next method.
        $linkBuilder = $this->fetchLinkBuilderWithFriendlyLink($friendlyLink);

        // add it to the added array to delete at the end of the tests.
        $this->addedLinkBuilders[] = $linkBuilder;

        return $linkBuilder;
    }

    /**
     * Asserts and returns the LinkBuilder with the friendlyLink the same value as '$friendlyLink'.
     */
    protected function fetchLinkBuilderWithFriendlyLink($friendlyLink)
    {
        // fetch the link builder.
        $linkBuilder = $this->container
            ->get('doctrine')
            ->getManager('edcoms_cms')
            ->getRepository('EdcomsCMSContentBundle:LinkBuilder')
            ->findByFriendlyLink($friendlyLink);

        $this->assertNotNull($linkBuilder, "Failed to fetch LinkBuilder with the friendlyLink of '$friendlyLink'.");
        $this->assertEquals($linkBuilder->getFriendlyLink(), $friendlyLink, "Fetched LinkBuilder with ID of {$linkBuilder->getId()} has a friendly link of '{$linkBuilder->getFriendlyLink()}' is not the same as '{$friendlyLink}'.");

        return $linkBuilder;
    }

    protected function tearDown()
    {
        if ($this->client !== null) {
            $em = $this->container
                ->get('doctrine')
                ->getManager('edcoms_cms');

            $idsToRemove = [];

            // remove all added link builder entities added as part of these tests.
            foreach ($this->addedLinkBuilders as $linkBuilder) {
                $idsToRemove[] = $linkBuilder->getId();
            }

            // re-fetch the stored LinkBuilder objects.
            $linkBuilders = $em->getRepository('EdcomsCMSContentBundle:LinkBuilder')->findById($idsToRemove);

            // iterate through and remove them from the database.
            foreach ($linkBuilders as $linkBuilder) {
                // remove from DB.
                $em->remove($linkBuilder);
            }

            // write changes.
            $em->flush();
        }
    }
}
