<?php

namespace EdcomsCMS\ContentBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use EdcomsCMS\AuthBundle\Entity\Token;

/**
 * Description of APIController
 *
 * @author richard
 */

class APIController extends Controller
{
    const STATUS_ERR = 0;
    const STATUS_OK = 1;
    
    const API_EXPIRY = '1 hour';

    /**
     *
     * @var \EdcomsCMS\ContentBundle\Helpers\APIHelper
     */
    private $APIHelper;
    
    /**
     *
     * @var \EdcomsCMS\ContentBundle\Helpers\QuizHelper
     */
    private $quiz;
    
    /**
     *
     * @var 'EdcomsCMS\AuthBundle\Entity\Connector
     */
    private $connector;

    /**
     * Save the data provided into the CMS
     * @Route("/cms/API/{type}/save/{id}")
     * @Method({"PUT", "POST"})
     * @param Request $request The page request
     * @param string $type The type of data to save
     * @param integer $id The ID of the content
     * @return JsonResponse
     */
    public function saveAction(Request $request, $type, $id)
    {
        $API = $this->get('APIHelper');
        
        // parse the data here and get it formatted ready to roll \\
        $data = $request;
        
        $API->save($type, $id, $data);
    }
    /**
     * Get the data requested from the CMS
     * @Route("/cms/API/{type}/get/{id}")
     * @Method({"GET"})
     * @param string $type The type of data to get
     * @param integer $id The ID of the content
     * @return JsonResponse
     */
    public function getAction($type, $id)
    {
        $API = $this->get('APIHelper');
        
        $API->get($type, $id);
    }


    /**
     * Creates a new LinkBuilder entity.
     * Uses the APIHelper service to process this after validating the request.
     *
     * @param   Request     $request    Object containing details of the incoming request.
     *
     * @return  JsonResponse            Status and data provided to detail the outcome of this response.
     */
    public function createShortURLAction(Request $request)
    {
        $this->processJSONForRequest($request);

        $host = $request->getHost();
        $link = $request->request->get('link');
        $options = [];

        if ($link === null || strlen($link) === 0) {
            return new JsonResponse([
                'data' => '\'link\' not provided',
                'status' => self::STATUS_ERR
            ], 400);
        }

        // detect whether '$link' is an internal URL.
        if (strpos($link, $host) !== false) {
            $options['internal'] = true;
            $link = ltrim($link, $host);
        }

        // set the 'target' in options if defined in the POST data.
        if ($request->request->has('target')) {
            $options['target'] = $request->request->get('target');
        }

        // create the LinkBuilder object.
        $apiHelper = $this->get('APIHelper');
        $linkBuilder = $apiHelper->createFriendlyLink($link, $options);

        // if the returned LinkBuilder is null,
        // something has gone wrong, probably because the Structure object cannot be found.
        // so return a 400.
        if ($linkBuilder === null) {
            return new JsonResponse([
                'data' => 'Not found',
                'status' => self::STATUS_ERR
            ], 400);
        }

        // return the created friendly link in a JSON string.
        return new JsonResponse([
            'data' => '/' . $linkBuilder->getFriendlyLink(),
            'status' => self::STATUS_OK
        ]);
    }

    /**
     * Retrieves the full URL from the LinkBuilder entity found by using '$friendlyLink' as the search criteria.
     * If the URL exists for the '$friendlyLink', a redirection response is returned.
     *
     * Uses the APIHelper service to fetch this after validating the request.
     *
     * @param   string      $friendlyLink   The shortened URL to search with.
     * @param   Request     $request        Object containing details of the incoming request.
     *
     * @return  RedirectResponse            Redirection to the link contained in the found LinkBuilder entity.
     * @throws  NotFoundHttpException       If no LinkBuilder is found, or the associated Structure entity could not be validated.
     */
    public function shortURLAction($friendlyLink, Request $request)
    {
        // retrieve the LinkBuilder object.
        $apiHelper = $this->get('APIHelper');
        $url = $apiHelper->getURLFromFriendlyLink($friendlyLink);

        // if returned value is null, the LinkBuilder entity could not be found.
        // or the associated Sturcture entity could not be validated.
        if ($url === null) {
            throw new NotFoundHttpException("URL not found for friendly link '$friendlyLink'.");
        }

        // if the URL is internal, add the scheme and host to the URL.
        if (substr($url, 0, 1) === '/') {
            $context = $this->container->get('router')->getContext();
            $url = $context->getScheme() . '://' . $context->getHost() . rtrim($url, '/');
        }

        return new RedirectResponse($url);
    }

