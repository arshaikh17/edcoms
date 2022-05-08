````
edcoms_cms_content:
    cdn:
        enabled: false                     # Defaut:false
        cdn_host: 'https://my-example-cdn.com'       # Defaut: ''
    email: email@domain.com       # Defaut: %mailer_user%
    structure:
        context_enabled: true|false   # Defaut:false
        additional_context_classes:
            game_resource:
                context: AppBundle\Entity\Resource\GameResource
                label: 'Game resource'
                form: AppBundle\Form\Type\Resource\GameResourceType
                name: 'game_resource'
                context_class: EdcomsCMS\ResourcesBundle\EntityContext\ResourceContext
       
````
