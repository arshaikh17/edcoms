(function () {
  'use strict';

  angular
    .module('cms.data')
    .factory('dataDashboard', dataDashboard);

  /* @ngInject */
  function dataDashboard($http, notificationHelper) {

    const service = {
      getReports: getReports
    };

    return service;

    function getReports() {
      return $http.get('/cms/reporting')
        .then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: update user failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

  }
})();
