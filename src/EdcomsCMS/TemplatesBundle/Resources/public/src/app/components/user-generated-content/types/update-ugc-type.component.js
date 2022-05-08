/**
 * Created by Aurore on 13/08/15.
 */
(function () {
  'use strict';

  angular.module('cms.userGeneratedContent')
		.component("cmsUpdateUgcType", {
  templateUrl: '/bundles/edcomscmstemplates/src/app/components/user-generated-content/types/update.html',
  controller: UpdateUgcTypeController,
  controllerAs: 'vm',
  bindings: {}
});

	/* @ngInject */

	// UGC stand for User Generated Content
  function UpdateUgcTypeController($location, $routeParams, dataUserGeneratedContent, commonHelper, notificationHelper) {
    let vm = this;
    vm.customFields = [];
    vm.contentEntries = [];

    vm.back = commonHelper.back;
    vm.callFunction = commonHelper.callFunction;
    vm.updateUgcType = updateUgcType;
    vm.checkboxGroupSelected = commonHelper.checkboxGroupSelected;
    vm.addField = addField;
    vm.addContentEntry = addContentEntry;

    vm.action = vm.topAction = {
      labels: 'Save or cancel',
      actions: {
        cancel: {
          button: vm.back,
          type: 'button',
          color: 'white'
        },
        submit: {
          button: vm.updateUgcType,
          type: 'submit',
          color: 'primary'
        }
      }
    };

    initForm();

		// Create new ugc type
    function initForm() {
      vm.ugcType = {};
      vm.ugcType.UserGeneratedContentFormCreate = {};

      let id = $routeParams.ugcTypeId || -1;

      dataUserGeneratedContent.getUserGeneratedContentType(id).then(function (data) {
        vm.required = data.data.required;
        vm.title = data.data.user_generated_content_form.name ? 'Update ' + data.data.user_generated_content_form.name : 'Create a new User Generated Content type';

        vm.contents = data.data.content;
        vm.structure = data.data.structure;
        vm.contentTypes = data.data.contentTypes;
        vm.groups = data.data.groups;

        vm.ugcType.UserGeneratedContentFormCreate = data.data.user_generated_content_form || {};
        vm.ugcType.UserGeneratedContentFormCreate.id = id;
        vm.ugcType.UserGeneratedContentFormCreate._token = data.token;
        if ($routeParams.ugcTypeId) {
          vm.contentEntries = data.data.user_generated_content_form.content;
        }

      });
    }

		// Create ugc type
    function updateUgcType() {
			// Get data if any
      if (vm.UserGeneratedContentFormCreate.$valid) {
        dataUserGeneratedContent.updateUserGeneratedContentType(vm.ugcType)
					.then(function (resp) {
  if (resp.status) {
    notificationHelper.success('User generated content type ' + vm.ugcType.UserGeneratedContentFormCreate.name + ' has been successfully created.');
    $location.url('/user-generated-content/types');
  } else {
    notificationHelper.error('Error: create user generated content type  failed ' + resp.errors);
  }
});
      }
    }


    function addField() {
      let newItemNo = vm.customFields.length;
      vm.customFields.push({
        'id': newItemNo
      });
    }

    function addContentEntry() {
      let newItemNo = vm.contentEntries.length;
      vm.contentEntries.push({
        'id': newItemNo
      });
    }

  }
})();
