<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ContentType
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\ContentTypeRepository")
 */
class ContentType
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
     * @ORM\Column(name="name", type="string", length=60)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="thumbnail", type="string", length=150, nullable=TRUE)
     */
    private $thumbnail;

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_children", type="boolean")
     */
    private $showChildren;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="TemplateFiles", fetch="EAGER" ,cascade={"all"}, orphanRemoval=true ,inversedBy="contentTypes")
     * @ORM\JoinTable(name="content_templates",
     *                joinColumns={@ORM\JoinColumn(name="contentID", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="templateID", referencedColumnName="id")})
     */
    private $template_files;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_child", type="boolean")
     */
    private $isChild;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="CustomFields", mappedBy="content_type", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"order"="ASC"})
     * @Assert\Valid()
     */
    private $custom_fields;

    /**
     * @var string
     *
     * @ORM\Column(name="context", type="string", nullable=true)
     */
    private $context;

    /**
     * @var boolean
     *
     * @ORM\Column(name="context_enabled", type="boolean", nullable=true)
     */
    private $contextEnabled;

    /**
     * @var boolean
     *
     * @ORM\Column(name="force_context_dropdown", type="boolean", nullable=true)
     */
    private $forceContextDropdown;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_page", type="boolean", nullable=true)
     */
    private $isPage;

    public function __construct() {
        $this->custom_fields = new ArrayCollection();
        $this->template_files = new ArrayCollection();
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
     * @return ContentType
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
     * Set description
     *
     * @param string $description
     * @return ContentType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set thumbnail
     *
     * @param string $thumbnail
     * @return ContentType
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * Get thumbnail
     *
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * Set showChildren
     *
     * @param boolean $showChildren
     * @return ContentType
     */
    public function setShowChildren($showChildren)
    {
        $this->showChildren = $showChildren;

        return $this;
    }

    /**
     * Get showChildren
     *
     * @return boolean
     */
    public function getShowChildren()
    {
        return $this->showChildren;
    }

    /**
     * Add template_file
     */
    public function addTemplateFile($templateFile)
    {
        $this->template_files[] = $templateFile;
        return $this;
    }
    
    /**
     * Remove a template_file
     *
     * @param TemplateFiles $templateFiles
     * @return ContentType
     */
    public function removeTemplateFile($templateFile)
    {
        $this->template_files->removeElement($templateFile);

        return $this;
    }

    /**
     * Set template_file
     *
     * @param string $templateFiles
     * @return ContentType
     */
    public function setTemplateFiles($templateFiles)
    {
        $this->template_files = $templateFiles;
        return $this;
    }

    /**
     * Get templateFile
     *
     * @return string
     */
    public function getTemplateFiles()
    {
        return $this->template_files;
    }

    /**
     * Set isChild
     *
     * @param boolean $isChild
     * @return ContentType
     */
    public function setIsChild($isChild)
    {
        $this->isChild = $isChild;

        return $this;
    }

    /**
     * Get isChild
     *
     * @return boolean
     */
    public function getIsChild()
    {
        return $this->isChild;
    }

    public function getCustomFields()
    {
        return $this->custom_fields;
    }

    public function addCustomField(CustomFields $custom_field, $recurs=true)
    {
        if ($custom_field->getId() === -1) {
            $custom_field->setId(null);
        }
        $this->custom_fields[] = $custom_field;
        if ($recurs) {
            $custom_field->setContentType($this, false);
        }
    }

    public function removeCustomField(CustomFields $customField)
    {
        $this->custom_fields->removeElement($customField);
        $customField->setContentType(null);

        return $this;
    }

    public function setCustomFields($custom_fields)
    {
        foreach ($custom_fields as $custom_field) {
            $this->addCustomField($custom_field);
        }
        return $this;
    }

    public function toJSON($vars=[]) {
        unset($this->json);
        $this->json = get_object_vars($this);
        if (empty($vars) || in_array('custom_fields', $vars)) {
            $tempcustom_fields = [];
            foreach ($this->custom_fields as $key=>$custom_field) {
                $tempcustom_fields[$key] = (!is_array($custom_field)) ? $custom_field->toJSON() : $custom_field;
            }
            $this->json['custom_fields'] = $tempcustom_fields;
        }
        if (empty($vars) || in_array('template_files', $vars)) {
            // template files \\
            $temptemplate_files = [];
            foreach ($this->template_files as $key=>$template_file) {
                $temptemplate_files[$key] = (!is_array($template_file)) ? $template_file->toJSON() : $template_file;
            }
            $this->json['template_files'] = $temptemplate_files;
        }

        if (empty($vars)) {
            // remove anything with an _ \\
            $this->json = array_filter($this->json, array(&$this, 'json_filter'), ARRAY_FILTER_USE_BOTH);
            return $this->json;
        }
        $obj = [];
        foreach ($vars as $prop) {
            if (!isset($this->json[$prop])) {
                $this->json[$prop] = $this->{$prop};
            }
            $obj[$prop] = $this->json[$prop];
        }
        return $obj;
    }
    public function json_filter($val, $key) {
        if (!strstr($key, '__')) {
            return true;
        }
        return false;
    }

    public function __toString()
    {
        return $this->getName() ? $this->getName() : '';
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return bool
     */
    public function isContextEnabled()
    {
        return $this->contextEnabled;
    }

    /**
     * @param bool $contextEnabled
     */
    public function setContextEnabled($contextEnabled)
    {
        $this->contextEnabled = $contextEnabled;
    }

    /**
     * @return bool
     */
    public function getForceContextDropdown()
    {
        return $this->forceContextDropdown;
    }

    /**
     * @param bool $forceContextDropdown
     */
    public function setForceContextDropdown($forceContextDropdown)
    {
        $this->forceContextDropdown = $forceContextDropdown;
    }

    /**
     * @return bool
     */
    public function isPage()
    {
        return $this->isPage;
    }

    /**
     * @param bool $isPage
     */
    public function setIsPage($isPage)
    {
        $this->isPage = $isPage;
    }





}
