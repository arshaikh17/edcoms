(function () {
  'use strict';

  angular
		.module('common')
		.directive('cmsUserNav', cmsUserNav);

	/* @ngInject */

	/**
	 * Toggle user navigation
	 */
  function cmsUserNav() {
    return {
      restrict: 'A',
      link: function (scope, elm) {
        elm.on('click', function () {
          angular.element(this).toggleClass('is-active');
          angular.element('.js-usernav').slideToggle();
        });
      }
    };
  }
})();
