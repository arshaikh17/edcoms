## Breaking changes

#### Configuration loading
       
Remove EdcomsBadgeBundle services import - The configuration files are now loaded from the corresponding bundle extensions

    # app/config/config.yml
    imports:
        - { resource: parameters.yml }
        - { resource: security.yml }
        - { resource: services.yml }
        - { resource: '@EdcomsCMSBadgeBundle/Resources/config/services.yml' }    <-- Must be removed

