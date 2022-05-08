<?php

namespace EdcomsCMS\BadgeBundle\Controller;

use EdcomsCMS\BadgeBundle\Entity\BadgeSimple;
use EdcomsCMS\BadgeBundle\Entity\BadgeAward;
use EdcomsCMS\BadgeBundle\Form\BadgeCreate;
use EdcomsCMS\BadgeBundle\Form\BadgeSimpleCreate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Created by PhpStorm.
 * User: stevenduncan-brown
 * Date: 05/10/2016
 * Time: 15:14
 */
class BadgeController extends Controller
{
    /**
     * Create or edit a badge
     * All fields sent as post data
     *
     * @param Request $request
     * @param $badgeId - integer
     * @return JsonResponse
     */
    public function editAction(Request $request, $badgeId)
    {
        $badgeId = intval($badgeId);
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $badgeSimpleRepo = $em->getRepository('EdcomsCMSBadgeBundle:BadgeSimple');

        if ($badgeId === -1) {//create new badge
            $badgeSimple = new BadgeSimple();
            $em->persist($badgeSimple);
        } else {//edit existing badge
            $badgeSimple = $badgeSimpleRepo->find($badgeId);
            if (!$badgeSimple) {
                return new JsonResponse('badge not found', JsonResponse::HTTP_NOT_FOUND);
            }
        }

        $form = $this->createForm(BadgeSimpleCreate::class, $badgeSimple);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->flush();

            } else {
                $data = ['errors'=>$this->get('form_errors')->getArray($form), 'status'=>0];
                return new JsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
            }

