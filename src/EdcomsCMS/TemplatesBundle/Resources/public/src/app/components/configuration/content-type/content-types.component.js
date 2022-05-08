(function () {
  'use strict';

  angular
    .module('cms.contentType')
    .component("cmsContentTypes", {
      templateUrl: '/bundles/edcomscmstemplates/src/app/components/configuration/content-type/content-types.html',
      bindings: {
        contentTypes: '<',
      },
      controllerAs: 'vm',
      controller: ContentTypesController,
    });

  /* @ngInject */
  function ContentTypesController() {
    this.action = {
      labels: 'Add',
      actions: {
        add: {
          tag: 'a',
          link: '#configuration/content-type/create',
          color: 'primary'
        }
      }
    };
  }

})();
