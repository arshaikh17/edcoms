(function () {
  'use strict';

  angular.module('cms.userGeneratedContent')
		.component("cmsUgcEntries", {
  templateUrl: '/bundles/edcomscmstemplates/src/app/components/user-generated-content/entries/ugcEntries.html',
  controller: UgcEntriesController,
  controllerAs: 'vm',
  bindings: {}
});

	/* @ngInject */

	// UGC stand for User Generated Content
  function UgcEntriesController(dataUserGeneratedContent, notificationHelper, $location, permissionsHelper) {
    let vm = this;
    vm.title = 'User generated content entries';
    vm.getUgcEntries = getUgcEntries;
    vm.getUgcData = getUgcData;
    vm.setStatus = setStatus;

    getUgcTypes();


		// Get the ugc types list
    function getUgcTypes() {
      dataUserGeneratedContent.getUserGeneratedContentTypes().then(function (data) {
        vm.ugcTypes = data.user_generated_content_forms;
      });
    }

		// Get the ugc list
    function getUgcEntries(ugcTypeId) {
      vm.selectedUgcType = ugcTypeId;
      for (let i in vm.ugcTypes) {
        if (vm.ugcTypes[i].id == ugcTypeId) {
          vm.selectedForm = vm.ugcTypes[i].name;
        }
      }
      dataUserGeneratedContent.getUserGeneratedContentList(ugcTypeId).then(function (data) {
        vm.ugcEntries = data.user_generated_content_entries;
        vm.editable = data.editable;
      });
    }

    function getUgcData(ugcEntryId) {
      vm.selectedEntryId = ugcEntryId;
      for (let i in vm.ugcEntries) {
        if (vm.ugcEntries[i].id == ugcEntryId) {
          vm.selectedEntry = vm.ugcEntries[i].title;
        }
      }
      dataUserGeneratedContent.getUserGeneratedContentEntry(ugcEntryId).then(function (data) {
        vm.ugcData = data.data;
        for (let i in vm.ugcData) {
          vm.ugcData[i].field = vm.ugcData[i].field.replace(/_/g, " "); // replace underscores in received data by spaces
        }

      });
    }

    function setStatus(id, status, title, contentid) {
      if (!permissionsHelper.hasPermissions('userGeneratedContent.entries', 'edit')) {
        return;
      }

      if (contentid) {
        dataUserGeneratedContent.updateUserGeneratedContentEntryStatus(id, {
          'status': status
        }).then(function (resp) {
          if (resp.status) {
            notificationHelper.success('Status updated for ' + title + '.');
          } else {
            notificationHelper.error('Error: status update failed' + resp.errors + '.');
          }
        });
      } else {
        $location.url('/user-generated-content/entry/update/' + id);
      }
    }

  }
})();
