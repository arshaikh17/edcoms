(function () {
  'use strict';

  angular.module('cms.data')
		.factory('dataUserGeneratedContent', dataUserGeneratedContent);

	/* @ngInject */

  function dataUserGeneratedContent($http, notificationHelper) {
    let service = {
      getUserGeneratedContentList: getUserGeneratedContentList,
      getUserGeneratedContentEntry: getUserGeneratedContentEntry,
      updateUserGeneratedContentEntry: updateUserGeneratedContentEntry,
      updateUserGeneratedContentEntryStatus: updateUserGeneratedContentEntryStatus,
      getUserGeneratedContentTypes: getUserGeneratedContentTypes,
      getUserGeneratedContentType: getUserGeneratedContentType,
      updateUserGeneratedContentType: updateUserGeneratedContentType
    };

    return service;

		/**
    * Get list of UGC entries
    */
    function getUserGeneratedContentList(id) {
      return $http.get('/cms/user-generated-content/list/' + id)
				.then(data => data.data)
        .catch(function () {
          notificationHelper.setErrorMessage('get user generated content list');
        });
    }

    /**
    * Get UGC entry details by id
    */
    function getUserGeneratedContentEntry(id) {
      return $http.get('/cms/user-generated-content/entry/' + id)
				.then(data => data.data)
        .catch(function () {
          notificationHelper.setErrorMessage('get user generated content entry');
        });
    }

    /**
    * Update UGC entry
    */
    function updateUserGeneratedContentEntry(data) {
      let url = '/cms/user-generated-content/entry';

      if (angular.isDefined(data.ContentCreate.id)) {
        url += '/' + data.ContentCreate.id;
      }
      return $http.post(url, data)
				.then(data => data.data)
        .catch(function () {
          notificationHelper.setErrorMessage('update user generated content entry');
        });
    }

    /**
    * Update UGC entry status
    */
    function updateUserGeneratedContentEntryStatus(id, data) {
      let url = '/cms/user-generated-content/entry/status/' + id;

      return $http.post(url, data)
				.then(data => data.data)
        .catch(function () {
          notificationHelper.setErrorMessage('update user generated content entry');
        });
    }

    /**
    * Get list of UGC types
    */
    function getUserGeneratedContentTypes() {
      return $http.get('/cms/user-generated-content/get')
				.then(data => data.data)
        .catch(function () {
          notificationHelper.setErrorMessage('get user generated content types');
        });
    }

    /**
    * Get UGC type by id
    */
    function getUserGeneratedContentType(id) {
      return $http.get('/cms/user-generated-content/update/' + id)
				.then(data => data.data)
        .catch(function () {
          notificationHelper.setErrorMessage('get user generated content type');
        });
    }

    /**
    * Update UGC type
    */
    function updateUserGeneratedContentType(data) {
      let url = '/cms/user-generated-content/update';

      if (angular.isDefined(data.UserGeneratedContentFormCreate.id)) {
        url += '/' + data.UserGeneratedContentFormCreate.id;
      }
      return $http.post(url, data)
				.then(data.data)
        .catch(function () {
          notificationHelper.setErrorMessage('update user generated content type');
        });
    }
  }
})();
