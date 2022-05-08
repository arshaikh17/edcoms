<?php
/**
 * Created by Redi Linxa
 * Date: 25.11.19
 * Time: 11:13
 */

namespace EdcomsCMS\SettingsBundle\Service;

use Dmishh\SettingsBundle\Entity\SettingsOwnerInterface;
use EdcomsCMS\SettingsBundle\Form\Type\SettingsType;
use Dmishh\SettingsBundle\Manager\SettingsManager as DmisshManager;
use Symfony\Component\Form\FormFactory;

class SettingsService
{
    /**
     * This service is build to wrap the functionality of the Dmishh Settings bundle.
     * It will help avoiding any direct dependencies on specifics project.
     * @var DmisshManager
     */
    private $dminshhManager;

    /**
     * @var settingsConfigurations
     */
    private $settingsConfigurations;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * EdcomsSettingsService constructor.
     * @param DmisshManager $dminshhManager
     * @param FormFactory $formFactory
     */
    public function __construct(DmisshManager $dminshhManager, $settingsConfigurations, FormFactory $formFactory)
    {
        $this->dminshhManager = $dminshhManager;
        $this->settingsConfigurations = $settingsConfigurations;
        $this->formFactory = $formFactory;
    }

    public function get($name, SettingsOwnerInterface $owner = null, $default = null)
    {
        return $this->dminshhManager->get($name, $owner, $default);
    }

    public function all(SettingsOwnerInterface $owner = null)
    {
        return $this->dminshhManager->all($owner);
    }

    public function set($name, $value, SettingsOwnerInterface $owner = null)
    {
        return $this->dminshhManager->get($name, $value, $owner);
    }

    public function setMany(array $settings, SettingsOwnerInterface $owner = null)
    {
        return $this->dminshhManager->setMany($settings, $owner);
    }

    public function clear($name, SettingsOwnerInterface $owner = null)
    {
        return $this->set($name, null, $owner);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm(){
        return $this->formFactory->create(SettingsType::class, $this->all());
    }

    /**
     * Checks wether the setting exists or not.
     * Used in the MaintenanceListener to avoid throwing an exception when the setting does not exist.
     * @param $name
     * @param SettingsOwnerInterface|null $owner
     * @return bool
     */
    public function exists($name, SettingsOwnerInterface $owner = null){
        // Name validation
        if (!is_string($name) || !array_key_exists($name, $this->settingsConfigurations)) {
            return false;
        }
        return true;
    }
}