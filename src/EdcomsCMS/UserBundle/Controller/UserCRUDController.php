<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\UserBundle\Controller;


use EdcomsCMS\UserBundle\Entity\User;
use EdcomsCMS\UserBundle\Form\Type\RTBFUserType;
use EdcomsCMS\UserBundle\Service\RTBFUserService;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserCRUDController extends CRUDController {

    /**
     * @var \EdcomsCMS\UserBundle\Service\RTBFUserService
     */
    private $RTBFUserService;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * UserCRUDController constructor.
     *
     * @param \EdcomsCMS\UserBundle\Service\RTBFUserService $RTBFUserService
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationChecker $authorizationChecker
     */
    public function __construct(RTBFUserService $RTBFUserService, AuthorizationChecker $authorizationChecker) {
        $this->RTBFUserService = $RTBFUserService;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function rtbfAction($id, Request $request){

        if(!$this->authorizationChecker->isGranted('ROLE_RTBF')){
            throw new AccessDeniedException('Access denied');
        }

        $user = $this->admin->getSubject();

        if (!$user || !($user instanceof User)) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        /** @var User $user */
        if(!$this->RTBFUserService->isRTBFAllowed($user)){
            throw new AccessDeniedHttpException(sprintf('RTBF cannot be applied to user with id:  %s', $id));
        }

        $form = $this->container->get('form.factory')->create(
            RTBFUserType::class, $user, ['userId'=> $user->getId()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('rtbf_user')->isClicked()) {
                $this->RTBFUserService->applyRTBF($user);

                $this->addFlash(
                    'sonata_flash_success',
                    'Right to be forgotten has been applied to the user successfully.'
                );

                return $this->redirectToRoute('sonata.admin_list');
            }
        }

        $userOverview = $this->RTBFUserService->getUserOverview($user);
        return $this->render('@EdcomsCMSUser/Admin/User/Controller/rtbf_user.html.twig', [
            'form' => $form->createView(),
            'object' => $user,
            'action' => 'rtbf',
            'userOverview' => $userOverview
        ]);
    }

    public function rtbfUserCheckAction(Request $request){
        $form = $this->createFormBuilder()
            ->add('username', TextType::class,[
                'attr' => array(
                    'placeholder' => 'Type username...'
                )
            ])
            ->add('search', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        $isRTBFApplied = null;
        $username = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->getData()['username'];
            $isRTBFApplied = $this->RTBFUserService->isRTBFApplied($username);
        }

        return $this->render('@EdcomsCMSUser/Admin/User/Controller/rtbf_user_check.html.twig', [
            'form' => $form->createView(),
            'isRTBFApplied' => $isRTBFApplied,
            'username' => $username
        ]);
    }
}
