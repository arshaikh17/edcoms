<?php

namespace EdcomsCMS\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use EdcomsCMS\AuthBundle\Entity\cmsUsers;
use EdcomsCMS\AuthBundle\Entity\Person;
use EdcomsCMS\AuthBundle\Entity\Contact;
use EdcomsCMS\AuthBundle\Security\AccessControl;
use EdcomsCMS\ContentBundle\Form\User\UserCreate;

class UsersController extends Controller
{
    public function existsUsername($username) {
        $users = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSAuthBundle:cmsUsers');
        $user = $users->findOneBy(['username'=>strtolower($username)]);
        if ($user) {
            return true;
        }
        return false;
    }
    public function indexAction()
    {
        return $this->render(
            'EdcomsCMSTemplatesBundle:Users:index.html.twig',
            [
                'title'=>'Users'
            ]
        );
    }
    public function getAction()
    {
        $users = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSAuthBundle:cmsUsers');
        $userList = $users->findAll();
        $userJSON = [];
        foreach ($userList as $user) {
            $userJSON[] = $user->toJSON(['id', 'person']);
        }
        return new JsonResponse(['users'=>$userJSON]);
    }
    public function updateAction($id, Request $request)
    {
        $id = (int)$id;
        if ($id === -1) {
            $user = new cmsUsers();
        } else {
            $users = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSAuthBundle:cmsUsers');
            $user = $users->findOneBy(['id'=>$id,'deleted'=>0]);
        }
        return $this->processForm($user, $request);
    }
    public function deleteAction($id, Request $request)
    {
        $data = ['status'=>0, 'errors'=>'incorrect_method'];
        $status = 500;
        if ($request->isMethod('DELETE')) {
            $users = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSAuthBundle:cmsUsers');
            $user = $users->find($id);
            if (!is_null($user)) {
                $em = $this->getDoctrine()->getManager('edcoms_cms');
                $user->setDeleted(1);
                $em->persist($user);
                $em->flush();
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
    /**
     * @Route("/cms/users/export/{type}")
     */
    public function exportAction($type) {
        // get the users with type info specified (such as spirit) \\
        $users = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSAuthBundle:cmsUsers');
        $userList = $users->findAll();
        $data = [];
        switch ($type) {
            case "spirit":
                $data = $this->getSpiritInfo($userList);
                break;
        }
        $response = new StreamedResponse(function() use ($data) {
            $handle = fopen('php://output', 'r+');
            if (!empty($data)) {
                $first = true;
                $headArr = [];
                foreach ($data as $row) {
                    $rowArr = [];
                    if (is_object($row) || is_array($row)) {
                        if ($first) {
                            foreach ($row as $field=>$value) {
                                $headArr[] = $field;
                            }
                            fputcsv($handle, $headArr);
                            $first = false;
                        }
                        foreach ($headArr as $field) {
                            if (is_object($row) && isset($row->{$field})) {
                                $rowArr[] = $row->{$field};
                            } else if (is_array($row) && isset($row[$field])) {
                                $rowArr[] = $row[$field];
                            } else {
                                $rowArr[] = '';
                            }
                        }
                        fputcsv($handle, $rowArr);
                    }
                }
                fclose($handle);
            }
        });
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'filename="export-'.date('Y-m-d H:i:s').'.csv"');
        return $response;

    }
    private function processForm($user, $request) {
        $form = $this->createForm(UserCreate::class, $user);
        if ($request->isMethod('POST')) {
            // check the ID matches the URL \\
            $jsondata = json_decode($request->getContent(), true);
            $request->request->replace($jsondata);
            $form->handleRequest($request);

            $originalPassword = $user->getPassword();

            if ($form->get('id')->getData() !== $user->getId()) {
                $data = ['status'=>0, 'errors'=>'ID_mismatch'];
                $status = 400;
            }
            if ($form->isValid()) {
                if (!empty($form->get('password')->getData())) {
                    $encoder = $this->container->get('security.password_encoder');
                    $encoded = $encoder->encodePassword($user, $form->get('password')->getData());
                    $user->setPassword($encoded);
                } else {
                    $user->setPassword($originalPassword);
                }
                $em = $this->getDoctrine()->getManager('edcoms_cms');
                $em->persist($user);
                $em->flush();
                $data = ['status'=>1, 'data'=>$user->toJSON()];
                $status = 200;
            } else {
                $data = ['errors'=>$this->get('form_errors')->getArray($form), 'status'=>0];
                $status = 400;
            }
            $resp = new JsonResponse($data, $status);
        } else {
            $groups = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSAuthBundle:cmsUserGroups');

            $groupList = [];
            foreach ($groups->findAll() as $group) {
                $groupList[] = $group->toJSON();
            }

            $elems = $form->all();
            $required = [];
            foreach ($elems as $elem) {
                if ($elem->isRequired()) {
                    $required[$elem->getName()] = true;
                }
            }
            $csrf = $this->get('security.csrf.token_manager');
            $token = $csrf->refreshToken('UserCreate');
            //return new Response($token);
            /*return $this->render('EdcomsCMSTemplatesBundle:test:form.html.twig',
                    [
                        'form'=>$form->createView()
                    ]);*/
            $resp = new JsonResponse([
                    'data'=>['user'=>(!is_null($user)) ? $user->toJSON() : null, 'groups'=>$groupList, 'required'=>$required],
                    'token'=>$token->__toString()
                ], 200);
        }
        return $resp;
    }
    public function getSpiritInfo($users)
    {
        $spirit = $this->get('SPIRITRegistration');
        $spiritArr = [];
        if (!is_null($spirit)) {
            foreach ($users as $user) {
                $spiritID = $user->getPerson()->getContacts()->filter(function($type) {
                    return ($type->getType() === 'spirit_id');
                });
                $educationNumber = $user->getPerson()->getContacts()->filter(function($type) {
                    return $type->getType() === 'educationNumber';
                });
                if (count($spiritID) === 1) {
                    $spiritDefault = [
                        'First Name'=>$user->getPerson()->getFirstName(),
                        'Last Name'=>$user->getPerson()->getLastName(),
                        'Email'=>$user->getPerson()->getContacts()->filter(function($type) {return ($type->getType() === 'email');})->first()->getValue(),
                        'Establishment Name'=>'SPIRIT Error - no data recorded',
                        'Address 1'=>'SPIRIT Error - no data recorded',
                        'Address 2'=>'SPIRIT Error - no data recorded',
                        'Address 3'=>'SPIRIT Error - no data recorded',
                        'Town'=>'SPIRIT Error - no data recorded',
                        'County'=>'SPIRIT Error - no data recorded',
                        'Postcode'=>'SPIRIT Error - no data recorded',
                        'Country'=>'SPIRIT Error - no data recorded',
                        'Telephone'=>'SPIRIT Error - no data recorded',
                        'SPIRIT ID'=>$spiritID->first()->getValue()
                    ];
                    if (count($educationNumber) === 1 && $educationNumber->first()->getValue() !== '0') {
                        $establishment = $spirit->getRegisteredOrganisation($educationNumber->first()->getValue());
                        if (is_object($establishment)) {
                            $spiritArr[] = [
                                'First Name'=>$user->getPerson()->getFirstName(),
                                'Last Name'=>$user->getPerson()->getLastName(),
                                'Email'=>$user->getPerson()->getContacts()->filter(function($type) {return ($type->getType() === 'email');})->first()->getValue(),
                                'Establishment Name'=>$establishment->EstablishmentName,
                                'Address 1'=>$establishment->Address1,
                                'Address 2'=>(isset($establishment->Address2)) ? $establishment->Address2 : '',
                                'Address 3'=>(isset($establishment->Address3)) ? $establishment->Address3 : '',
                                'Town'=>$establishment->Town,
                                'County'=>(isset($establishment->County)) ? $establishment->County : '',
                                'Postcode'=>(isset($establishment->Postcode)) ? $establishment->Postcode : '',
                                'Country'=>(isset($establishment->Country)) ? $establishment->Country : '',
                                'Telephone'=>(isset($establishment->Telephone)) ? $establishment->Telephone : '',
                                'Establishment Type'=>(isset($establishment->EstablishmentTypeDescription)) ? $establishment->EstablishmentTypeDescription : '',
                                'SPIRIT ID'=>$spiritID->first()->getValue()
                            ];
                        }
                    } else if (count($spiritID) === 1 && $spiritID->first()->getValue() !== '0') {
                        $spiritVal = $spirit->getRegisteredUser($spiritID->first()->getValue());
                        if (is_object($spiritVal)) {
                            $establishment = (isset($spiritVal->EducationNumber)) ? $spirit->getRegisteredOrganisation($spiritVal->EducationNumber) : false;
                            $spiritArr[] = [
                                'First Name'=>$user->getPerson()->getFirstName(),
                                'Last Name'=>$user->getPerson()->getLastName(),
                                'Email'=>$user->getPerson()->getContacts()->filter(function($type) {return ($type->getType() === 'email');})->first()->getValue(),
                                'Establishment Name'=>(isset($spiritVal->EstablishmentName)) ? $spiritVal->EstablishmentName : '',
                                'Address 1'=>$spiritVal->Address1,
                                'Address 2'=>(isset($spiritVal->Address2)) ? $spiritVal->Address2 : '',
                                'Address 3'=>(isset($spiritVal->Address3)) ? $spiritVal->Address3 : '',
                                'Town'=>$spiritVal->Town,
                                'County'=>(isset($spiritVal->County)) ? $spiritVal->County : '',
                                'Postcode'=>(isset($spiritVal->Postcode)) ? $spiritVal->Postcode : '',
                                'Country'=>(isset($spiritVal->Country)) ? $spiritVal->Country : '',
                                'Telephone'=>(isset($spiritVal->Telephone)) ? $spiritVal->Telephone : '',
                                'Establishment Type'=> (is_object($establishment)) ? $establishment->EstablishmentTypeDescription : '',
                                'SPIRIT ID'=>$spiritID->first()->getValue()
                            ];
                        } else {
                            $spiritArr[] = $spiritDefault;
                        }
                    } else {
                        $spiritArr[] = $spiritDefault;
                    }
                }
            }
        }
        return $spiritArr;
    }
    /**
     *
     * @Route("/cms/users/import")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function userImport(Request $request)
    {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('data');
            $headers = $request->get('headers');
            $first = false;
            $columns = [
                'username',
                'password',
                'is_active',
                'first_name',
                'last_name'
            ];
            $contactArr = [];
            $contacts = $request->get('contacts');
            if (is_array($contacts)) {
                foreach ($contacts as $contact) {
                    if (is_array($contact)) {
                        $contactsArr[$contact['name']] = $contact['title'];
                        $columns[] = $contact['name'];
                    }
                }
            }
            $entries = [];
            if ($file) {
                $row = 1;
                if (($fh = fopen($file->getPathName(), 'r')) !== false) {
                    if ($headers) {
                        $first = true;
                    }
                    while (($csv = fgetcsv($fh)) !== false) {
                        if ($first) {
                            $columns = $csv;
                            $first = false;
                            continue;
                        }
                        $num = count($csv);
                        $row++;
                        $user = new cmsUsers();

                        if (!$this->existsUsername($csv[array_search('username', $columns)])) {
                            $isActive = false;
                            if ($csv[array_search('is_active', $columns)] === '1') {
                                $isActive = true;
                            }
                            $user->setIsActive($isActive);
                            $user->setPassword($csv[array_search('password', $columns)]);
                            $user->setUsername($csv[array_search('username', $columns)]);

                            $person = new Person();
                            $person->setFirstName($csv[array_search('first_name', $columns)]);
                            $person->setLastName($csv[array_search('last_name', $columns)]);
                            $i=0;
                            foreach ($contactsArr as $name=>$val) {
                                $contact = new Contact();
                                $contact->setTitle($val);
                                $contact->setType($name);
                                $contact->setValue($csv[array_search($name, $columns)]);
                                $person->addContact($contact);
                            }
                            $user->setPerson($person);
                            $entries[] = $user;
                        }
                    }
                    $em = $this->getDoctrine()->getManager('edcoms_cms');
                    foreach ($entries as $entry) {
                        $em->persist($entry);
                    }
                    $em->flush();
                }
            }
        }
        return $this->render('EdcomsCMSTemplatesBundle:Users:import.html.twig');
    }
    /**
     *
     * @Route("/cms/users/import/prep")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function prepareUserList(Request $request)
    {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('data');
            $headers = $request->get('headers');
            $mapping = $request->get('mapping');
            $fields = [];
            $keys = [];
            $data = [];
            if (is_array($mapping)) {
                foreach ($mapping as $map) {
                    $fields[$map['to']] = $map['from'];
                }
            }
            if ($file) {
                $row = 0;
                if (($fh = fopen($file->getPathName(), 'r')) !== false) {
                    if ($headers) {
                        $first = true;
                    }
                    while (($csv = fgetcsv($fh)) !== false) {
                        if ($first) {
                            foreach ($fields as $i=>$f) {
                                $keys[$i] = array_search($f, $csv);
                            }
                            $first = false;
                            continue;
                        }
                        $data[$row] = [];
                        foreach ($keys as $head=>$ind) {
                            $data[$row][$head] = $csv[$ind];
                        }
                        $row++;
                    }
                }

            }
            $date = new \DateTime();
            header("Content-disposition: attachment; filename=user_import_{$date->format('YmdHis')}.csv");
            header("Content-Type: text/csv");
            $fh = fopen('php://output', 'r+');
            fputcsv($fh, array_keys($keys));
            foreach ($data as $nrow) {
                fputcsv($fh, $nrow);
            }
            fclose($fh);
            exit();
        }
        return $this->render('EdcomsCMSTemplatesBundle:Users:import_preparation.html.twig');
    }

    /**
     * Check the current user has a given privilege on a given context
     *
     * @Route("/cms/users/check_perm/{context}/{name}")
     * @Method({"GET"})

     * @param string $context - the item to check the permission on
     * @param string $name - the permission to check for
     * @return JsonResponse
     */
    public function checkPerm($context, $name)
    {
        //Set up access control object
        $accessControl = $this->get('AccessControl');

        //Check for permission and return result
        $access = $accessControl->has_permission($context, $name);

        return new JsonResponse(['access'=>$access]);
    }

    /**
     * Get all permissions for the logged in user
     *
     * @Route("/cms/users/get_perms")
     * @Method({"GET"})
     * @return JsonResponse
     */
    public function getPerms() {
        //Set up access control object
        $accessControl = $this->get('AccessControl');

        //Get the list of permissions for current user and return as json
        $permissions = $accessControl->get_permissions();

        if (sizeof($permissions) > 0) {
            //build up return array
            $returnPermissions = [];
            //for ease of use on the FE the permissions are converted to objects
            foreach($permissions as $permission) {
                if (!isset($returnPermissions[$permission->getContext()])) {
                    $returnPermissions[$permission->getContext()] = [];
                }
                $returnPermissions[$permission->getContext()][$permission->getName()] = $permission->getValue();
            }
        } else {
            $returnPermissions = (object)[];
        }
        
        // RW update - leave as an array - json_encode will cast to an object in the string, and saves memory overhead \\
        //make return an object for consistency on FE
//        $returnPermissions = (object) $returnPermissions;

        //add current user information
        $user = $this->getUser();

        return new JsonResponse([
            'permissions'=>$returnPermissions,
            'defaultPermission'=>$accessControl->get_user_default_permission(),
            'currentUser'=>$user->toJSON()
        ]);
    }
    public function getdeletedAction()
    {
        $users = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSAuthBundle:cmsUsers');
        $userList = $users->findAll(true);
        $userJSON = [];
        foreach ($userList as $user) {
            $userJSON[] = $user->toJSON(['id', 'person']);
    }
        return new JsonResponse(['users'=>$userJSON]);
    }
    public function restoreAction($id)
    {
        $data = ['status'=>0, 'errors'=>'incorrect_method'];
        $status = 500;
            $users = $this->getDoctrine()->getManager('edcoms_cms')->getRepository('EdcomsCMSAuthBundle:cmsUsers');
            $user = $users->find($id);
            if (!is_null($user)) {
                $em = $this->getDoctrine()->getManager('edcoms_cms');
                $user->setDeleted(0);
                $em->persist($user);
                $em->flush();
                $data = ['status'=>1];
                $status = 200;
            } else {
                $data = ['status'=>0, 'errors'=>'not_found'];
                $status = 404;
            }
        $resp = new JsonResponse($data, $status);
        return $resp;
    }
}
