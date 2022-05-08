<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Admin;

use EdcomsCMS\ContentBundle\Entity\Structure;
use EdcomsCMS\ContentBundle\Form\Type\Content\ContentFieldsType;
use EdcomsCMS\ContentBundle\Form\Type\Structure\StructureType;
use EdcomsCMS\ContentBundle\Service\Content\ContentService;
use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\ContentType;
use EdcomsCMS\ContentBundle\Entity\TemplateFiles;
use EdcomsCMS\ContentBundle\Service\EdcomsContentConfigurationService;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Validator\Constraints\Valid;

class ContentAdmin extends AbstractAdmin
{

    /** @var ContentService  $contentService */
    private $contentService;

    /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage  */
    private $tokenStorage;

    /** @var EdcomsContentConfigurationService $confService */
    private $confService;

    /**
     * @var bool
     * @TODO enable that when Content Preview development starts
     */
    public $supportsPreviewMode = false;

    /**
     *
     * Maps Role name to ContentType id
     * For example [ROLE_APP_ADMIN_CONTENT_SPONSOR]=3
     *
     * @var array
     */
    private $contentPermissionsMapping;

    /**
     * ContentAdmin constructor.
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     * @param Container $container
     */
    public function __construct($code, $class, $baseControllerName, Container $container)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->contentService = $container->get('edcoms.content.service.content_service');
        $this->tokenStorage = $container->get('security.token_storage');
        $this->confService = $container->get('edcoms.content.service.configuration');
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
//        $query->andWhere("o.status='published'");
        return $query;
    }

