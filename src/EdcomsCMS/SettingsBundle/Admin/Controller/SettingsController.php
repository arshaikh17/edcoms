<?php
/**
 * Created by Redi Linxa
 * Date: 22.11.19
 * Time: 09:31
 */

namespace EdcomsCMS\SettingsBundle\Admin\Controller;


use Dmishh\SettingsBundle\Entity\SettingsOwnerInterface;
use Dmishh\SettingsBundle\Form\Type\SettingsType;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SettingsController extends CRUDController
{
    public function listAction()
    {
        $request = $this->getRequest();

        $this->admin->checkAccess('list');

        return $this->manage($request);
    }
    /**
     * @param Request $request
     * @param SettingsOwnerInterface|null $owner
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function manage(Request $request, SettingsOwnerInterface $owner = null)
    {
        $form = $this->get('edcoms.settings')->getForm();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->get('edcoms.settings')->setMany($form->getData(), $owner);
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('settings_updated', array(), 'settings')
                );

                return $this->redirect($request->getUri());
            }
        }

        return $this->render(
            $this->container->getParameter('edcoms.settings_manager.template'),
            array(
                'settings_form' => $form->createView(),
            )
        );
    }
}