<?php

namespace EdcomsCMS\ContentBundle\Controller;

use Assetic\Filter\JSMinFilter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
//use Edcoms\Quiz;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class QuizController extends Controller
{
    private $app;
    private $apiURL;
    private $studentURL;
    private $joinURL;
    private $hostURL;
    private $returnURL;
    private $client;
    private $url = "local.pl.co.uk";
    private $stylesheet = "/css";
    private $assets = "/assets";
    private $description = "Premier League ";

    public function __construct() {
        $this->setApp("va-quiz-live");
        $this->setApiURL("http://local.be_quiz.co.uk/app_dev.php/api");
        $this->setStudentURL("http://local.pl.co.uk/cms/quiz/join");
        $this->setJoinURL("http://localhost:3000/#/quiz");
        $this->setHostURL("http://localhost:3000/#/host");
        $this->setReturnURL("http://local.pl.co.uk/cms/quiz/");
        $this->setClient("premierleague");
    }

    public function setApp($app) {
        $this->app = $app;
        return $this;
    }

    public function setApiURL($apiURL) {
        $this->apiURL = $apiURL;
        return $this;
    }

    public function getApiURL() {
        return $this->apiURL;
    }

    public function setStudentURL($studentURL) {
        $this->studentURL = $studentURL;
        return $this;
    }

    public function getStudentURL() {
        return $this->studentURL;
    }

    public function setJoinURL($joinURL) {
        $this->joinURL = $joinURL;
        return $this;
    }

    public function getJoinURL() {
        return $this->joinURL;
    }

    public function setHostURL($hostURL) {
        $this->hostURL = $hostURL;
        return $this;
    }

    public function getHostURL() {
        return $this->hostURL;
    }

    public function setReturnURL($returnURL) {
        $this->returnURL = $returnURL;
        return $this;
    }

    public function getReturnURL() {
        return $this->returnURL;
    }

    public function setClient($client) {
        $this->client = $client;
        return $this;
    }

    public function getClient() {
        return $this->client;
    }



    /**
     * @Route("/quiz/launch/{teamId}")
     * @Method({"GET"})
     */
    public function launchAction($teamId,Request $request) {
        $mode = $request->get('mode');
        $message = '';
        $teamsRepo = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('AppBundle:Team');
        $team = $teamsRepo->findOneBy(['id' => $teamId]);
        $teamName = $team->getName();
        $numberOfStudents = count($team->getMembers());
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if(is_object($user)) {
            $username = $user->getUsername();
            $userid = $user->getId();
            $url = $this->hostURL;
            /** First we create the teacher user or retrieve an existing teacher user */
            $response = $this->createUserAction($request, $username, $userid, $teamName, $teamId, $numberOfStudents, 'teacher');
            /** if teacher exists, continue creating the game */
            if ($response->status === true && property_exists($response, 'user')) {

                $gameResponse = $this->createGameAction($request, $username, $teamId, $mode, $numberOfStudents);
                if ($gameResponse->status === true && property_exists($gameResponse, 'game')) {

                    $tokenResponse = $this->generateAccessTokenAction($username, $gameResponse->game->token);

                    if ($tokenResponse->status === true) {
                        $url .= "/" . $username . "/" . $tokenResponse->token;
                    } else {
                        $message = implode(', ', $tokenResponse->message);
                    }
                } else {
                    $message = implode(', ', $gameResponse->message);
                }
            } else {
                $message = implode(', ', $response->message);
            }
        }else{
            $message = "User not logged in.";
        }
        return $this->redirect($url);
    }

    /**
     * @Route("/cms/quiz/join/q/{teamId}/{gameToken}")
     * @Method({"GET"})
     */
    public function joinAction($teamId,$gameToken) {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $url = '';
        if(is_object($user)) {
            $username = $user->getUsername();
            $userid = $user->getId();
            $message = '';
            if ($teamId) {
                $response = $this->createUserAction($username, $userid, null, $teamId, 0, 'student');
                /** is user exists, join game */
                if ($response->status === true && property_exists($response, 'user')) {
                    $tokenResponse = $this->generateAccessTokenAction($username, $gameToken);
                    if ($tokenResponse->status === true) {
                        $url = $this->joinURL . "/" . $username . "/" . $tokenResponse->token;
                    } else {
                        $message = implode(', ', $tokenResponse->message);
                    }
                } else {
                    $message = implode(', ', $response->message);
                }
            } else {
                $message = "Team not found.";
            }
        }else{
            $message = "User not logged in.";
        }
        return $this->redirect($url);
    }

    private function createUserAction($request, $username, $externalId, $teamName = null, $teamExternalId = null, $numberOfStudents = 0, $type) {
        $clientCheck = $this->checkClientExists($request);
        if($clientCheck) {
            $params = array(
                'app' => $this->app,
                'username' => $username,
                'external-id' => $externalId,
                'type' => $type,
                'number-of-students' => $numberOfStudents,
                'client' => $this->client,
                'url' => $this->url,
            );
            if ($teamName !== null) {
                $params['team-name'] = $teamName;
            }

            if ($teamExternalId !== null) {
                $params['team-external-id'] = $teamExternalId;
            }

            $response = $this->makeCall("create-user", $params);
            if (empty($response->errorNo)) {
                return json_decode($response->response);
            }
        }
    }

    private function createGameAction($request,$username, $teamExternalId, $mode, $numberOfStudents = null, $studentUrl = null) {
        $params = array(
            'app' => $this->app,
            'username' => $username,
            'team-external-id' => $teamExternalId,
            'quiz' => $request->get('quiz'),
            'mode' => $mode,
            'return-url' => $this->returnURL,
        );
        if ($mode == 'classroom') {
            $params['number-of-students'] = $numberOfStudents;
        } elseif ($mode == 'multiplayer') {
            $params['student-url'] = $studentUrl;
            $params['number-of-students'] = $numberOfStudents;
        }
        $response = $this->makeCall("create-game", $params);
        if (empty($response->errorNo)) {
            return json_decode($response->response);
        }
    }

    private function generateAccessTokenAction($username, $gameToken) {
        $params = array(
            'app' => $this->app,
            'username' => $username,
            'token' => $gameToken,
        );
        $response = $this->makeCall("generate-access-token", $params);
        if (empty($response->errorNo)) {
            return json_decode($response->response);
        }
    }

    /**
     * @Route("/cms/create/quiz")
     * @Method({"GET","POST"})
     */
    public function createQuizAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $client = $this->checkClientExists($request);
        if ($client) {
            if(is_object($user)) {
                $username = $user->getUsername();
                $userId = $user->getId();
                $well_done_text = $request->get('well_done_text');
                $quizName = $request->get('quiz');
                $params = array(
                    'app' => $this->app,
                    'user' => $userId,
                    'client' => $this->client,
                    'quiz' => $quizName,
                    'return-url' => $this->returnURL,
                    'well_done_text' => $well_done_text,
                );

                $response = $this->makeCall("create-quiz", $params);
                if (empty($response->errorNo)) {
                    return json_decode($response->response);
                }
            }
        }
    }

    private function checkClientExists(Request $request) {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $params = array(
            'client' => $this->client,
            'description' => $this->description,
            'url' => $this->url,
            'stylesheet' => $this->stylesheet,
            'assets' => $this->assets
        );
        $response = $this->makeCall("check-client", $params);
        if (empty($response->errorNo)) {
            return json_decode($response->response);
        }
    }

    /**
     * @Route("/cms/quiz/question/create")
     * @Method({"GET", "POST"})
     */
    public function createQuestionAction(Request $request) {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $username = $user->getUsername();
        $teamExternalId = $request->get('teamExternalId');
        $quizId = $request->get('quizId');
        $question = $request->get('question');
        $explanation = $request->get('explanation');
        $order = $request->get('order');
        $correctAnswer = $request->get('correct_answer');
        $imagePath = $request->get('image');
        $type = $request->get('type');
        $timeLimit = $request->get('timeLimit');
        $params = array(
            'app' => $this->app,
            'username' => $username,
            'team-external-id' => $teamExternalId,
            'quiz' => $quizId,
            'return-url' => $this->returnURL,
            'text' => $question,
            'explanation' => $explanation,
            'order' => $order,
            'correctAnswer' => $correctAnswer,
            'imagePath' => $imagePath,
            'type' => $type,
            'timeLimit' => $timeLimit,
        );
        $response = $this->makeCall("create-question", $params);
        if (empty($response->errorNo)) {
            return json_decode($response->response);
        }
    }

    /**
     * @Route("/cms/quiz/answer/create")
     * @Method({"GET","POST"})
     */
    public function createAnswerAction(Request $request) {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $username = $user->getUsername();
        $teamExternalId = $request->get('teamExternalId');
        $quizName = $request->get('quizname');
        $question = $request->get('question');
        $text = $request->get('text');
        $params = array(
            'app' => $this->app,
            'username' => $username,
            'team-external-id' => $teamExternalId,
            'quiz' => $quizName,
            'return-url' => $this->returnURL,
            'question' => $question,
            'text' => $text
        );
        $response = $this->makeCall("create-answer", $params);

        if (empty($response->errorNo)) {
            return json_decode($response->response);
        }else{
            return new JsonResponse('error');
        }
    }

    private function generateReport() {
        $params = array(
            'app' => $this->app
        );
        $response = $this->makeCall("generate-report", $params);
        if (empty($response->errorNo)) {
            return json_decode($response->response);
        }
    }

    private function makeCall($endpoint, $fields = []) {
        $connection = curl_init();
        curl_setopt_array($connection, [
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->apiURL . "/" . $endpoint,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_TIMEOUT => 30,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $fields,
        ]);

        $callResponse = new \stdClass();
        $callResponse->response = curl_exec($connection);
        $callResponse->errorNo = curl_errno($connection);
        $callResponse->error = curl_error($connection);
        $callResponse->info = curl_getinfo($connection);

        curl_close($connection);

        return $callResponse;
    }
}