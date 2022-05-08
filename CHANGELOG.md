## 2.0 (2017-XX-XX)

### Features

 - [Performance] Introduce new method **getContentByStructure** on GetContent helper (**getContentByStructure** from Twig) that doesn't fetch the children content.
 - [Feature] Sitemap functionality.

## 1.4 (2017-XX-XX)

### Steps to Upgrade

- Include Sonata User Bundle to **composer** requirements as shown below. As this bundle is still in development we need to explicitly declare in the project that a development version is allowed. EdcomsCMS requires a particular commits of that repository to avoid untested updates.

```
    "require": {
            "php": ">=5.5.9",
            "doctrine/doctrine-bundle": "^1.6",
            "doctrine/doctrine-cache-bundle": "^1.2",
            "doctrine/orm": "^2.5",
            "incenteev/composer-parameter-handler": "^2.0",
            "sensio/distribution-bundle": "^5.0",
            "sensio/framework-extra-bundle": "^3.0.2",
            "symfony/monolog-bundle": "^3.0.2",
            "symfony/polyfill-apcu": "^1.0",
            "symfony/swiftmailer-bundle": "^2.3.10",
            "symfony/symfony": "3.2.*",
            "twig/twig": "^1.0||^2.0",
            "symfony/assetic-bundle": "~2.3",
            "edcoms/cms": "dev-sonata/initial-integration",
            "edcoms/SPIRIT": "dev-master",
            "sonata-project/user-bundle": "@dev"            <---------- 
        },
```

- Update your **AppKernel.php** to match the following bundles:

```
public function registerBundles()
    {
        $bundles = [
            .......
        //  Sonata dependencies
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
        
        //  Edcoms CMS dependencies
            new EdcomsCMS\AdminBundle\EdcomsCMSAdminBundle(),
            new EdcomsCMS\AuthBundle\EdcomsCMSAuthBundle(),
            new EdcomsCMS\BadgeBundle\EdcomsCMSBadgeBundle(),
            new EdcomsCMS\ContentBundle\EdcomsCMSContentBundle(),
            new EdcomsCMS\TemplatesBundle\EdcomsCMSTemplatesBundle(),
            new Edcoms\SPIRIT\SpiritBundle\EdcomsSPIRITSpiritBundle(),
            new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
            new Infinite\FormBundle\InfiniteFormBundle(),
            new EdcomsCMS\UserBundle\EdcomsCMSUserBundle(),
        
        //  User bundles
             new FOS\UserBundle\FOSUserBundle(),
             new Sonata\UserBundle\SonataUserBundle('FOSUserBundle')
            ]
    }
```

- Remove **import** statements from config.php for EdcomsContentBundle and EdcomsBadgeBundle services. Read more at the corresponding CHANGELOG documents: [ContentBundle](src/EdcomsCMS/ContentBundle/CHANGELOG.md), [BadgeBundle](src/EdcomsCMS/BadgeBundle/CHANGELOG.md)

```
# app/config.config.yml
imports:
    - { resource: parameters.yml }
    - { resource: security.yml }
    - { resource: services.yml }
    - { resource: '@EdcomsCMSContentBundle/Resources/config/services.yml' }    <-- Must be removed
    - { resource: '@EdcomsCMSBadgeBundle/Resources/config/services.yml' }    <-- Must be removed
```

- Add new admin routes to **routing.yml** file as shown below:

```
# app/config/routing.yml

# Sonata User
sonata_user_admin_security:
    resource: '@SonataUserBundle/Resources/config/routing/admin_security.xml'
    prefix: /admin

sonata_user_admin_resetting:
    resource: '@SonataUserBundle/Resources/config/routing/admin_resetting.xml'
    prefix: /admin/resetting

# Sonata Admin
admin_area:
    resource: "@SonataAdminBundle/Resources/config/routing/sonata_admin.xml"
    prefix: /admin

_sonata_admin:
    resource: .
    type: sonata_admin
    prefix: /admin

```

- Enable Symfony **Translator** service

```
# app/config/config.yml

framework:
    translator: { fallbacks: ['%locale%'] }

```

- Add configuration for **Edcoms Admin** Bundle

```
# app/config/config.yml

edcoms_cms_admin: ~

```

- Add EdcomsCMSAdminBundle to **assetic configuration**

```
# app/config/config.yml

assetic:
    debug:          "%kernel.debug%"
    use_controller: true
    bundles:        [ EdcomsCMSTemplatesBundle, EdcomsCMSAdminBundle ]
    #java: /usr/bin/java
    filters:
        cssrewrite: ~
```

- Add Doctrine mappings to the default Entity Manager as shown below:

```
# app/config/config.yml
    orm:
        default_entity_manager: default
        auto_generate_proxy_classes: "%kernel.debug%"
        entity_managers:
            default:
                connection: default
                mappings:
                    EdcomsCMSAuthBundle: ~
                    EdcomsCMSBadgeBundle: ~
                    EdcomsCMSContentBundle: ~
                    EdcomsCMSUserBundle: ~
                    FOSUserBundle: ~
                    AppBundle: ~
            edcoms_cms:
                connection: default
                mappings:
                    EdcomsCMSAuthBundle: ~
                    EdcomsCMSBadgeBundle: ~
                    EdcomsCMSContentBundle: ~
                    EdcomsCMSUserBundle: ~
                    FOSUserBundle: ~
                auto_mapping: true
            destination_database:
                connection: destination_database
                mappings:
                    EdcomsCMSAuthBundle: ~
                    EdcomsCMSBadgeBundle: ~
                    EdcomsCMSContentBundle: ~

```

- Update *security.yml* (role_hierarchy, encoders, providers, add admin firewall, add access control) to secure CMS(AdminBundle) area

```
# app/config/security.yml

security:
    role_hierarchy:
        ROLE_ADMIN:       [ROLE_USER, ROLE_SONATA_ADMIN]
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]
    encoders:
        FOS\UserBundle\Model\UserInterface: bcrypt
    providers:
        fos_userbundle:
            id: fos_user.user_provider.username
    firewalls:
        admin:
            pattern:            /admin(.*)
            context:            user
            form_login:
                provider:       fos_userbundle
                login_path:     /admin/login
                use_forward:    false
                check_path:     /admin/login_check
                failure_path:   null
                default_target_path: sonata_admin_dashboard
            logout:
                path:           /admin/logout
                target:         sonata_admin_dashboard
            anonymous:          true
    access_control:
        - { path: ^/admin/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin/logout$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin/login_check$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin/resetting, role: IS_AUTHENTICATED_ANONYMOUSLY }

        # Secured part of the site
        # This config requires being logged for the whole site and having the admin role for the admin part.
        # Change these rules to adapt them to your needs
        - { path: ^/admin/, role: [ROLE_ADMIN, ROLE_SONATA_ADMIN] }
        
        - { path: ^/.*, role: IS_AUTHENTICATED_ANONYMOUSLY }
```
### Features

### Performance Improvements

### Bug Fixes

