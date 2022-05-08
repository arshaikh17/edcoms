(function () {
  'use strict';

  angular
		.module('common.user')
		.factory('userHelper', userHelper);

	/* @ngInject */

  function userHelper($rootScope) {
    let user = null;

    const service = {
      get,
      set
    };

    return service;

		/**
		* Get the current user
		*/
    function get() {
      return user;
    }

		/**
		* Ser the current user
		*/
    function set(newUser) {
      user = newUser;
      $rootScope.$broadcast('user:update');
    }

  }
})();
