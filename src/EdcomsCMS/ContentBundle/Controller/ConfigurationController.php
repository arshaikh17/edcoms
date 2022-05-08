<?php

namespace EdcomsCMS\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\ContentType;
use EdcomsCMS\ContentBundle\Entity\MediaTypes;

use EdcomsCMS\ContentBundle\Form\Media\MediaTypeCreate;

class ConfigurationController extends Controller
{
    public function indexAction()
    {
        return $this->render(
            'EdcomsCMSTemplatesBundle:Configuration:index.html.twig',
            [
                'title'=> 'Configuration'
            ]
        );
    }
    /**
     * @Route("/cms/settings/target/update/{id}", defaults={"id"=-1})
     * @return JsonResponse
     */
    public function updateTargetAction($id=-1, Request $request)
    {
        $id = (int)$id;
        if ($id === -1) {
            $MediaType = new MediaTypes();
        } else {
            $MediaTypes = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:MediaTypes');
            $MediaType = $MediaTypes->find($id);
            if (!$MediaType) {
                return new JsonResponse(['errors'=>'media_type_not_found'], 404);
            }
        }
        return $this->processForm($MediaType, $request);
    }
    
    /**
     * 
     * @Route("/cms/settings/target/get")
     * @return JsonResponse
     */
    public function getTargetAction() {
        $MediaTypes = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:MediaTypes');
        $MediaTypesList = $MediaTypes->findAll();
        $MediaTypesJSON = [];
        foreach ($MediaTypesList as $MediaType) {
            $MediaTypesJSON[] = $MediaType->toJSON();
        }
        return new JsonResponse(['media_types'=>$MediaTypesJSON]);
    }
    
    private function processForm(MediaTypes $MediaType, Request $request)
    {
        $form = $this->createForm(MediaTypeCreate::class, $MediaType);
        $resp = new JsonResponse([]);
        $cmsFields = $this->get('CMSFields');
        if ($request->isMethod('POST')) {
            $jsondata = json_decode($request->getContent(), true);
            $request->request->replace($jsondata);
            if ($form->get('id')->getData() !== $MediaType->getId()) {
                $data = ['status'=>0, 'errors'=>'ID_mismatch'];
                $status = 400;
            }
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager('edcoms_cms');
                $em->persist($MediaType);
                $em->flush();
                $data = ['status'=>1, 'data'=>$MediaType->toJSON()];
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
            $token = $csrf->refreshToken('MediaTypeCreate');
//            return $this->render('EdcomsCMSTemplatesBundle:test:form.html.twig',
//                [
//                    'form'=>$form->createView()
//                ]);
            $resp = new JsonResponse([
                'data'=>['media_type'=>(!is_null($MediaType)) ? $MediaType->toJSON() : null, 'required'=>$required, 'mimetype'=>$cmsFields->get('mimetype') ],
                'token'=>$token->__toString()
            ], 200);
        }
        return $resp;
    }
}
