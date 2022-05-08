<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use EdcomsCMS\ContentBundle\Entity\Media;
use EdcomsCMS\ResourcesBundle\Entity\AgeGroup;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class BaseResource
 * @package EdcomsCMS\ResourcesBundle\Model
 *
 * @UniqueEntity("slug")
 */
class BaseResource implements ResourceInterface
{

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string")
     * @Assert\NotBlank()
     *
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="subtitle", type="string")
     *
     */
    protected $subtitle;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", unique=true)
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="text")
     */
    protected $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="quick_view_content", type="text", nullable=true)
     */
    protected $quickViewContent;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    protected $content;

    /**
     * @var string
     *
     * @ORM\Column(name="curriculum_content", type="text", nullable=true)
     */
    protected $curriculumContent;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdOn;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedOn;

    /**
     * @var ArrayCollection|ResourceSubjectInterface
     * @ORM\ManyToMany(targetEntity="EdcomsCMS\ResourcesBundle\Model\ResourceSubjectInterface")
     * @ORM\JoinTable(name="resource_subjects",
     *      joinColumns={@ORM\JoinColumn(name="resource_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="resource_subject_id", referencedColumnName="id")}
     *      )
     */
    protected $subjects;

    /**
     * @var ArrayCollection|ResourceTopicInterface
     * @ORM\ManyToMany(targetEntity="EdcomsCMS\ResourcesBundle\Model\ResourceTopicInterface")
     * @ORM\JoinTable(name="resource_topics",
     *      joinColumns={@ORM\JoinColumn(name="resource_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="resource_topic_id", referencedColumnName="id")}
     *      )
     */
    protected $topics;

    /**
     * @var ArrayCollection|ResourceActivityInterface
     * @ORM\ManyToMany(targetEntity="EdcomsCMS\ResourcesBundle\Model\ResourceActivityInterface")
     * @ORM\JoinTable(name="resource_activities",
     *      joinColumns={@ORM\JoinColumn(name="resource_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="resource_activity_id", referencedColumnName="id")}
     *      )
     */
    protected $activities;

    /**
     * @var ArrayCollection|AgeGroupInterface
     * @ORM\ManyToMany(targetEntity="EdcomsCMS\ResourcesBundle\Model\AgeGroupInterface")
     * @ORM\JoinTable(name="resource_age_groups",
     *      joinColumns={@ORM\JoinColumn(name="resource_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="age_group_id", referencedColumnName="id")}
     *      )
     */
    protected $ageGroups;

    /**
     * @var ResourceTypeInterface
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\ResourcesBundle\Model\ResourceTypeInterface")
     */
    protected $type;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\Media")
     */
    protected $thumbImage;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\Media")
     */
    protected $headerImage;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\Media")
     */
    protected $file;


    public function __construct()
    {
        $this->subjects = new ArrayCollection();
    }

    public function getCompactAgeGroups(){
        $payload = [];
        /** @var AgeGroupInterface $ageGroup */
        foreach ($this->getAgeGroups() as $ageGroup){
            $payload[] = $ageGroup->getTitle();
        }
        return $payload;
    }

    public function getCompactTopics(){
        $payload = [];
        /** @var ResourceTopicInterface $topic */
        foreach ($this->getTopics() as $topic){
            $payload[] = $topic->getTitle();
        }
        return $payload;
    }

    public function getCompactSubjects(){
        $payload = [];
        /** @var ResourceSubjectInterface $subject */
        foreach ($this->getSubjects() as $subject){
            $payload[] = $subject->getTitle();
        }
        return $payload;
    }

    public function getCompactActivities(){
        $payload = [];
        /** @var ResourceActivityInterface $activity */
        foreach ($this->getActivities() as $activity){
            $payload[] = $activity->getTitle();
        }
        return $payload;
    }

    public function getCompactType(){
        return $this->getType() ? $this->getType()->getTitle() : '';
    }
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param string $subtitle
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param \DateTime $createdOn
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
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

    /**
     * @return ArrayCollection|ResourceSubjectInterface
     */
    public function getSubjects()
    {
        return $this->subjects;
    }

    /**
     * @param ArrayCollection|ResourceSubjectInterface $subjects
     */
    public function setSubjects($subjects)
    {
        $this->subjects = $subjects;
    }

    public function __toString()
    {
       return $this->getTitle() ?: '';
    }

    /**
     * @return ResourceTypeInterface
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param ResourceTypeInterface $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return ArrayCollection|ResourceTopicInterface
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * @param ArrayCollection|ResourceTopicInterface $topics
     */
    public function setTopics($topics)
    {
        $this->topics = $topics;
    }

    /**
     * @return ArrayCollection|AgeGroupInterface
     */
    public function getAgeGroups()
    {
        return $this->ageGroups;
    }

    /**
     * @param ArrayCollection|AgeGroupInterface $ageGroups
     */
    public function setAgeGroups($ageGroups)
    {
        $this->ageGroups = $ageGroups;
    }

    /**
     * @return Media
     */
    public function getThumbImage()
    {
        return $this->thumbImage;
    }

    /**
     * @param Media $thumbImage
     */
    public function setThumbImage($thumbImage)
    {
        $this->thumbImage = $thumbImage;
    }

    /**
     * @return Media
     */
    public function getHeaderImage()
    {
        return $this->headerImage;
    }

    /**
     * @param Media $headerImage
     */
    public function setHeaderImage($headerImage)
    {
        $this->headerImage = $headerImage;
    }

    /**
     * @return ArrayCollection|ResourceActivityInterface
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * @param ArrayCollection|ResourceActivityInterface $activities
     */
    public function setActivities($activities)
    {
        $this->activities = $activities;
    }

    /**
     * @return string
     */
    public function getQuickViewContent()
    {
        return $this->quickViewContent;
    }

    /**
     * @param string $quickViewContent
     */
    public function setQuickViewContent(string $quickViewContent)
    {
        $this->quickViewContent = $quickViewContent;
    }

    /**
     * @return Media
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param Media $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getCurriculumContent()
    {
        return $this->curriculumContent;
    }

    /**
     * @param string $curriculumContent
     */
    public function setCurriculumContent($curriculumContent)
    {
        $this->curriculumContent = $curriculumContent;
    }



}