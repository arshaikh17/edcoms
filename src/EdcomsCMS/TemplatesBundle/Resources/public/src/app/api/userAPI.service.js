(function () {
  'use strict';

	/**
	* User related endpoints calls
	*/
  angular
		.module('cms.data')
		.factory('userAPI', userAPI);

	/* @ngInject */

  function userAPI($http, notificationHelper) {

    let service = {
      getUsers: getUsers,
      getUser: getUser,
      updateUser: updateUser,
      deleteUser: deleteUser,
      getPermissions: getPermissions
    };

    return service;

		/**
		* Get the permissions for the current user
		*/
    function getUsers() {
      return $http.get('/cms/users/get')
        .then((data) => data.data.users)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: update user failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

		/**
		* Get the permissions for the current user
		*/
    function getUser(id) {
      return $http.get('/cms/users/update/' + id)
				.then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: update user failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

		/**
		* Get the permissions for the current user
		*/
    function updateUser(data) {
      let url = '/cms/users/update';

      if (angular.isDefined(data.UserCreate.id)) {
        url += '/' + data.UserCreate.id;
      }

      return $http.post(url, data)
				.then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: update user failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

		/**
		* Get the permissions for the current user
		*/
    function deleteUser(id) {
      return $http.delete('/cms/users/delete/' + id)
				.then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error('Fatal error: update user failed. ' + resp.status + ': ' + resp.statusText);
        });
    }

		/**
		* Get the permissions for the current user
		*/
    function getPermissions(context, name) {
      let url = '/cms/users/get_perms';

      if (context && name) {
        url = `/cms/users/check_perm/${context}/${name}`;
      }

      return $http.get(url)
				.then((data) => data.data)
        .catch(function (resp) {
          notificationHelper.error(`Fatal error: gettting permissions failed. ${resp.status}: ${resp.statusText}`);
        });
    }
  }
})();
