(function () {
  'use strict';

  /**
  * Content type CRUD
  */
  angular
    .module('cms.data')
    .factory('dataContentType', dataContentType);

  /* @ngInject */
  function dataContentType($http, notificationHelper) {

    let service = {
      getContentTypes: getContentTypes,
      getContentType: getContentType,
      updateContentType: updateContentType
    };

    return service;

    /**
    * Get a list of content types from the API
    */
    function getContentTypes() {
      return $http.get('/cms/content_type/get')
        .then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: get content types failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

    /**
    * Get a content type item data from API
    * @param {Number} id id of the wanted content type
    */
    function getContentType(id) {
      return $http.get('/cms/content_type/update/' + id)
        .then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: get content type failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

    /**
    * Send updated content data to BE in the expected format by symfony form
    * e.g. in a ContentTypeCreate object
    * Call used to create and edit content type
    * @param {Object} data data to post
    */
    function updateContentType(data) {
      let url = '/cms/content_type/update';
      if (angular.isDefined(data.id)) {
        url += '/' + data.id;
      }

      const toPost = {
        ContentTypeCreate: data
      };

      return $http.post(url, toPost)
        .then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: update content type failed. ' + resp.status + ': ' + resp.statusText);
        });
    }
  }
})();
