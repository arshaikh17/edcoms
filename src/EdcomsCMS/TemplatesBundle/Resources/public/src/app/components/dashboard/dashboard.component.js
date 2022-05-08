(function () {
  'use strict';

  angular.module('cms.dashboard')
		.component("cmsDashboard", {
  bindings: {},
  templateUrl: '/bundles/edcomscmstemplates/src/app/components/dashboard/dashboard.html',
  controllerAs: 'vm',
  controller: DashboardController
});

	/* @ngInject */

  function DashboardController(dataDashboard) {
    let vm = this;

    vm.reports = [];
    vm.title = 'Dashboard';

    vm.action = {
      labels: 'View',
      actions: {
        view: {
          link: '#contents',
          color: 'primary'
        }
      }
    };

    getReports();

    function getReports() {
      dataDashboard.getReports().then(function (data) {
        vm.reports = data;
      });
    }
  }
})();
