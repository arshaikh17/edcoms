##Settings Bundle 
- *Description*: The Bundle will make available the administration of the maintenance section of the site. 
It has dependency on the EdcomsCMSSettingsBundle as the current status is retrieved from there. 
The only configuration supported for the moment is **template** overriding. 
If this settings is not present the bundle will use the default template present in the ``Resources/views/Maintenance/default.html.twig``.

 
##Installation 
#### Add extensions to the AppKernel
****Note****: *Add the Maintenance bundle before the DmishhSettingsBundle and EdcomsCMSSettingsBundle*
- [Demo]
```
//Maintenance Bundle
new EdcomsCMS\MaintenanceBundle\EdcomsCMSMaintenanceBundle(),

//Settings Bundle
new Dmishh\SettingsBundle\DmishhSettingsBundle(),
new EdcomsCMS\SettingsBundle\EdcomsCMSSettingsBundle(),
``` 

##Configuration
#### Add the following to the imports of the main *config.yml* file for the bundle configuration with the maintenance predefined setting.  
```
edcoms_cms_maintenance:
    template: EdcomsCMSSettingsBundle:Maintenance:deafult.html.twig
```

## Usage 
#### Sample usage
- [Configuration Reference][1]

[1]:  Resources/doc/configuration_reference.md