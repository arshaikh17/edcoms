(function () {
  'use strict';

  angular.module('cms.user')
		.component("cmsDeleteUser", {
  templateUrl: '/bundles/edcomscmstemplates/src/app/components/user/delete.html',
  controller: DeleteUserController,
  controllerAs: 'vm',
  bindings: {}
});

	/* @ngInject */

  function DeleteUserController($scope, $location, $routeParams, userAPI, commonHelper, notificationHelper) {
    let vm = this;
		/* === Users == */
    vm.title = 'Delete';
    vm.user = {};

    vm.back = commonHelper.back;
    vm.callFunction = commonHelper.callFunction;
    vm.deleteUser = deleteUser;

    vm.action = {
      labels: 'Confirm or cancel',
      actions: {
        cancel: {
          button: vm.back,
          type: 'button',
          color: 'white'
        },
        submit: {
          button: vm.deleteUser,
          type: 'button',
          color: 'primary'
        }
      }
    };


    init();

		// Create new user
    function init() {
      userAPI.getUser($routeParams.userId).then(function (data) {
        vm.user = data.data.user;
        vm.title = 'Delete ' + vm.user.person.firstName + ' ' + vm.user.person.lastName;
      });
    }

    function deleteUser() {
      userAPI.deleteUser(vm.user.id)
				.then(function (resp) {
  if (resp.status) {
    notificationHelper.success('User has been successfully deleted.');
    $location.url('/users');
  } else {
    notificationHelper.error('Error: delete user failed');
  }
});
    }
  }
})();
