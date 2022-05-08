(function () {
  'use strict';

  angular.module('cms.user')
		.component("cmsViewUser", {
  templateUrl: '/bundles/edcomscmstemplates/src/app/components/user/user.html',
  controller: ViewUserController,
  controllerAs: 'vm',
  bindings: 'vm'
});

	/* @ngInject */

  function ViewUserController($routeParams, userAPI, commonHelper) {
    let vm = this;
		/* === Users == */
    vm.title = 'Create a new user';
    vm.user = {};

    vm.back = commonHelper.back;
    vm.callFunction = commonHelper.callFunction;


    vm.action = {
      labels: 'Edit or go back',
      actions: {
        cancel: {
          button: vm.back,
          type: 'button',
          color: 'white'
        },
        edit: {
          link: '#users/update/',
          color: 'primary'
        }
      }
    };

    viewUser();

		// Create new user
    function viewUser() {
      userAPI.getUser($routeParams.userId).then(function (resp) {
        vm.user = resp.data.user;
      });
    }
  }
})();
