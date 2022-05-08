<?php

namespace EdcomsCMS\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ActivityController
 *
 * Logging of site activity
 *
 * @package EdcomsCMS\ContentBundle\Controller
 */
class ActivityController extends Controller
{

    public function postAction(Request $request) {

        //requires user to be logged in
        $user = $this->getUser();
        if ($user === null) {
            $data = array('success' => false, 'message' => 'user must be logged in');
            $status = JsonResponse::HTTP_UNAUTHORIZED;
            return new JsonResponse($data, $status);
        }

        //check site is configured to record this activity
        $activityHelper = $this->container->get('ActivityHelper');
        if (!$activityHelper->checkActivityAllowed($request)) {
            //return error
            $data = array(
                'success' => false,
                'message' => $activityHelper->getMessage()
            );
            $status = $activityHelper->getStatus;

            return new JsonResponse($data, $status);
        }

        //Get the resource id and if not present fall back to user id
        $resourceId = $request->get('resourceId');
        if(!$resourceId) {
            $resourceId = $user->getID();
        }
        $type = $request->get('type');
        $action = $request->get('action');
        $detail = $request->get('detail');

        //add to db and spirit
        if (!$activityHelper->recordActivity($type, $action, $detail, $resourceId, $user)) {
            return new JsonResponse(
                array('success' => false, 'message' => 'Activity not saved'),
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        //if this point is reached then no error have occurred and the activity is logged
        return new JsonResponse(
            array('success' => true, 'message' => 'Activity logged'),
            JsonResponse::HTTP_OK);
    }
}