            return new JsonResponse('success', JsonResponse::HTTP_OK);
        }

        return new JsonResponse($badgeSimple->toJSON(), 200);
    }

    /**
     * Delete a badge by id
     *
     * @param Request $request
     * @param $badgeId - integer
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $badgeId)
    {
        $badgeId = intval($badgeId);
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $badgeRepo = $em->getRepository('EdcomsCMSBadgeBundle:BadgeBase');
        $badge = $badgeRepo->find($badgeId);

        if (!$badge) {
            return new JsonResponse('badge not found', JsonResponse::HTTP_NOT_FOUND);
        }

        $em->remove($badge);
        $em->flush();
        return new JsonResponse('success', JsonResponse::HTTP_OK);
    }

    /**
     * Get a badge by id, set to -1 to get all badges
     *
     * @param Request $request
     * @param $badgeId
     * @return JsonResponse
     */
    public function getAction(Request $request, $badgeId)
    {
        $badgeId = intval($badgeId);
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $badgeRepo = $em->getRepository('EdcomsCMSBadgeBundle:BadgeBase');

        if ($badgeId === -1) {//get all badges
            $badges = $badgeRepo->findAll();
            if (count($badges) > 0) {//replace objects with json
                foreach($badges as $k => $badge) {
                    $badges[$k] = $badge->toJSON();
                }
            }

            return new JsonResponse($badges, JsonResponse::HTTP_OK);

        } else {//get one badge
            $badge = $badgeRepo->find($badgeId);

            if (!$badge) {
                return new JsonResponse('badge not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return new JsonResponse($badge->toJSON(), JsonResponse::HTTP_OK);
        }
    }

    /**
     * Get badges awarded to a user or a collection of users
     *
     * @param Request $request
     * @param $userId
     * @return JsonResponse
     */
    public function getByUserAction(Request $request, $userId)
    {
        $userId = intval($userId);
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $userRepo = $em->getRepository('EdcomsCMSAuthBundle:cmsUsers');
        $badgeHelper = $this->get('badge_helper');

        if ($userId === -1) {
            //no id supplied, check for collection of ids in post data
            if ($request->request->has('userIds')) {
                //ids param supplied
                $ids = json_decode($request->request->get('userIds'));
                if (count($ids) > 0) {
                    //at least one id supplied
                    $badgeAwardsArray = [];
                    $errors = [];
                    foreach ($ids as $id) {
                        $user = $userRepo->find($id);
                        if ($user) {
                            //user exists
                            $badgeAwardsArray[$id] = $badgeHelper->getBadgeAwardsForUser($user, $em);
                        } else {
                            //no user
                            $errors['no user with id'] = $id;
                        }
                    }
                    return new JsonResponse([
                        'badgeAwards' => $badgeAwardsArray,
                        'errors' => $errors
                    ], JsonResponse::HTTP_OK);
                } else {
                    return new JsonResponse('userIds post parameter empty', JsonResponse::HTTP_BAD_REQUEST);
                }

            } else {
                return new JsonResponse('userIds post parameter missing', JsonResponse::HTTP_BAD_REQUEST);
            }

        } else {
            //get badges for single user
            $user = $userRepo->find($userId);
            if ($user) {
                //success
                $badgeAwards = $badgeHelper->getBadgeAwardsForUser($user, $em);

                return new JsonResponse(['badgeAwards' => $badgeAwards], JsonResponse::HTTP_OK);
            } else {
                return new JsonResponse('user not found', JsonResponse::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * Award a badge to a user
     *
     * @param Request $request
     * @param $badgeId - integer
     * @param $userId - integer
     * @return JsonResponse
     */
    public function awardToUserAction(Request $request, $badgeId, $userId)
    {
        $badgeId = intval($badgeId);
        $userId = intval($userId);
        $em = $this->getDoctrine()->getManager('edcoms_cms');

        $badgeRepo = $em->getRepository('EdcomsCMSBadgeBundle:BadgeBase');
        $badge = $badgeRepo->find($badgeId);
        if (!$badge) {
            return new JsonResponse('badge not found', JsonResponse::HTTP_NOT_FOUND);
        }

        $userRepo = $em->getRepository('EdcomsCMSAuthBundle:cmsUsers');
        $user = $userRepo->find($userId);
        if (!$user) {
            return new JsonResponse('user not found', JsonResponse::HTTP_NOT_FOUND);
        }

        $badgeHelper = $this->get('badge_helper');
        $badgeAward = $badgeHelper->awardBadgeToUser($user, $badge, new \DateTime(), $em);

        return new JsonResponse($badgeAward->toJSON(), JsonResponse::HTTP_OK);
    }

    /**
     * Get a list of badge criteria defined for this site
     *
     * @return JsonResponse
     */
    public function getCriteriaListAction()
    {
        if ($this->container->hasParameter('badges')) {
            return new JsonResponse($this->container->getParameter('badges'), JsonResponse::HTTP_OK);
        }

        return new  JsonResponse('badges not defined', JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Award all active badges
     * If recalculate is included in the post data then all previous awards will be deleted
     * before assessing if the active badge should be awarded
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function awardAllAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        //get all badges and loop through
        $badgeRepo = $em->getRepository('EdcomsCMSBadgeBundle:BadgeBase');
        $badges = $badgeRepo->findBy( [ 'isActive'=>true ] );

        if (count($badges) > 0) {//if there are badges
            $resp = [];
            foreach ($badges as $badge) {
                if ($request->request->has('recalculate')) {//delete existing badge awards
                    $badgeAwardsRepo = $em->getRepository('EdcomsCMSBadgeBundle:BadgeAward');
                    $badgeAwardsRepo->deleteByBadge($badge);
                }

                //assess badge
                $badgeHelper = $this->get('badge_helper');
                if ($badgeHelper->awardBadge($badge, $em)) {
                    $resp[$badge->getId()] = 'success';
                } else {
                    $resp[$badge->getId()] = 'no users in group';
                }
            }

            return new JsonResponse($resp, JsonResponse::HTTP_OK);

        } else {
            return new JsonResponse('no badges', JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * Award a badge
     * If recalculate is included in the post data then all previous awards will be deleted
     * before assessing if the badge should be awarded
     *
     * @param Request $request
     * @param $badgeId - integer
     * @return JsonResponse
     */
    public function awardBadgeAction(Request $request, $badgeId)
    {
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $badgeId = intval($badgeId);
        $badgeSimpleRepo = $em->getRepository('EdcomsCMSBadgeBundle:BadgeSimple');
        $badgeSimple = $badgeSimpleRepo->find($badgeId);

        if ($badgeSimple) {
            if ($request->request->has('recalculate')) {//delete existing badge awards
                $badgeAwardsRepo = $em->getRepository('EdcomsCMSBadgeBundle:BadgeAward');
                $badgeAwardsRepo->deleteByBadge($badgeSimple);
            }
            $badgeHelper = $this->get('badge_helper');
            if ($badgeHelper->awardBadge($badgeSimple, $em)) {
                return new JsonResponse('success', JsonResponse::HTTP_OK);
            } else {
                return new JsonResponse('no users in group', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }

        } else {
            return new JsonResponse('badge not found', JsonResponse::HTTP_NOT_FOUND);
        }
    }
}