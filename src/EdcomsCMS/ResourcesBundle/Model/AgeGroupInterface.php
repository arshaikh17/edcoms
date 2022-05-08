<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Model;


interface AgeGroupInterface
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
     * @return integer
     */
    public function getMinAge();

    /**
     * @param integer $age
     */
    public function setMinAge($age);

    /**
     * @return integer
     */
    public function getMaxAge();

    /**
     * @param integer $age
     */
    public function setMaxAge($age);

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