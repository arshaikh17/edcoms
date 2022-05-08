<?php

namespace EdcomsCMS\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Doctrine\ORM\PersistentCollection;
use EdcomsCMS\ContentBundle\Entity\Structure;


class StructureController extends Controller
{
    /**
     * @Route("/cms/structure/deletedlist")
     * @return JsonResponse
     */
    public function deletedlistAction() {
        $structures = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure');
        $structureList = $structures->findBy(['deleted' => true]);
        $structureArr = [];
        foreach ($structureList as $structure) {
            $structureArr[] = $structure->toJSON(['id', 'link', 'priority', 'title', 'content', 'addedOn']);
        }
        return new JsonResponse($structureArr);
    }

    /**
     *
     * @Route("/cms/structure/restore/{id}")
     * @param int $id
     */
    public function restoreAction($id) {
        $id = (int) $id;
        if ($id === -1) {
            return new Exception('An ID is required');
        } else {
            $em = $this->getDoctrine()->getManager('edcoms_cms');
            $structures = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure');
            $structure = $structures->find($id);
            $structure->setDeleted(false);
            $em->persist($structure);
            $em->flush();
        }
        return $this->deletedlistAction();
    }

    /**
     * @Route("/cms/structure/{parent}/{type_id}", defaults={"parent" = 0, "type_id" = 0})
     * @Method({"GET"})
     */
    public function indexAction($parent)
    {
        $accessControl = $this->get('AccessControl');
        $structures = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure');
        if (intval($parent) === 0) {
            $structure = $structures->findOneBy(['deleted'=>false], ['id'=>'ASC']);
        } else {
            $structure = $structures->findOneBy(['id'=>$parent,'deleted'=>false]);
        }
        $rating = new \EdcomsCMS\ContentBundle\Helpers\RatingHelper($this->getDoctrine(), $structure);
        return new JsonResponse((!is_null($structure)) ? array_merge($structure->toJSON(['id', 'link', 'parent', 'priority', 'title', 'content', 'addedOn', 'children', 'rateable', 'master','visible']), ['rating'=>$rating->GetAverage()]) : null);
    }
    public function moveAction($id, Request $request) {
        $id = (int)$id;
        if ($id === -1) {
            return new Exception('An ID is required');
        } else {
            $structures = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure');
            $structure = $structures->find($id);
        }
        return $this->processForm($structure, $request);
    }
    private function processForm($structure, $request) {
        $form = $this->createForm('StructureCreate', $structure);
        if ($request->isMethod('POST')) {
            $jsondata = json_decode($request->getContent(), true);
            $request->request->replace($jsondata);
            $form->handleRequest($request);
            $data = $form->getData();
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager('edcoms_cms');
                $em->persist($structure);
                $em->flush();
                $data = ['status'=>1, 'data'=>$structure->toJSON()];
                $status = 200;
            } else {
                $data = ['errors'=>$this->get('form_errors')->getArray($form), 'status'=>0];
                $status = 400;
            }
            $resp = new JsonResponse($data, $status);
        } else {
            $elems = $form->all();
            $required = [];
            foreach ($elems as $elem) {
                if ($elem->isRequired()) {
                    $required[$elem->getName()] = true;
                }
            }
            $csrf = $this->get('security.csrf.token_manager');
            $token = $csrf->refreshToken('UserCreate');
            $resp = new JsonResponse([
                'data'=>['structure'=>(!is_null($structure)) ? $structure->toJSON() : null, 'required'=>$required],
                'token'=>$token->__toString()
            ], 200);
        }
        return $resp;
    }
    /**
     * @Route("/cms/structure/delete/check/{id}")
     * @param integer $id
     */
    public function deleteCheck($id)
    {
        $data = ['status'=>0, 'errors'=>'incorrect_method'];
        $status = 500;
        $structures = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure');
        $structure = $structures->find($id);
        if (!is_null($structure)) {
            $data = ['status'=>1, 'children'=>false, 'title'=>$structure->getTitle()];
            if ($structure->getChildren()->count() > 0) {
                $data['children'] = true;
            }
            $status = 200;
        } else {
            $data = ['status'=>0, 'errors'=>'not_found'];
            $status = 404;
        }
        $resp = new JsonResponse($data, $status);
        return $resp;
    }

    /**
     *
     * @Route("/cms/structure/delete/{id}/{mode}")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAction($id, $mode, Request $request)
    {
        $data = ['status'=>0, 'errors'=>'incorrect_method'];
        $status = 500;
        if ($request->isMethod('DELETE')) {
            $structures = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:Structure');
            $structure = $structures->find($id);
            if (!is_null($structure)) {
                $parent = $structure->getParent();
                switch ($mode) {
                    case "single":
                        $em = $this->getDoctrine()->getManager('edcoms_cms');
                        $structureC = $structure->getChildren();
                        $structureC->forAll(function($id, $item) use ($parent, $em) {
                            $item->setParent($parent);
                            $em->persist($item);
                            return true;
                        });

                        //$em->remove($structure);
                        $structure->setDeleted(true);
                        $em->persist($structure);
                        $em->flush();
                        break;
                    case "recursive":
                        $this->recursiveDelete([$structure]);
                        break;
                }

                $data = ['status'=>1];
                $status = 200;
            } else {
                $data = ['status'=>0, 'errors'=>'not_found'];
                $status = 404;
            }
        }
        $resp = new JsonResponse($data, $status);
        return $resp;
    }
    private function recursiveDelete($structures) {
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        foreach ($structures as $structure) {
            // find the children of this \\
            $this->recursiveDelete($structure->getChildren());
            //$em->remove($structure);
            $structure->setDeleted(true);
            $em->persist($structure);
        }
        $em->flush();
    }
}
