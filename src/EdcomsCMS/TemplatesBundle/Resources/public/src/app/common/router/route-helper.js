(function () {
  'use strict';

  angular
		.module('common.router')
		.provider('routeHelperConfig', routeHelperConfig)
		.factory('routeHelper', routeHelper);


	/* @ngInject */

	// Must configure via the routeHelperConfigProvider
  function routeHelperConfig() {
    this.config = {
      docTitle: 'EdComs Connect CMS'
    };

    this.$get = function () {
      return {
        config: this.config
      };
    };
  }

  function routeHelper($location, $rootScope, $route, routeHelperConfig) {
    let handlingRouteChangeError = false;
    let routeCounts = {
      errors: 0,
      changes: 0
    };
    let routes = [];
    let $routeProvider = routeHelperConfig.config.$routeProvider;

    let service = {
      configureRoutes: configureRoutes,
      getRoutes: getRoutes,
      routeCounts: routeCounts
    };

    init();

    return service;
		///////////////

    function configureRoutes(routes) {
      routes.forEach(function (route) {
        route.config.resolve =
					angular.extend(route.config.resolve || {}, routeHelperConfig.config.resolveAlways);
        $routeProvider.when(route.url, route.config);
      });
      $routeProvider.otherwise({
        redirectTo: '/'
      });
    }

    function handleRoutingErrors() {
			// Route cancellation:
			// On routing error, go to the dashboard.
			// Provide an exit clause if it tries to do it twice.
      $rootScope.$on('$routeChangeError', function () {
        if (handlingRouteChangeError) {
          return;
        }
        routeCounts.errors++;
        handlingRouteChangeError = true;
        $location.path('/');
      });
    }

    function init() {
      handleRoutingErrors();
      updateDocTitle();
    }

    function getRoutes() {
      routes = [];
      for (let prop in $route.routes) {
        if ($route.routes.hasOwnProperty(prop)) {
          let route = $route.routes[prop];
          let isRoute = !!route.title;
          if (isRoute) {
            routes.push(route);
          }
        }
      }

      return routes;
    }


    function updateDocTitle() {
      $rootScope.$on('$routeChangeSuccess', function (event, current) {
        routeCounts.changes++;
        handlingRouteChangeError = false;
        let title = `${current.title} - ${routeHelperConfig.config.docTitle}`;

                // data bind to <title>
        $rootScope.title = title;
      });
    }
  }
})();
