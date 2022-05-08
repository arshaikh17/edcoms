(function () {
  'use strict';


  angular
		.module('cms.data')
		.factory('dataStructure', dataStructure);

	/* @ngInject */
  function dataStructure($http, notificationHelper) {
    const service = {
      getStructure: getStructure,
      deleteCheckStructure: deleteCheckStructure,
      deleteStructure: deleteStructure,
      getDeletedStructureItems: getDeletedStructureItems,
      restoreDeletedStructureItem: restoreDeletedStructureItem
    };

    return service;

    function getStructure(id) {
      let url = '/cms/structure';
      if (angular.isDefined(id)) {
        url += '/' + id;
      }
      return $http.get(url)
				.then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: update user failed. ' + resp.status + ': ' + resp.statusText);
        });
    }


    function deleteCheckStructure(id) {
      return $http.get('/cms/structure/delete/check/' + id)
				.then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: delete check structure failed. ' + resp.status + ': ' + resp.statusText);
        });
    }


    function deleteStructure(id, mode) {
      return $http.delete('/cms/structure/delete/' + id + '/' + mode)
				.then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: delete structure failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

    function getDeletedStructureItems() {
      return $http.get('/cms/structure/deletedlist')
				.then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: get deleted structure items failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

    function restoreDeletedStructureItem(id) {
      return $http.post(' /cms/structure/restore/' + id)
				.then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: restore deleted structure item failed. ' + resp.status + ': ' + resp.statusText);
        });
    }
  }
})();
