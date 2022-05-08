(function () {
  'use strict';

  angular
    .module('cms.media')
    .run(appRun);

  /* @ngInject */

  function appRun(routeHelper) {
    routeHelper.configureRoutes(getRoutes());
  }

  function getRoutes() {
    const sidemenu = {
      title: 'Media management',
      type: 'media'
    };

    return [{
      url: '/media',
      config: {
        template: '<cms-media></cms-media>',
        title: 'Media',
        context: 'media',
        settings: {
          nav: 3,
          content: '<i class="m-menu__icon material-icons">&#xE3B6;</i><label class="m-btn__label"> Media</label>'
        },
        sidemenu: sidemenu
      }
    }, {
      url: '/media/create',
      config: {
        template: '<cms-update-media></cms-update-media>',
        title: 'Create media',
        sidemenu: sidemenu,
        context: 'media'
      }
    }];
  }
})();
