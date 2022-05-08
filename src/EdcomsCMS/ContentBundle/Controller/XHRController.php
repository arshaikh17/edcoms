<?php

namespace EdcomsCMS\ContentBundle\Controller;

use EdcomsCMS\ContentBundle\Event\RatingEvent;
use EdcomsCMS\ContentBundle\Event\RatingEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use EdcomsCMS\ContentBundle\Entity\Structure;
use EdcomsCMS\ContentBundle\Helpers\ContentHelper;
use EdcomsCMS\ContentBundle\Helpers\RatingHelper;

class XHRController extends Controller
{
    /**
     * Handle a rating
     * @param Request $request
     * @param type $structureID
     * @param type $ratingVal
     * @return JsonResponse
     */
    public function ratingAction(Request $request, $structureID, $ratingVal)
    {
        $structures = $this->getDoctrine()->getManager('edcoms_cms')
            ->getRepository('EdcomsCMSContentBundle:Structure');
        $structure = $structures->find(intval($structureID));
        $resp = ['status'=>0, 'data'=>'not_found'];
        $status = 404;
        if ($structure && $structure->getRateable()) {
            if ($structure->getMaster() !== null) {
                $structure = $structure->getMaster();
            }
            $rating = new RatingHelper($this->getDoctrine(), $structure, $this->get('event_dispatcher'));
            $ratingObj = $rating->AddRating(intval($ratingVal),
                $this->get('security.token_storage')->getToken()->getUser());

            //fire off log event
            $event = new RatingEvent($ratingObj);
            $this->get('event_dispatcher')->dispatch(RatingEvents::RATING_AWARDED, $event);

            $resp = ['status'=>1, 'data'=>$rating->GetAverage()];
            $status = 200;
        }
        return new JsonResponse($resp, $status);
    }
    
    /**
     * Get all items from the CMS via AJAX that match the IDs provided
     * @param string $item
     * @param string $ids
     * @return JsonResponse
     */
    public function getAction($item, $ids)
    {
        $user = null;
        $userObj = $this->get('security.token_storage')->getToken()->getUser();
        if (is_object($userObj)) {
            $user = $userObj;
        }
        $contentHelper = new ContentHelper(
            $this->getDoctrine()->getManager('edcoms_cms')
                ->getRepository('EdcomsCMSContentBundle:Content'),
            $this->getDoctrine()->getManager('edcoms_cms')
                ->getRepository('EdcomsCMSContentBundle:Structure'),
            $this->getDoctrine(),
            $user,
            $this->get('edcoms.content.service.configuration')
        );
        switch ($item) {
            case 'content':
                $ret = $contentHelper->getContent($ids);
                break;
        }
        return $ret;
    }
    
}