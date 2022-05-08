(function () {
  'use strict';

  angular.module('cms', [
    'common',
    'cms.core',
    'cms.data',
    'cms.layout',
    'cms.sidemenu',
    'cms.dashboard',
    'cms.user',
    'cms.media',
    'cms.content',
    'cms.configuration',
    'cms.userGeneratedContent'
  ])
		.run(initApp);

	/* @ngInject */
  function initApp(permissionsHelper, $rootScope, notificationHelper, $location, userAPI, userHelper) {
		// Set toast library options
    toastr.options = {
      "closeButton": true,
      "positionClass": "toast-bottom-right"
    };

		/**
		 * Retrieve current user permissions and save them
		 */
    userAPI.getPermissions().then((data) => {
      const permissions = {
        list: data.permissions,
        default: data.defaultPermission
      };
      permissionsHelper.set(permissions);
      $rootScope.$broadcast('permissions:update');
      userHelper.set(data.currentUser);

      $rootScope.$on('$routeChangeStart', function (event, next) {
        if (!permissionsHelper.hasPermissions(next.$$route.context, 'read')) {
          notificationHelper.error(`Oops! You don't have the authorisation to access to this page!`);
          $location.path('/');
        }
      });
    });

  }
})();
