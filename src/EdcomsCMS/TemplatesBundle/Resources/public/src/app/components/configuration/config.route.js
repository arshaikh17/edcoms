(function () {
  'use strict';

  angular
		.module('cms.configuration')
		.run(appRun);


	/* @ngInject */
  function appRun(routeHelper) {
    routeHelper.configureRoutes(getRoutes());
  }

  function getRoutes() {
    const sidemenu = {
      title: 'Configure your CMS',
      items: {
        0: {
          url: '/configuration/settings',
          content: '<i class="m-tree__icon m-tree__icon--small material-icons">&#xE7FD;</i>Site settings',
          context: 'configuration.settings'
        },
        1: {
          url: '/configuration/content-type',
          content: '<i class="m-tree__icon m-tree__icon--small material-icons">&#xE7FD;</i>Content type',
          context: 'configuration.contentTypes'
        }
      }
    };

    return [{
      url: '/configuration/settings',
      title: 'Configuration',
      config: {
        template: '<cms-settings></cms-settings>',
        title: 'Configure your CMS',
        context: 'configuration.settings',
        settings: {
          nav: 6,
          content: '<i class="m-menu__icon material-icons">&#xE8B8;</i> <label class="m-btn__label">Configuration</label>'
        },
        sidemenu: sidemenu
      }
    },{
      url: '/configuration/content-type',
      title: 'Configuration',
      config: {
        template: '<cms-content-types content-types="$resolve.contentTypes"><cms-content-types>',
        title: 'Content types',
        context: 'configuration.contentTypes',
        sidemenu: sidemenu,
        resolve: {
          /* @ngInject */
          contentTypes(dataContentType) {
            return dataContentType.getContentTypes().then(data => data.content_types);
          }
        },
      }
    }, {
      url: '/configuration/content-type/create',
      title: 'Configuration',
      config: {
        template: '<cms-update-content-type data="$resolve.contentTypeData"></cms-update-content-type>',
        title: 'Create content type',
        sidemenu: sidemenu,
        context: 'configuration.contentTypes',
        resolve: {
          /* @ngInject */
          contentTypeData(dataContentType) {
            return dataContentType.getContentType().then((data) => data);
          }
        }
      }
    }, {
      url: '/configuration/content-type/update/:contentTypeId',
      title: 'Configuration',
      config: {
        template: '<cms-update-content-type data="$resolve.contentTypeData"></cms-update-content-type>',
        title: 'Update content type',
        sidemenu: sidemenu,
        context: 'configuration.contentTypes',
        resolve: {
          /* @ngInject */
          contentTypeData(dataContentType, $route) {
            return dataContentType.getContentType($route.current.params.contentTypeId).then((data) => data);
          }
        }
      }
    },{
      url: '/configuration/settings/mediatype/create',
      title: 'Configuration',
      config: {
        template: '<cms-update-media-type></cms-update-media-type>',
        title: 'Create media type',
        context: 'configuration.mediaTypes',
        sidemenu: sidemenu
      }
    }, {
      url: '/configuration/settings/mediatype/update/:mediaId',
      title: 'Configuration',
      config: {
        template: '<cms-update-media-type></cms-update-media-type>',
        title: 'Update media type',
        sidemenu: sidemenu,
        context: 'configuration.mediaTypes'
      }
    }];
  }
})();
