(function () {

  'use strict';

  angular.module('cms.core')
    .config(configure);

    /* @ngInject */

  function configure ($routeProvider, routeHelperConfigProvider) {
        // Configure the common route provider
    routeHelperConfigProvider.config.$routeProvider = $routeProvider;
  }

})();
