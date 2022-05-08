(function () {
  'use strict';

  angular
		.module('common.permissions')
		.factory('permissionsHelper', permissionsHelper);

	/* @ngInject */
  function permissionsHelper() {
    let permissions = {
      list: {},
      default: false
    };

    let service = {
      get,
      set,
      hasPermissions
    };

    return service;
    
    /**
     * Get the array of permission for current user
     * 
     * @returns array of permissions
     */
    function get() {
      return permissions;
    }

    /**
     * Set array of permissions for current user
     * 
     * @param {any} newPermissions 
     */
    function set(newPermissions) {
      permissions = newPermissions;
    }

    /**
     * Check permissions for a certain context and name
     * 
     * @param {any} context 
     * @param {any} name 
     * @returns {boolean}
     */
    function hasPermissions(context, name) {
      let permissionsList = permissions.list;
      return permissionsList[context] ? permissionsList[context][name] : permissions.default;
    }
  }
})();
