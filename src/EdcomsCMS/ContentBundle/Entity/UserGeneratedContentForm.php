<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Content
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\UserGeneratedContentFormRepository")
 */
class UserGeneratedContentForm
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=150)
     */
    private $name;
    
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Content")
     * @ORM\JoinTable(name="ContentForms",
     *                joinColumns={@ORM\JoinColumn(name="formID", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="contentID", referencedColumnName="id")})
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=20)
     */
    private $type;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="templateFile", type="string", length=100)
     */
    private $templateFile;

    /**
     * @var boolean
     *
     * @ORM\Column(name="entriesVisible", type="boolean")
     */
    private $entriesVisible;

    /**
     * @var ContentType
     *
     * @ORM\ManyToOne(targetEntity="ContentType")
     * @ORM\JoinColumn(name="contentTypeID", referencedColumnName="id")
     */
    private $entryContentType;
    
    /**
     * @var Content
     * 
     * @ORM\OneToOne(targetEntity="Structure")
     * @ORM\JoinColumn(name="entriesStructureID", referencedColumnName="id", nullable=true)
     */
    private $entriesParent;

    /**
     * @var string
     *
     * @ORM\Column(name="notification", type="string", length=255)
     */
    private $notification;
    
    /**
     *
     * @var string
     * @ORM\Column(name="form_title", type="string", length=120, nullable=true)
     */
    private $form_title;
    
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUserGroups")
     * @ORM\JoinTable(name="UserGeneratedContentAccess",
     *                joinColumns={@ORM\JoinColumn(name="formID", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="groupID", referencedColumnName="id")})
     */
    private $groups;
    
    /**
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="FormBuilderElements", mappedBy="userGeneratedContentForm")
     */
    private $formBuilderElements;

    public function __construct() {
        $this->content = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->formBuilderElements = new ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return UserGeneratedContentForm
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Content
     * @return ArrayCollection
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Set Content
     * @param ArrayCollection $content
     * @return UserGeneratedContentForm
     */
    public function setContent($contents)
    {
        foreach ($contents as $content) {
            $this->addContent($content);
        }
        return $this;
    }
    /**
     * Add Content
     * @param Content $content
     * @return UserGeneratedContentForm
     */
    public function addContent($content)
    {
        $this->content[] = $content;
        return $this;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    
    public function getTemplateFile()
    {
        return $this->templateFile;
    }
    
    public function setTemplateFile($templateFile)
    {
        $this->templateFile = $templateFile;
        return $this;
    }
    
    public function getEntriesVisible()
    {
        return $this->entriesVisible;
    }
    
    public function setEntriesVisible($entriesVisible)
    {
        $this->entriesVisible = $entriesVisible;
        return $this;
    }
    
    public function getEntryContentType()
    {
        return $this->entryContentType;
    }
    
    public function setEntryContentType($entryContentType)
    {
        $this->entryContentType = $entryContentType;
        return $this;
    }
    
    public function getEntriesParent()
    {
        return $this->entriesParent;
    }
    
    public function setEntriesParent($entriesParent)
    {
        $this->entriesParent = $entriesParent;
        return $this;
    }
    
    public function getGroups()
    {
        return $this->groups;
    }
    
    public function setGroups($groups)
    {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }
        return $this;
    }
    
    public function addGroup($group)
    {
        $this->groups[] = $group;
        return $this;
    }
    
    public function getNotification()
    {
        return $this->notification;
    }
    
    public function setNotification($notification)
    {
        $this->notification = $notification;
        return $this;
    }
    
    public function getFormTitle()
    {
        return $this->form_title;
    }
    
    public function setFormTitle($form_title)
    {
        $this->form_title = $form_title;
        return $this;
    }
    
    /**
     * Get FormBuilderElements
     * @return ArrayCollection
     */
    public function getFormBuilderElements()
    {
        return $this->formBuilderElements;
    }
    
    /**
     * Set FormBuilderElements
     * @param ArrayCollection $formBuilderElements
     * @return FormBuilderElements
     */
    public function setFormBuilderElements($formBuilderElements)
    {
        foreach ($formBuilderElements as $formBuilderElement) {
            $this->addFormBuilderElement($formBuilderElement);
        }
        return $this;
    }
    /**
     * Add FormBuilderElement
     * @param FormBuilderElement $formBuilderElement
     * @return UserGeneratedContentForm
     */
    public function addFormBuilderElement(FormBuilderElements $formBuilderElement, $recurs=true)
    {
        $this->formBuilderElements[] = $formBuilderElement;
        if ($recurs) {
            $formBuilderElement->setUserGeneratedContentForm($this);
        }
        return $this;
    }
    
    public function toJSON($vars=[]) {
        unset($this->json);
        if (empty($vars)) {
            $this->json = get_object_vars($this);
            if (!is_array($this->content)) {
                $tempcontent = [];
                foreach ($this->content as $key=>$content) {
                    $tempcontent[$key] = $content->getId();
                }
                $this->json['content'] = $tempcontent;
            }
            
            $this->json['entryContentType'] = (!is_array($this->json['entryContentType'])) ? (!is_null($this->entryContentType)) ? $this->entryContentType->getId() : null  : $this->entryContentType;
            $this->json['entriesParent'] = (!is_array($this->json['entriesParent'])) ? (!is_null($this->entriesParent)) ? $this->entriesParent->getId() : null  : $this->entriesParent;
            
            if (!is_array($this->groups)) {
                $tempGroups = [];
                foreach ($this->groups as $key=>$group) {
                    $tempGroups[$key] = $group->getId();
                }
                $this->json['groups'] = $tempGroups;
            }
            // remove anything with an _ \\
            $this->json = array_filter($this->json, array(&$this, 'json_filter'), ARRAY_FILTER_USE_BOTH);
            return $this->json;
        }
        $obj = [];
        foreach ($vars as $prop) {
            $obj[$prop] = (is_object($this->{$prop}) && method_exists($this->{$prop}, 'toJSON')) ? $this->{$prop}->toJSON() : $this->{$prop};
        }
        return $obj;
    }
    public function json_filter($val, $key) {
        if (!strstr($key, '__')) {
            return true;
        }
        return false;
    }
}
