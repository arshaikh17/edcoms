(function () {
  'use strict';

	/**
	 *
	 * Directive to display tree structure
	 * Works recusively
	 */
  angular
		.module('common.tree')
		.directive('cmsTree', cmsTree);

	/* @ngInject */
  function cmsTree($compile) {
    return {
      restrict: 'E',
      scope: {
        tree: '=',
        selected: '=',
        field: '=',
        showChildren: '&',
        action: '&'
      },
      controller: function ($scope) {
				/**
				 * Check if current item is selected or not
				 * selected item objects if from the directive attributes
				 * This is checked recursively
				 */
        this.isSelected = function () {
          if (!$scope.selected) {
            return false;
          }

          let selected = angular.isDefined($scope.tree.id) ? $scope.tree.id : $scope.tree.path;

          if (angular.isArray($scope.selected)) {
            return $scope.selected.indexOf(selected + "") > -1;
          }

          return $scope.selected.id ? $scope.selected.id === selected : $scope.selected.path === selected;
        };
      },
      link: function (scope, element, attrs, ctrl) {
        let template = '';
				// Either an array or a boolean
        let hasChildren = angular.isArray(scope.tree.children) ? scope.tree.children.length : scope.tree.children;

        scope.isSelected = ctrl.isSelected;
        template += '<div ng-class="{ \'is-active\': isSelected(), \'m-tree__leaf\': !tree.children.length, \'m-tree__node\': tree.children.length,  \'is-disabled\': tree.unActive }">';

        if (hasChildren) {
          template += `<i ng-click="showChildren({tree: tree})" class="m-tree__icon material-icons" ng-class="{\'is-hidden\': tree.isOpen}">&#xE5CC;</i>
						<i ng-click="showChildren({tree: tree})" class="m-tree__icon material-icons" ng-class="{\'is-hidden\': !tree.isOpen}">&#xE5CF;</i>`;
        } else {
          template += '<i class="m-tree__icon m-tree__icon--small material-icons">&#xE873;</i>';
        }

        if (scope.tree.unActive) {
          template += '<span class="m-tree__link">{{ tree.title }}</span>';
        } else if (scope.field) {
          template += '<a class="m-tree__link" ng-click="action({key: field, item : tree})">{{ tree.title }}</a>';
        } else {
          template += '<a class="m-tree__link" ng-click="action({item : tree})">{{ tree.title }}</a>';
        }

        if (hasChildren) {
          template += `</div>
						<ul class="m-tree__children" ng-show="tree.isOpen">
						<li ng-repeat="tree in tree.children">
						<cms-tree tree="tree" selected="selected" show-children="showChildren({tree :tree})"`;
          if (scope.field) {
            template += 'action="action({key: key, item: item})" field="field"></cms-tree>';
          } else {
            template += 'action="action({item: item})"></cms-tree>';
          }
          template += `</li>
						</ul>`;
        }

        let newElement = angular.element(template);
        $compile(newElement)(scope);
        element.replaceWith(newElement);
      }
    };
  }
})();
