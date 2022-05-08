(function () {
  'use strict';

  angular
		.module('common')
		.directive('pasteMaxLength', pasteMaxLength);

	/** Limit the number of character for an input on paste.
	 * Require maxlength to be added on the input.
	 */
	/* @ngInject */
  function pasteMaxLength() {
    return {
      link: function (scope, elm, attrs) {
        if (attrs.maxlength) {
          elm.on('onpaste', function (e) {
            e.clipboardData.getData('text/plain').slice(0, parseInt(attrs.maxlength));
          });
        }
      }
    };
  }
})();