    /**
     * Processes the content contained in '$request' by parsing it as a JSON string.
     * If parsing is successful, the content in '$request' is then set with the object returned from parsing.
     *
     * @param   Request     $request    Contains content to process, also manipulated if content is valid JSON string.
     */
    private function processJSONForRequest(Request &$request)
    {
            // try to parse the content as a JSON string.
            $jsonData = json_decode($request->getContent(), true);

            // only set the content if there is no error with decoding the string as JSON.
            if (json_last_error() === JSON_ERROR_NONE) {
                    if ($request->isMethod(Request::METHOD_POST) || $request->isMethod(Request::METHOD_PUT)) {
                            $request->request->replace($jsonData);
                    } else if ($request->isMethod(Request::METHOD_GET)) {
                            $request->query->replace($jsonData);
                    }
            }
    }

    /**
     * Get any sources for the specified library and return a token too
     * @Route("/API/sources/{library}")
     * @Method({"GET"})
     * @param string $library
     * @return JsonResponse
     */
    public function getSources($library)
    {
        $this->APIHelper = $this->container->get('APIHelper');
        $this->APIHelper->generateToken($library);
        $token = $this->APIHelper->getToken();
        $sources = $this->APIHelper->getSources($library);
        // TODO: refactor this so that it is not procedural.
        $data = ['sources'=>$sources, 'token'=>$token];
        return new JsonResponse($data);
    }
    
