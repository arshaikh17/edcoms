(function () {
  'use strict';

  angular
  .module('cms.layout')
  .component('cmsMainMenu', {
    templateUrl: '/bundles/edcomscmstemplates/src/app/layout/mainmenu.html',
    bidings: {},
    controller: MainMenuController,
    controllerAs: 'vm'
  });


	/* @ngInject */
  function MainMenuController($route, routeHelper) {
    let vm = this;

    vm.header = '';
    let routes = routeHelper.getRoutes();

    getNavRoutes();

    function getNavRoutes() {
      vm.navRoutes = routes.filter(function (r) {
        if (r.header) {
          vm.header = r.header;
        }
        return r.settings && r.settings.nav;
      }).sort(function (r1, r2) {
        return r1.settings.nav - r2.settings.nav;
      });
    }

    vm.isCurrent = (route) => {
      if (!route.title || !$route.current || !$route.current.title) {
        return '';
      }
      let menuName = route.title;
      return $route.current.title.substr(0, menuName.length) === menuName ? 'is-active' : '';
    };
  }
})();
