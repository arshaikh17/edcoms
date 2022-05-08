(function () {
  'use strict';

  angular
		.module('cms.dashboard')
		.run(appRun);

	/* @ngInject */
  function appRun(routeHelper) {
    routeHelper.configureRoutes(getRoutes());
  }

  function getRoutes() {
    return [{
      url: '/',
      config: {
        template: '<cms-dashboard></cms-dashboard>',
        title: 'Dashboard',
        context: 'index',
        settings: {
          nav: 1,
          content: '<i class="m-menu__icon material-icons">&#xE88A;</i> <label class="m-btn__label">Dashboard</label>'
        }
      }
    }];
  }
})();
