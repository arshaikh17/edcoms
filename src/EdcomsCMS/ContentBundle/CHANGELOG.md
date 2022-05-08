### Review is needed
Below is a list of changes in the existing Edcoms CMS codebase that might have side effects. Those changes need to be reviewed by the BE devs team.

- **OrphanRemoval** configuration has been added to **TemplateFiles property** of [**ContentType**][1] entity
- New mapped entity named **ContentTypes** has been added to [**TemplateFiles**][2] entity
- **OrphanRemoval** configuration has been added to **custom_field_data property** of [**Content**][3] entity
- **Property value** of [**Content**][3] entity can be null
- User is not set during file upload as new User entity is not of cmsUsers type. TODO...
- User default Entity Manager at [**FileManagerController**][4] && [**MediaController**][5] (before UploadFile method)


### Breaking changes
#### Configuration loading      
Remove EdocmsCMSContent services import - The configuration files are now loaded from the corresponding bundle extensions

    # app/config/config.yml
    imports:
        - { resource: parameters.yml }
        - { resource: security.yml }
        - { resource: services.yml }
        - { resource: '@EdcomsCMSContentBundle/Resources/config/services.yml' }    <-- Must be removed
        
        
        
        
            
[1]:  Entity/ContentType.php
[2]:  Entity/TemplateFiles.php
[3]:  Entity/Content.php
[4]:  Controller/FilemanagerController.php
[5]:  Controller/MediaController.php

