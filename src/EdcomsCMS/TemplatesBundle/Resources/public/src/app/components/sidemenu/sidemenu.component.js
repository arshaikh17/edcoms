(function () {
  'use strict';

  angular
    .module('cms.sidemenu')
    .component('cmsSideMenu', {
      templateUrl: '/bundles/edcomscmstemplates/src/app/components/sidemenu/sidemenu.html',
      bindings: {},
      controller: SidemMenuController,
      controllerAs: 'vm'
    });

  /* @ngInject */

  function SidemMenuController($rootScope, $route, routeHelper, treeHelper, $location) {
    let routesSide = routeHelper.getRoutes();

    this.header = '';
    this.showChildren = treeHelper.showChildren;
    this.setSelected = treeHelper.setSelectedItem;

    let getNavRoutes = () => {
      this.tree = undefined;
      this.title = undefined;
      this.items = undefined;

      for (let r in routesSide) {
        if ($location.path().match(routesSide[r].regexp)) {
          let sidemenu = routesSide[r].sidemenu;
          if (sidemenu) {
            this.title = sidemenu.title;
            this.items = sidemenu.items;
            this.type = sidemenu.type;
          }
        }
      }

      // If sidemenu  defined and items are not defined, show tree
      // TODO: To improve
      if (angular.isUndefined(this.items) && angular.isDefined(this.title)) {
        treeHelper.getFromAPI(this.type).then((data) => {
          this.tree =  data;
          let selectedItem = treeHelper.getSelectedItem();

          /** reset select item for tree if we're in another state / route
           ** of the sidemenu for contents
           */
          if ($route.current.$$route.originalPath.indexOf('contents/') > -1) {
            this.selected = null;
          } else {
            this.selected = selectedItem;
          }
        });
      }
    };

    getNavRoutes();

    $rootScope.$on('$locationChangeSuccess', getNavRoutes);
    $rootScope.$on('tree.update', () => {
      let selectedItem = treeHelper.getSelectedItem();
      this.selected = selectedItem;
    });

    this.isCurrent = (route) => {
      if (!$route.current) {
        return '';
      }
      return route.url.match($route.current.regexp) ? 'is-active' : '';
    };
  }
})();
