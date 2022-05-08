TBD

#### TODOs:

##### Register only the required Bundle entities
At the moment the bundle auto-loads the [Context entities](EntityContext) and includes the [rest entities](Entity) only if the EdcomsCMSResourcesBundles has been added to the app configuration mappings.
If the application doesn't need to overwrite any Entity such as Resource, ResourceSubject etc, there isn't any issue.
However if the app overwrites one or more entities, one or more tables that are not needed will be auto-generated.

If for example the app needs to overwrite the ResourceSubject Entity, we need to add the following configuration
```
edcoms_cms_resources: ~
  resource_subject:
    entity_class: AppBundle\Entity\ResourceSubject
    form:    AppBundle\Form\Type\ResourceType
```
As a result, Doctrine will create a new table according to the  AppBundle\Entity\ResourceSubject annotations, but it will create an extra ResourceSubject table (edcoms_resource_subject) as this has been registered automatically by doctrine configuration.
```
 orm:
        default_entity_manager: default
        auto_generate_proxy_classes: "%kernel.debug%"
        entity_managers:
            default:
                connection: default
                mappings:
                    EdcomsCMSAuthBundle: ~
                    EdcomsCMSBadgeBundle: ~
                    EdcomsCMSResourcesBundle: ~
                    EdcomsCMSContentBundle: ~
                    EdcomsCMSUserBundle: ~
                    FOSUserBundle: ~
                    AppBundle: ~
```


### Advanced
- [Configuration Reference][1]


[1]:  Resources/doc/configuration_reference.md