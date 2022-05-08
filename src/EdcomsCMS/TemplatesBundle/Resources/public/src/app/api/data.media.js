(function () {
  'use strict';


  angular
    .module('cms.data')
    .factory('dataMedia', dataMedia);

  /* @ngInject */

  function dataMedia($http, notificationHelper) {

    let service = {
      getAllMedia: getAllMedia,
      createFolder: createFolder,
      copyFiles: copyFiles,
      pasteFiles: pasteFiles
    };

    return service;

    function getAllMedia(subdirectory) {
      let url = '/cms/media/list';
      if (subdirectory) {
        url += '/' + subdirectory;
      }
      return $http.get(url)
        .then(data => {
          data = data.data;
          
          // if trying to retrive the root, we need to add the root ourselves
          return subdirectory ? data : {
            title: '/',
            type: 'directory',
            children: data,
            path: '/',
          };
        })
        .catch(function (resp) {
          notificationHelper.error('Fatal error: get all media failed. ' + resp.status + ': ' + resp.statusText);
        });
    }


    function createFolder(data) {
      const url = '/cms/media/create/folder';

      return $http.post(url, data)
        .then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: create folder failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

    function copyFiles(data) {
      const url = '/cms/media/copy';

      return $http.post(url, data)
        .then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: copy files failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

    function pasteFiles(data) {
      const url = '/cms/media/paste/' + data;

      return $http.post(url)
        .then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: paste files failed. ' + resp.status + ': ' + resp.statusText);
        });
    }
  }
})();
