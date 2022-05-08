(function () {
  'use strict';

  angular.module('common')
		.factory('commonHelper', commonHelper);

	/* @ngInject */

  function commonHelper($window) {

    let service = {
      callFunction: callFunction,
      back: back,
      checkboxGroupSelected: checkboxGroupSelected
    };

    return service;
		///////////////

    function callFunction(fn, args) {
      fn(args);
    }

    function back() {
      $window.history.back();
    }

    function checkboxGroupSelected(object) {
      if (object) {
        return Object.keys(object).some(function (key) {
          return object[key];
        });
      } else {
        return false;
      }
    }


  }

})();
