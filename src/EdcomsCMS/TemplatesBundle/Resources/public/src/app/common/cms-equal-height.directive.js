(function () {
  'use strict';

  angular
		.module('common')
		.directive('cmsEqualHeight', cmsEqualHeight);

	/* @ngInject */

	/**
	 * Equalise height of child with js-eqH class in container
	 */
  function cmsEqualHeight($timeout) {
    return {
      restrict: 'A',
      link: function (scope, elm) {
				/* ### GRID: Equalise each block height in a grid ### */
        let currentTallest = 0,
          rowDivs = [],
          $el;

        $timeout(function () {
          angular.forEach(elm.find('.js-eqH'), function (value) {
            $el = angular.element(value);
            $el.height('auto');
            rowDivs.push($el);
            currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
						
            for (let currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
              rowDivs[currentDiv].height(currentTallest);
            }
          });

        }, 1000);
      }
    };
  }
})();
