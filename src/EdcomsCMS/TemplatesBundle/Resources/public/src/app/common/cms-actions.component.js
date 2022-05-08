(function () {
  'use strict';

  angular
    .module('common')
    .component('cmsActions', {
      bindings: {
        actions: '=',
        id: '=',
        call: '&'
      },
      templateUrl: '/bundles/edcomscmstemplates/src/app/common/cms-actions.html',
      controllerAs: 'vm',
      controller: cmsActionsComponent,
    });

  /* @ngInject */
  function cmsActionsComponent() {
  }
})();
