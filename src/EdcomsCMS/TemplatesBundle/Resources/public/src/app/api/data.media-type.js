(function () {
  'use strict';

  angular.module('cms.data')
		.factory('dataMediaType', dataMediaType);

	/* @ngInject */
  function dataMediaType($http, notificationHelper) {
    const service = {
      getMediaTypes: getMediaTypes,
      getMediaType: getMediaType,
      updateMediaType: updateMediaType
    };

    return service;

    function getMediaTypes() {
      return $http.get('/cms/settings/target/get')
				.then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: get media types failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

    function getMediaType(id) {
      return $http.get('/cms/settings/target/update/' + id)
				.then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: get media type failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

    function updateMediaType(data) {
      let url = '/cms/settings/target/update';
      if (angular.isDefined(data.MediaTypeCreate.id)) {
        url += '/' + data.MediaTypeCreate.id;
      }

      return $http.post(url, data)
				.then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: update media types failed. ' + resp.status + ': ' + resp.statusText);
        });
    }
  }
})();
