(function () {
  'use strict';

  angular.module('cms.user')
		.component("cmsUpdateUser", {
  templateUrl: '/bundles/edcomscmstemplates/src/app/components/user/update.html',
  controller: UpdateUserController,
  controllerAs: 'vm',
  bindings: {}
});

	/* @ngInject */

  function UpdateUserController($location, $routeParams, userAPI, commonHelper, notificationHelper) {
    let vm = this;

    vm.user = {};
    vm.user.UserCreate = {};
    vm.contacts = [];

    vm.back = commonHelper.back;
    vm.callFunction = commonHelper.callFunction;
    vm.updateUser = updateUser;
    vm.addContact = addContact;
    vm.deleteContact = deleteContact;
    vm.checkboxGroupSelected = commonHelper.checkboxGroupSelected;
    vm.updateGroup = updateGroup;

    vm.action = vm.topAction = {
      labels: 'Save, delete or cancel',
      actions: {
        cancel: {
          button: vm.back,
          type: 'button',
          color: 'white'
        },
        delete: {
          link: '#users/delete/',
          color: 'red'
        },
        submit: {
          button: vm.updateUser,
          type: 'submit',
          color: 'primary'
        }
      }
    };


    initForm();
		// Create new user
    function initForm() {
      let id = $routeParams.userId || -1;
      userAPI.getUser(id).then(function (resp) {
        let userGroups = resp.data.user.groups;

        vm.groups = resp.data.groups;
        vm.required = resp.data.required;

        vm.title = resp.data.user.person.firstName ? 'Update ' + resp.data.user.person.firstName + ' ' + resp.data.user.person.lastName : 'Create a new user';

        vm.user.UserCreate = resp.data.user;
        vm.user.UserCreate._token = resp.token;
        vm.contacts = resp.data.user.person.contacts;
        vm.user.UserCreate.groups = [];

        if (id === -1) {
          vm.user.UserCreate.id = -1;
          vm.user.UserCreate.person = {};
          vm.user.UserCreate.person.contacts = [];
          vm.user.UserCreate.person.contacts[0] = {};
          vm.user.UserCreate.person.contacts[0].type = 'email';
          vm.user.UserCreate.person.contacts[0].title = 'Email';

          vm.contacts = [];
          vm.contacts.push({
            id: 0
          }); // Contact email is required
        } else {
          for (let i in userGroups) {
            vm.user.UserCreate.groups.push(userGroups[i].id);
          }
        }
      });

    }

		// Update user
    function updateUser() {
			// Get data if any
      if (vm.userCreate.$valid) {
        userAPI.updateUser(vm.user)
					.then(function (resp) {
  if (resp.status) {
    let action = vm.user.UserCreate.id === -1 ? 'created' : 'updated';
    notificationHelper.success('User ' + vm.user.UserCreate.username + ' has been successfully ' + action + '.');
    $location.url('/users');
  } else {
    notificationHelper.error('Error: update user failed.' + resp.errors);
  }
});
      }
    }
		//};

    function addContact() {
			//var newItemNo = vm.contacts.length;
      vm.contacts.push({});
    }

    function deleteContact(index) {
      vm.contacts.splice(index, 1);
      delete vm.user.UserCreate.person.contacts[index];
    }

    function updateGroup(id) {
      let groups = vm.user.UserCreate.groups;
      let idx = groups.indexOf(id);

      if (idx > -1) {
        groups.splice(idx, 1);
      } else {
        groups.push(id);
      }
    }

  }
})();
