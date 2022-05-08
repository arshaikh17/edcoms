##Settings Bundle 
- *Description*: The bundle will display a **General Settings** section for the CMS ***Settings*** menu. 
There a predefined `Maintenance` setting has already been created for the maintenance mode. However,  
we can update those settings by adding extra ones as needed. For more information about the customization, 
please refer the *configuration_reference.yml* file.

##Installation 
#### Add extensions to the AppKernel

```
//Settings Bundle
new Dmishh\SettingsBundle\DmishhSettingsBundle(),
new EdcomsCMS\SettingsBundle\EdcomsCMSSettingsBundle()
``` 

#### Execute migrations
```
bin/console doctrine:migration:diff
bin/console doctrine:migration:migrate
```

##Configuration
#### Add the following to the imports of the main *config.yml* file for the bundle configuration with the maintenance predefined setting.  
- ORM Mappings
```
 orm:
        ......
        ......
        entity_managers:
            default:
                connection: default
                mappings:
                    .....
                    .....
                    DmishhSettingsBundle: ~
            edcoms_cms:
                connection: default
                mappings:
                    .....
                    .....
                    DmishhSettingsBundle: ~
```
- Maintenance setting
```
edcoms_cms_settings:
  settings:
      maintenance:

```

## Usage 
#### Sample usage
- [Configuration Reference][1]

[1]:  Resources/doc/configuration_reference.md

#### Sample translations
- [Translations][1]

[1]:  Resources/doc/translations.md