//    R&D for Content ACL -
//    public function createQuery($context = 'list')
//    {
//        $query = parent::createQuery($context);
//        /** @var User $user*/
//        $user = $this->tokenStorage->getToken()->getUser();
//        if(!$user->hasRole("ROLE_SONATA_SUPER_ADMIN") && !$user->hasRole("ROLE_APP_ADMIN_CONTENT_ALL")){
//            $allowedContentTypes = array();
//            foreach ($user->getRoles() as $role){
//                if(array_key_exists($role,$this->contentPermissionsMapping)){
//                    $allowedContentTypes[] = $this->contentPermissionsMapping[$role];
//                }
//            }
//            if(count($allowedContentTypes)!=0){
//                $query->andWhere(
//                    $query->expr()->in('o.contentType',$allowedContentTypes)
//                );
//            }else{
//                throw new AccessDeniedException();
//            }
//        }
//
//        return $query;
//    }
//
//    public function setSecurityInformation(array $information)
//    {
//        $contentTypes = $this->getModelManager()->findBy(ContentType::class);
//
//        $contentTypesPermissions = array();
//        foreach ($contentTypes as $ct) {
//            /** @var ContentType $ct */
//            $permissionName = strtoupper(preg_replace('/\s+/', '', $ct->getName()));
//            $contentTypesPermissions[$permissionName] = $permissionName;
//            $this->contentPermissionsMapping[sprintf("ROLE_APP_ADMIN_CONTENT_%s",$permissionName)] = $ct->getId();
//        }
//        parent::setSecurityInformation(array_merge($information, $contentTypesPermissions));
//    }

    /**
     * @param string $action
     * @param mixed  $object
     *
     * @return array
     */
    public function getActionButtons($action, $object = null)
    {
        $list = parent::getActionButtons($action,$object);
        unset($list['create']);
        return $list;
    }

    public function create($object){
        /** @var Content $object */
        $structure = $object->getStructure();
        if(!$this->confService->getStructureVisibility()){
            $structure->setVisible(false);
        }
        $structure->setRateable(false);
        $object->setTitle($structure->getTitle());
        $object->setAddedBy($this->tokenStorage->getToken()->getUser());

        return parent::create($object);
    }

    public function update($object){
        /** @var Content $object */
        $object->setTitle($object->getStructure()->getTitle());
        $object->setUpdatedBy($this->tokenStorage->getToken()->getUser());
        $object->setUpdatedOn(new \DateTime());
        return parent::update($object);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Content $content */
        $content = $this->getSubject();

        // Content Update
        if($content->getId()){
            $contentType = $content->getContentType();
            if(!$this->isSymlinkContent($contentType)){
                $this->contentService->syncContentFields($content);
            }
            // Content Creation
        }else{
            $contentTypeParameter = $this->getRequest()->get('content_type');
            $contentType = $this->modelManager->find(ContentType::class,$contentTypeParameter);
            if(!$contentType){
                throw new \Exception("Content type not exist");
            }
            /** @var ContentType $contentType */
            $this->setSubject($this->contentService->initContent($contentType, $content));
        }


        $em = $this->modelManager->getEntityManager(TemplateFiles::class);
        $templateFilesQuery = $em->createQueryBuilder()
            ->select("templateFiles")
            ->from("EdcomsCMSContentBundle:TemplateFiles", "templateFiles")
            ->leftJoin("templateFiles.contentTypes", "contentTypes")
            ->where("contentTypes.id=:id")
            ->setParameter("id",$contentType->getId())
            ->getQuery()
        ;

        if($this->isContextEnabled()){
            $contextLabel = $this->contentService->getStructureContextEntities()[$content->getContentType()->getContext()]->getLabel();

            $formMapper
                ->tab($contextLabel, array(
                    'contentContext' => true
                ))
                ->with("Context", array('class'=>'col-md-12'))
                ->end()
                ->end();
        }

        $formMapper
            ->tab('Details')
            ;
        if(!$this->isSymlinkContent($contentType)){
            $formMapper
                ->with("Content Type", array('class'=>'col-md-6'))
                    ->add('templateFile','sonata_type_model',array(
                        'required' => true,
                        'query' => $templateFilesQuery,
                    ))
                ->end();
        }

        $formMapper
            ->with("Content info", array('class'=>'col-md-6'))
                ->add('structure', StructureType::class, array(
                    "label" => false,
                    "constraints" =>  new Valid(),
                    "is_symlink" => $this->isSymlinkContent($contentType)
                ))
                ->add('status', 'choice',array(
                    'choices' => array(
                        'Published' => 'published',
                        'Unpublished' => 'hidden',
                    ),
                ))
            ->end()
        ->end()
        ;
        if(!$this->isSymlinkContent($contentType)){
            $formMapper
                ->tab('Content data')
                    ->with("Fields", array('class'=>'col-md-12'))
                    ->add('custom_field_data', ContentFieldsType::class,array(
                        "label" => false,
                        "content" => $content
                    ))
                    ->end()
                ->end()
            ;
        }

        if($this->isPageMetadataEnabled()){
            $formMapper
                ->tab('Metadata', array(
                    'pageMeta' => true
                ))
                ->with("Metadata", array('class'=>'col-md-12'))
                ->end()
                ->end();
        }
    }

    public function isContextEnabled(){
        /** @var Content $content */
        $content = $this->getSubject();
        $structureContextEntities = $this->contentService->getStructureContextEntities();
        return $this->confService->isContextEnabled() && $content->getContentType()->isContextEnabled() && isset($structureContextEntities[$content->getContentType()->getContext()]);
    }

    public function isPageMetadataEnabled(){
        /** @var Content $content */
        $content = $this->getSubject();
        return $content->getContentType()->isPage();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', 'edcoms_doctrine_orm_istring')
            ->add('contentType')
            ->add('structure.parent')
        ;
    }


    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('contentType.name', 'text', array("label"=> 'Type'))
            ->add('status')
            ->add('addedBy')
            ->add('updatedOn')
            ->add('structure.parent')
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add("create_content_type",'create_content_type/{content_type}');
    }

    public function getContentTypes(){
        $contentTypes = $this->getModelManager()->findBy(ContentType::class);
        return $contentTypes;
    }

    protected function configureShowFields(ShowMapper $show)
    {
        // TODO Further work is needed to format the CustomFieldData according to their type. At the moment the value property is shown.
        $show
            ->add('templateFile')
            ->add('title')
            ->add('custom_field_data', null, array(
                "template" => "EdcomsCMSContentBundle:Admin:Content/show_custom_fields_data.html.twig",
                "associated_property" => 'value'
            ))
        ;
    }

    private function isSymlinkContent(ContentType $contentType){
        return $contentType->getName()=="System Symlink" ? true : false;
    }

}