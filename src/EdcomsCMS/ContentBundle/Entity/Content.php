<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use EdcomsCMS\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Content
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\ContentRepository")
 */
class Content
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
     * @ORM\Column(name="title", type="string", length=150)
     */
    private $title;

    /**
     * @var ContentType
     *
     * @ORM\ManyToOne(targetEntity="ContentType", cascade={"persist"})
     * @ORM\JoinColumn(name="content_type", referencedColumnName="id")
     */
    private $contentType;
    
    /**
     * @var TemplateFiles
     * 
     * @ORM\ManyToOne(targetEntity="TemplateFiles")
     * @ORM\JoinColumn(name="template_file", referencedColumnName="id")
     */
    private $templateFile;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=10)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="added_on", type="datetime")
     */
    private $addedOn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="approved_on", type="datetime", nullable=true)
     */
    private $approvedOn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_on", type="datetime", nullable=true)
     */
    private $updatedOn;
    
    /**
     * @var cms_users
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUsers")
     * @ORM\JoinColumn(name="added_by", referencedColumnName="id")
     */
    private $addedUser;
    
    /**
     * @var cms_users
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\AuthBundle\Entity\cmsUsers")
     * @ORM\JoinColumn(name="approved_by", referencedColumnName="id")
     */
    private $approvedUser;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="added_by_user", referencedColumnName="id")
     */
    private $addedBy;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="approved_by_user", referencedColumnName="id")
     */
    private $approvedBy;
    
    /**
     * @var Structure
     * @ORM\ManyToOne(targetEntity="Structure", inversedBy="content", cascade={"all"})
     * @ORM\JoinColumn(name="structureID", referencedColumnName="id")
     */
    private $structure;
    
    /**
     * @var ArrayCollection
     * @Assert\Valid
     * @ORM\OneToMany(targetEntity="CustomFieldData", mappedBy="content", cascade={"all", "detach"}, orphanRemoval=true)
     */
    private $custom_field_data;
    
    /**
     *
     * @var array
     */
    private $custom_field_data_arr;

    public function __construct() {
        $this->custom_field_data = new ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Content
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set contentType
     *
     * @param string $contentType
     * @return Content
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get contentType
     *
     * @return ContentType
     */
    public function getContentType()
    {
        return $this->contentType;
    }
    
    /**
     * Set templateFile
     *
     * @param string $templateFile
     * @return Content
     */
    public function setTemplateFile($templateFile)
    {
        $this->templateFile = $templateFile;

        return $this;
    }

    /**
     * Get templateFile
     *
     * @return string 
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Content
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set addedOn
     *
     * @param \DateTime $addedOn
     * @return Content
     */
    public function setAddedOn($addedOn)
    {
        $this->addedOn = $addedOn;

        return $this;
    }

    /**
     * Get addedOn
     *
     * @return \DateTime 
     */
    public function getAddedOn()
    {
        return $this->addedOn;
    }

    /**
     * Set approvedOn
     *
     * @param \DateTime $approvedOn
     * @return Content
     */
    public function setApprovedOn($approvedOn)
    {
        $this->approvedOn = $approvedOn;

        return $this;
    }

    /**
     * Get approvedOn
     *
     * @return \DateTime 
     */
    public function getApprovedOn()
    {
        return $this->approvedOn;
    }
    
    /**
     * Get addedUser
     *
     * @return integer 
     */
    public function getAddedUser()
    {
        return $this->addedUser;
    }
    
    /**
     * Set addedUser
     * @param cmsUsers $addedUser
     * @return Content 
     */
    public function setAddedUser($addedUser)
    {
        $this->addedUser = $addedUser;
        return $this;
    }
    
    /**
     * Get approvedUser
     *
     * @return integer 
     */
    public function getApprovedUser()
    {
        return $this->approvedUser;
    }
    
    /**
     * Set approvedUser
     * @param cmsUsers $approvedUser
     * @return Content
     */
    public function setApprovedUser($approvedUser)
    {
        $this->approvedUser = $approvedUser;
        return $this;
    }

    /**
     * 
     * @return ArrayCollection
     */
    public function getCustomFieldData()
    {
        return $this->custom_field_data;
    }
    
    public function addCustomFieldData(CustomFieldData $custom_field_data, $recurs=true)
    {
        $this->custom_field_data->add($custom_field_data);
        if ($recurs) {
            $custom_field_data->setContent($this, false);
        }
        return $this;
    }


    /**
     * @param CustomFieldData $custom_field_data
     * @return Content
     */
    public function removeCustomFieldData(CustomFieldData $custom_field_data){
        if($this->custom_field_data->contains($custom_field_data)){
            $this->custom_field_data->removeElement($custom_field_data);
            $custom_field_data->setContent(null);
        }
        return $this;
    }
    
    /**
     * 
     * @param ArrayCollection $custom_field_data
     * @return Content
     */
    public function setCustomFieldData($custom_field_data)
    {
        foreach ($custom_field_data as $custom_data) {
            $this->addCustomFieldData($custom_data, true);
        }
        return $this;
    }
    
    /**
     * 
     * @return Content
     */
    public function resetCustomFieldData()
    {
        $this->custom_field_data = new ArrayCollection();
        return $this;
    }
    
    /**
     * 
     * @return ArrayCollection
     */
    public function getCustomFieldDataArr()
    {
        return $this->custom_field_data_arr;
    }
    
    /**
     * 
     * @param Array $custom_field_data_arr
     * @return Content
     */
    public function setCustomFieldDataArr($custom_field_data_arr)
    {
        $this->custom_field_data_arr = $custom_field_data_arr;
        return $this;
    }

    public function setStructure(Structure $structure)
    {
        if ($structure->getId() === -1) {
            $structure->setId(null);
        }
        $this->structure = $structure;
    }
    
    /*public function setStructure($structures)
    {
        foreach ($structures as $structure) {
            $this->addStructure($structure);
        }
        return $this;
    }*/
    
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Determines if the Content is behind login.
     * It searches through all of the CustomFieldData,
     * comparing only the values with the CustomField name of 'permission'.
     * 'true' is returned 'f the field is matches and the value is set to '1'.
     *
     * @return  boolean  'true' if Content is behind login.
     */
    public function behindLogin()
    {
        $behindLogin = false;

        foreach ($this->getCustomFieldData() as $customFieldData) {
            if ($customFieldData->getCustomFields()->getName() === 'permission' && $customFieldData->getValue() === '1') {
                $behindLogin = true;
                break;
            }
        }

        return $behindLogin;
    }

    public function toJSON($vars=[]) {
        unset($this->json);
        if (empty($vars)) {
            $this->json = get_object_vars($this);
            $this->json['contentType'] = (!is_int($this->json['contentType'])) ? (!is_null($this->contentType)) ? $this->contentType->toJSON() : null  : $this->contentType;
            $this->json['templateFile'] = (!is_array($this->json['templateFile'])) ? (!is_null($this->templateFile)) ? $this->templateFile->toJSON() : null  : $this->templateFile;
            $this->json['addedUser'] = (!is_array($this->json['addedUser'])) ? (!is_null($this->addedUser)) ? $this->addedUser->toJSON() : null : $this->addedUser;
            $this->json['approvedUser'] = (!is_array($this->json['approvedUser'])) ? (!is_null($this->approvedUser)) ? $this->approvedUser->toJSON() : null : $this->addedUser;
            $this->json['structure'] = (!is_array($this->json['structure'])) ? (!is_null($this->structure)) ? $this->structure->toJSON() : null : $this->structure;
            $this->json['addedOn'] = $this->getAddedOn()->format('d/m/Y');

            if (!is_array($this->custom_field_data)) {
                $tempcustom_field_data = [];
                foreach ($this->custom_field_data as $key=>$custom_data) {
                    $tempcustom_field_data[$key] = $custom_data->toJSON();
                }
                $this->json['custom_field_data'] = $tempcustom_field_data;
            }
            // remove anything with an _ \\
            $this->json = array_filter($this->json, array(&$this, 'json_filter'), ARRAY_FILTER_USE_BOTH);
            return $this->json;
        }
        if (in_array('structureID', $vars)) {
            $this->structureID = $this->structure->getId();
        }
        $obj = [];
        foreach ($vars as $prop) {
            $obj[$prop] = (is_object($this->{$prop}) && method_exists($this->{$prop}, 'toJSON')) ? $this->{$prop}->toJSON() : $this->{$prop};
            if ($prop === 'addedOn') {
                $obj[$prop] = $this->getAddedOn()->format('d/m/Y');
            }
            if ($prop === 'custom_field_data') {
                if (!is_array($this->custom_field_data)) {
                    $tempcustom_field_data = [];
                    foreach ($this->custom_field_data as $key=>$custom_data) {
                        $tempcustom_field_data[$key] = $custom_data->toJSON();
                    }
                    $obj[$prop] = $tempcustom_field_data;
                }
            }
        }
        return $obj;
    }
    // this method allows you to clone a Content item and do a deep clone of custom field data - which is what we will always want for versioning \\
    public function __clone()
    {
        if ($this->id) {

            $customFieldData = $this->getCustomFieldData();
            $this->custom_field_data = new ArrayCollection();
            foreach ($customFieldData as $customFieldDataItem) {
                $cloneField = clone $customFieldDataItem;
                // set addedOn to now \\
                $cloneField->setAddedOn(new \DateTime());
                $this->addCustomFieldData($cloneField);
            }
            $customFieldData = null;
        }
    }
    public function json_filter($val, $key) {
        if (!strstr($key, '__')) {
            return true;
        }
        return false;
    }

    public function __toString()
    {
        return $this->getTitle() ? $this->getTitle() : '';
    }

    /**
     * @return User
     */
    public function getAddedBy()
    {
        return $this->addedBy;
    }

    /**
     * @param User $addedBy
     */
    public function setAddedBy($addedBy)
    {
        $this->addedBy = $addedBy;
    }

    /**
     * @return User
     */
    public function getApprovedBy()
    {
        return $this->approvedBy;
    }

    /**
     * @param User $approvedBy
     */
    public function setApprovedBy($approvedBy)
    {
        $this->approvedBy = $approvedBy;
    }

    /**
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param User $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     * @param \DateTime $updatedOn
     */
    public function setUpdatedOn($updatedOn)
    {
        $this->updatedOn = $updatedOn;
    }




}
