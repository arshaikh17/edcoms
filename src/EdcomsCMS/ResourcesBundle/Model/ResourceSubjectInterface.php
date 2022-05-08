<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Model;


interface ResourceSubjectInterface
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
     * @return string
     */
    public function __toString();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @param bool $active
     */
    public function setActive($active);
}