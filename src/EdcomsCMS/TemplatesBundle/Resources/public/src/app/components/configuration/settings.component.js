(function () {
  'use strict';

  angular.module('cms.configuration')
    .component("cmsSettings", {
      templateUrl: '/bundles/edcomscmstemplates/src/app/components/configuration/settings.html',
      bindings: {},
      controller: SettingsController,
      controllerAs: 'vm'
    });

  /* @ngInject */

  function SettingsController(dataMediaType) {
    let vm = this;

    vm.action = {
      labels: 'Add',
      actions: {
        add: {
          tag: 'a',
          link: '#configuration/settings/mediatype/create',
          color: 'primary'
        }
      }
    };

    getMediaTypesComplete();

    function getMediaTypesComplete() {
      dataMediaType.getMediaTypes().then(function (data) {
        vm.mediatypes = data.media_types;
      });
    }
  }
})();
