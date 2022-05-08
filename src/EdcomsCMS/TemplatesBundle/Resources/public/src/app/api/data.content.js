(function () {
  'use strict';

  /**
  * Content CRUD
  */
  angular
    .module('cms.data')
    .factory('dataContent', dataContent);

  /* @ngInject */
  function dataContent($http, notificationHelper) {

    let service = {
      getContents: getContents,
      getContent: getContent,
      updateContent: updateContent,
      getContentTypes: getContentTypes,
      approveContent: approveContent
    };

    return service;

    /**
    * Get a list of contents from the API
    */
    function getContents(structure_id) {
      return $http.get('/cms/content/get/' + structure_id)
        .then(function (data) {
          return data.data;
        })
        .catch(function (resp) {
          notificationHelper.error('Fatal error: update content failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

    /**
    * Get a content item data from API
    * @param {Number} id id of the wanted content
    * @param {Number} ctId content type id of the wanted content
    */
    function getContent(id, ctId) {
      let url = '/cms/content/update/' + id;

      if (angular.isDefined(ctId)) {
        url += '/' + ctId;
      }

      return $http.get(url)
        .then(function (data) {
          return data.data;
        })
        .catch(function (resp) {
          notificationHelper.error('Fatal error: update content failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

    /**
    * Send updated content data to BE in the expected format by symfony form
    * e.g. in a ContentCreate object
    * Call used to create and edit content
    * @param {Object} data data to post
    * @param {Number} ctId content type id of the content to update
    */
    function updateContent(data, ctId) {
      let url = '/cms/content/update';

      if (angular.isDefined(data.id)) {
        url += '/' + data.id + '/' + ctId;
      }

      // Encapsulate data to post in ContentCreate object
      const toPost = {
        ContentCreate: data
      };

      return $http.post(url, toPost)
        .then(function (data) {
          return data.data;
        })
        .catch(function (resp) {
          notificationHelper.error('Fatal error: update content failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

    /**
    * Send content to approve to BE
    * @param {Object} data data to post
    */
    function approveContent(data) {
      let url = '/cms/content/approve';
      if (angular.isDefined(data.id)) {
        url += '/' + data.id;
      }

      return $http.post(url, data)
        .then(function (data) {
          return data.data;
        })
        .catch(function (resp) {
          notificationHelper.error('Fatal error: approve content failed. ' + resp.status + ': ' + resp.statusText);
        });

    }

    /**
    * Get a list of content types form API
    */
    function getContentTypes() {
      return $http.get('/cms/content_type/get')
        .then(function (data) {
          return data.data;
        })
        .catch(function (resp) {
          notificationHelper.error('Fatal error: update content failed. ' + resp.status + ': ' + resp.statusText);
        });
    }
  }
})();
