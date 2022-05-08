(function () {
  'use strict';

  /**
   *
   * Directive to check the permssions and set element visibility
   * Remove link href but display link content
   * Hide other elements
   * Hide all elements by default and show them only if user has the right permissions
   */
  angular
    .module('common.permissions')
    .directive('permissions', permissions);

  /* @ngInject */
  function permissions($compile, permissionsHelper, $rootScope, $timeout) {
    return {
      restrict: 'A',
      scope: {
        permissions: '@'
      },
      link: function ($scope, $elmt, $attr) {
        // hidding by default
        if (!$attr.ngHref || $elmt.hasClass('m-btn')) {
          $elmt.addClass('ng-hide');
        }

        $scope.$watch('permissions', function () {
          if (Object.keys(permissionsHelper.get()).length) {
            check($attr.permissions.split(';'));
          }
        });

        $rootScope.$on('permissions:update', function () {
          check($attr.permissions.split(';'));
        });

        function check(permissions) {
          // check if permissions contains a context and a name
          if (permissions.length === 2) {
            if (!permissionsHelper.hasPermissions(permissions[0], permissions[1])) {
              // remove href for links
              // TODO: improve class check
              if ($attr.ngHref && !$elmt.hasClass('m-btn')) {
                $timeout(function () {
                  $attr.$set('ngHref', null);
                  $attr.$set('href', null);
                  $elmt.addClass('u-no-link');
                });
              }
              // hide elements for other HTML tags
              else {
                $elmt.addClass('ng-hide');
              }
            } else {
              $elmt.removeClass('ng-hide');
            }
          }
        }
      }
    };
  }
})();
