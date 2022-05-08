<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use EdcomsCMS\ContentBundle\Entity\Media;

interface VideoResourceInterface
{

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getSubtitle();

    /**
     * @param string $subtitle
     */
    public function setSubtitle($subtitle);

    /**
     * @return string
     */
    public function getSlug();

    /**
     * @param string $slug
     */
    public function setSlug($slug);

    /**
     * @return string
     */
    public function getSummary();

    /**
     * @param string $summary
     */
    public function setSummary($summary);

    /**
     * @return string
     */
    public function getContent();

    /**
     * @param string $content
     */
    public function setContent($content);

    /**
     * @return \DateTime
     */
    public function getCreatedOn();

    /**
     * @param \DateTime $createdOn
     */
    public function setCreatedOn($createdOn);

    /**
     * @return \DateTime
     */
    public function getUpdatedOn();

    /**
     * @param \DateTime $updatedOn
     */
    public function setUpdatedOn($updatedOn);

    /**
     * @return ArrayCollection|ResourceSubjectInterface
     */
    public function getSubjects();

    /**
     * @param ArrayCollection|ResourceSubjectInterface $subjects
     */
    public function setSubjects($subjects);

    /**
     * @return string
     */
    public function __toString();

    /**
     * @return ResourceTypeInterface
     */
    public function getType();

    /**
     * @param ResourceTypeInterface $type
     */
    public function setType($type);

    /**
     * @return ArrayCollection|ResourceTopicInterface
     */
    public function getTopics();

    /**
     * @param ArrayCollection|ResourceTopicInterface $topics
     */
    public function setTopics($topics);

    /**
     * @return ArrayCollection|AgeGroupInterface
     */
    public function getAgeGroups();

    /**
     * @param ArrayCollection|AgeGroupInterface $ageGroups
     */
    public function setAgeGroups($ageGroups);

    /**
     * @return Media
     */
    public function getVideo();

    /**
     * @param Media $video
     */
    public function setVideo($video);

}