    /**
     * Launch an external application with appropriate parameters - this is a default method that should be overridden by business logic
     * @Route("/API/launch/{type}/{id}/{$token}", defaults={"id"="", "token"=""})
     * @Method({"GET"})
     * @param Request $request
     * @param string $type
     * @param int $id
     * @param string $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse Redirect URL (either login if required or the location)
     */
    public function launchExternal(Request $request, $type, $id, $token)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $session = $request->getSession();
        $authorizationChecker = $this->container->get('security.authorization_checker');
        switch ($type) {
            case 'quiz':
                if (!$authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') && !$authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
                    // redirect to login \\
                    $session->set('_security.registered_area.target_path', $request->getUri());
                    $redirect = $this->redirect('/login');
                }
                $this->quiz = $this->get('QuizHelper');
                $this->quiz->setQuiz($id);
                $this->quiz->setUser($user);
                $redirect = $this->redirect($this->quiz->launchQuiz());
                break;
        }
        return $redirect;
    }
    
    
    /**
     * 
     * Generate a key for the specified site
     * @Route("/API/connector/generate/{connectorID}")
     * @param Request $request
     * @param type $connectorID
     */
    public function generateKeys(Request $request, $connectorID=-1)
    {
        if ($connectorID !== -1) {
            $em = $this->getDoctrine()->getManager('edcoms_cms');
            $connectors = $em->getRepository('EdcomsCMSAuthBundle:Connector');
            $connector = $connectors->find($connectorID);
            if ($connector) {
                $pkey = openssl_pkey_new();
                $string = null;
                openssl_pkey_export($pkey, $string);

                $keyDetails = openssl_pkey_get_details($pkey);
                $pubKey = $keyDetails['key'];
                $key = new \EdcomsCMS\AuthBundle\Entity\ConnectorKey();
                $key->setAccess('basic');
                $key->setPrivateKey(base64_encode($string));
                $key->setApiKey(base64_encode($pubKey));
                $connector->addKey($key, true);
                $em->persist($key);
                $em->flush();
                return new JsonResponse(['site'=>$connector->getSite(), 'status'=>'keys_added', 'api_key'=>$key->getApiKey()]);
            }
            return new JsonResponse(['error'=>'connector_not_found'], 404);
        }
        return new JsonResponse(['error'=>'invalid_connector'], 400);
    }
    
    
    /**
     * Add a URL to a connector
     * @Route("/cms/API/connector/origin")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function addConnectorURLAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $request->request->replace(json_decode($request->request->all()));
            $em = $this->getDoctrine()->getManager('edcoms_cms');
            $connectors = $em->getRepository('EdcomsCMSAuthBundle:Connector');
            $connector = $connectors->find($request->get('connector_id'));
            if ($connector) {
                $url = new \EdcomsCMS\AuthBundle\Entity\ConnectorHook();
                $url->setConnector($connector);
                $url->setDefault($request->get('is_default', false));
                $url->setType($request->get('type', null));
                $url->setUrl($request->get('url'));
                $em->persist($url);
                $em->flush();
                return new JsonResponse(['site'=>$connector->getSite(), 'url'=>$url->getUrl(), 'connector_id'=>$connector->getId(), 'url_id'=>$url->getId()]);
            }
            return new JsonResponse(['error'=>'connector_not_found'], 404);
        }
        return new JsonResponse(['error'=>'incorrect_method'], 400);
    }
    
    /**
     * Generate an authentication token for a user signed against an API key
     * @param \EdcomsCMS\AuthBundle\Entity\cmsUsers $user
     * @param string $apiKey
     * @return Token
     */
    protected function generateAuthToken(\EdcomsCMS\AuthBundle\Entity\cmsUsers $user, $apiKey)
    {
        $token = new Token();
        $token->setAction('api_auth');
        $token->setDate(new \DateTime());
        $token->setToken(md5(time().uniqid().$user->getUsername().$apiKey));
        $token->setUsed(false);
        $token->setUser($user);

        $this->getDoctrine()->getManager('edcoms_cms')->persist($token);
        $this->getDoctrine()->getManager('edcoms_cms')->flush();
        return $token;
    }
    
    /**
     * Verify a user with a token provided
     * @param string $auth
     * @return bool|cmsUsers
     */
    protected function verifyAuthToken($auth)
    {
        $user = false;
        $tokens = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSAuthBundle:Token');
        $token = $tokens->findOneBy(['token'=>$auth]);
        if ($token) {
            $now = new \DateTime();
            $now->modify('-'.self::API_EXPIRY);
            $isExpired = ($token->getDate() < $now) ? true : false;
            if (!$isExpired) {
                $user = $token->getUser();
            }
        }
        return $user;
    }
    
    /**
     * Verify a host is who they claim to be and has access rights with the key provided
     * @param array $referral
     * @param string $apiKey
     * @return boolean
     */
    protected function verifyHost(array $referral, $apiKey)
    {
        $referralStr = $referral['scheme'].'://'.$referral['host'].((isset($referral['port'])) ? ':'.$referral['port'] : '');
        $connectorRepo = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSAuthBundle:Connector');
        $connector = $connectorRepo->findByKeyAndHook($referralStr, $apiKey);
        if ($connector) {
            $this->connector = $connector;

            // TODO: remove this nasty bit of procedural code.
            // headers should be added into the response object.
            header('Access-Control-Allow-Origin: '.$referralStr);
            header('Access-Control-Allow-Headers: Authorization, Content-Type, Edcoms-Connect-Api-Key');
            header('Access-Control-Allow-Methods: POST, PUT, GET, OPTIONS');
            return true;
        }
        return false;
    }

    /**
     * Authenticates a user using the received signature and API key.
     *
     * @param   mixed   $user       Either the user entity to authenticate, or the criteria to fetch the user with.
     * @param   string  $signature  To decode and validate against the user and '$apiKey'.
     * @param   string  $apiKey     API key of the incoming request.
     *
     * @return  mixed               The authenticated user, or a JsonResponse if authenticating was not successful.
     */
    protected function authenticateUser($user, $signature, $apiKey)
    {
        $em = $this->getDoctrine()->getManager('edcoms_cms');

        // check to see if API key exists in the database.
        $connector = $em->getRepository('EdcomsCMSAuthBundle:Connector')->findByApiKey($apiKey);

        if ($connector === null) {
            return new JsonResponse([
                'code' => 404,
                'error' => 'invalid api key'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // fetch user is one hasn't been through the parameters.
        if (!$user instanceof cmsUsers) {
            $user = $em->getRepository('EdcomsCMSAuthBundle:cmsUsers')->find($user);

            if ($user === null) {
                return new JsonResponse([
                    'code' => 404,
                    'error' => 'user not found'
                ], JsonResponse::HTTP_NOT_FOUND);
            }
        }

        // '$apiKey' should be a base64 encoded public key,
        // where that value is store in a ConnectorKey record under the column of 'apikey'.
        if (!$this->get('APIHelper')->decodeData($signature, [$user->getUsername()], $apiKey)) {
            return new JsonResponse([
                'code' => 400,
                'error' => 'invalid signature'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        return $user;
    }

    protected function getConnector()
    {
        return $this->connector;
    }
}
