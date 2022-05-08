(function () {
  'use strict';

  angular
		.module('common.notification')
		.factory('notificationHelper', notificationHelper);

	/* @ngInject */

  function notificationHelper($log) {

    let service = {
      success,
      error,
      setErrorMessage
    };

    return service;
		///////////////

    /**
		 * Display success message
		 */
    function success(message) {
      toastr.success(message);
      $log.info(message);
    }

    /**
		 * Display error message
		 */
    function error(message) {
      toastr.error(message);
      $log.error(message);
    }

		/**
		 * Set notification error message
		 */
    function setErrorMessage(type, resp) {
      notificationHelper.error(`Fatal error: ${type} failed. ${resp.status} : ${resp.statusText}`);
    }

  }
})();
