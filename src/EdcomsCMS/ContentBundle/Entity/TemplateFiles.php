<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ContentTemplates
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\TemplateFilesRepository")
 */
class TemplateFiles
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
     * @ORM\Column(name="template_file", type="string", length=180)
     */
    private $templateFile;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="EdcomsCMS\ContentBundle\Entity\ContentType", mappedBy="template_files", fetch="EAGER")
     */
    private $contentTypes;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contentTypes = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Set id
     *
     * @return integer
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set templateFile
     *
     * @param string $templateFile
     * @return TemplateFiles
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
    public function toJSON($vars=[]) {
        unset($this->json);
        if (empty($vars)) {
            $this->json = get_object_vars($this);
            // remove anything with an _ \\
            $this->json = array_filter($this->json, array(&$this, 'json_filter'), ARRAY_FILTER_USE_BOTH);
            return $this->json;
        }
        $obj = [];
        foreach ($vars as $prop) {
            $obj[$prop] = $this->{$prop};
        }
        return $obj;
    }
    public function json_filter($val, $key) {
        if (!strstr($key, '__')) {
            return true;
        }
        return false;
    }

    /**
     * Add contentType
     *
     * @param \EdcomsCMS\ContentBundle\Entity\ContentType $contentType
     *
     * @return TemplateFiles
     */
    public function addContentType(\EdcomsCMS\ContentBundle\Entity\ContentType $contentType)
    {
        $this->contentTypes[] = $contentType;

        return $this;
    }

    /**
     * Remove contentType
     *
     * @param \EdcomsCMS\ContentBundle\Entity\ContentType $contentType
     */
    public function removeContentType(\EdcomsCMS\ContentBundle\Entity\ContentType $contentType)
    {
        $this->contentTypes->removeElement($contentType);
    }

    /**
     * Get contentTypes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContentTypes()
    {
        return $this->contentTypes;
    }

    public function __toString()
    {
        return $this->getTemplateFile();
    }
}
