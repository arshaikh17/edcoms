<?php

namespace EdcomsCMS\SettingsBundle\Twig;

use Symfony\Component\DependencyInjection\Container;

use EdcomsCMS\SettingsBundle\Service\SettingsService;
use Dmishh\SettingsBundle\Entity\SettingsOwnerInterface;

class EdcomsSettingsExtension extends \Twig_Extension
{

    /**
     * @var \EdcomsCMS\SettingsBundle\Service\SettingsService
     */
    private $settingsManager;

    private $settingCategories;

    public function __construct(SettingsService $settingsManager, array $settingCategories)
    {
        $this->settingsManager = $settingsManager;
        $this->settingCategories = $settingCategories;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('edcoms_get_setting', array($this, 'getSetting')),
            new \Twig_SimpleFunction('edcoms_get_all_settings', array($this, 'getAllSettings')),
            new \Twig_SimpleFunction('edcoms_get_all_settings_categories', array($this, 'getSetttingsCategories')),
        );
    }

    /**
     * Proxy to SettingsManager::get.
     *
     * @param string                      $name
     * @param SettingsOwnerInterface|null $owner
     *
     * @return mixed
     */
    public function getSetting($name, SettingsOwnerInterface $owner = null, $default = null)
    {
        return $this->settingsManager->get($name, $owner, $default);
    }

    /**
     * Proxy to SettingsManager::all.
     *
     * @param SettingsOwnerInterface|null $owner
     *
     * @return array
     */
    public function getAllSettings(SettingsOwnerInterface $owner = null)
    {
        return $this->settingsManager->all($owner);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'edcoms_settings_extension';
    }

    public function getSetttingsCategories(){
        return array_unique($this->settingCategories);
    }
}