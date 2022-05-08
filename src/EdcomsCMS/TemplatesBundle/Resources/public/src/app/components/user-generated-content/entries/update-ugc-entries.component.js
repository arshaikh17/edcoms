(function () {
  'use strict';

  angular.module('cms.userGeneratedContent')
		.component("cmsUpdateUgcEntries", {
  templateUrl: '/bundles/edcomscmstemplates/src/app/components/user-generated-content/entries/update.html',
  controller: UpdateUgcEntriesController,
  controllerAs: 'vm',
  bindings: {}
});

	/* @ngInject */

	// UGC stand for User Generated Content
  function UpdateUgcEntriesController($routeParams, $location, dataUserGeneratedContent, commonHelper, notificationHelper) {
    let vm = this;

    vm.back = commonHelper.back;
    vm.callFunction = commonHelper.callFunction;
    vm.updateUgcEntry = updateUgcEntry;
    vm.required = [];

    vm.action = vm.topAction = {
      labels: 'Save or cancel',
      actions: {
        cancel: {
          button: vm.back,
          type: 'button',
          color: 'white'
        },
        submit: {
          button: vm.updateUgcEntry,
          type: 'submit',
          color: 'primary'
        }
      }
    };

    initForm();


		//
		// Edit ugc
    function initForm() {
      dataUserGeneratedContent.getUserGeneratedContentEntry($routeParams.ugcEntryId)
				.then(function (data) {
  vm.title = 'Update ' + data.data.content.title;
  vm.ugc = {};
  vm.ugc.ContentCreate = {};
  vm.ugc.ContentCreate.structure = {};
  vm.ugc.ContentCreate.structure.parent = data.data.content.structure.parent.id;
  if (data.data.content.structure.link) {
    vm.ugc.ContentCreate.structure.link = data.data.content.structure.link;
  } else {
						// Regexp [^\w] => replace everything which is not in this safe list (digits, alphabet and underscore)
    vm.ugc.ContentCreate.structure.link = (data.data.content.title).replace(/[^\w]/g, '-').toLowerCase();
  }
  vm.ugc.ContentCreate.title = data.data.content.title;
  vm.ugc.ContentCreate.id = $routeParams.ugcEntryId;
  vm.ugc.ContentCreate.status = data.data.content.status;

  if (data.data.content.templateFile) {
    vm.ugc.ContentCreate.templateFile = data.data.content.templateFile.id;
  } else {
    vm.ugc.ContentCreate.templateFile = data.data.template_files[0].id; // Default to first in array
  }

  vm.ugc.ContentCreate._token = data.token;

					// Set fields
  vm.fields = data.data.fields;

  for (let i in vm.fields) {
    if (data.data.field_data[i]) {
      if (vm.fields[i].fieldType === 'file_array') {
        vm.ugc.ContentCreate[vm.fields[i].name] = angular.fromJson(data.data.field_data[i]);
      } else {
        vm.ugc.ContentCreate[vm.fields[i].name] = data.data.field_data[i];
      }
      if (vm.fields[i].required) {
        vm.required[vm.fields[i].name] = vm.fields[i].required;
      }
    } else if (vm.fields[i].defaultValue) {
      vm.ugc.ContentCreate[vm.fields[i].name] = vm.fields[i].defaultValue;
    }
  }
  vm.templateFiles = data.data.template_files;
});
    }

		// Update ugc
    function updateUgcEntry() {

			// Get data if any
      if (vm.ContentCreate.$valid) {
        dataUserGeneratedContent.updateUserGeneratedContentEntry(vm.ugc)
					.then(function (resp) {
  if (resp.status) {
    notificationHelper.success('User generated content type ' + vm.ugc.ContentCreate.name + ' has been successfully created.');
    $location.url('/user-generated-content/entries');
  } else {
    notificationHelper.error('Error: update user generated content entry failed' + resp.errors + '.');
  }
});
      }
    }


  }
})();
