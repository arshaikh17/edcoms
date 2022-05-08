(function () {
  'use strict';

  angular.module('cms.user')
		.component("cmsUsers", {
  templateUrl: '/bundles/edcomscmstemplates/src/app/components/user/users.html',
  controller: UsersController,
  controllerAs: 'vm',
  bindings: {}
});

	/* @ngInject */

  function UsersController(userAPI, commonHelper) {
    let vm = this;

    vm.items = [];
    vm.title = 'Users';

		/* === Common == */
    vm.loading = true;

    vm.callFunction = commonHelper.callFunction;

    vm.message = 'Hey, feel free to create or edit users from here. Add new ones below or use the filter to find users easily!!';

    vm.itemsActions = {
      view: {
        link: '#/users/view/',
        color: 'white',
        access: 'user;read'
      },
      edit: {
        link: '#/users/update/',
        color: 'primary',
        access: 'user;edit'
      },
      delete: {
        link: '#/users/delete/',
        color: 'red',
        access: 'user;delete'
      }
    };

    vm.action = {
      labels: 'Create a new user',
      actions: {
        add: {
          link: '#users/create',
          color: 'primary',
          access: 'user;create'
        }
      }
    };

    getUsers();

		// Get users list
    function getUsers() {
      userAPI.getUsers().then(function (data) {
        vm.items = data;
        for (let i in vm.items) {
          vm.items[i].displayedName = vm.items[i].person.firstName + ' ' + vm.items[i].person.lastName;
        }
        if (!vm.items.length) {
          vm.messages = 'Hey there! There is no users created yet. Start adding users now.';

        }
        return vm.items;
      })
				.finally(function () {
					//Set the loading is false.
  vm.loading = false;
});
    }

  }
})();
