(function () {
  'use strict';

  angular
    .module('cms.layout')
    .component('cmsHeader', {
      templateUrl: '/bundles/edcomscmstemplates/src/app/layout/header.html',
      bindings: {},
      controller: HeaderController,
      controllerAs: 'vm'
    });

	/* @ngInject */
  function HeaderController(userHelper, $rootScope) {
    $rootScope.$on('user:update', () => {
      this.user = userHelper.get();
    });
  }
})();
