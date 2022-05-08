(function () {
  'use strict';

  angular.module('cms.content')
		.component("cmsDeleteContent", {
  templateUrl: '/bundles/edcomscmstemplates/src/app/components/content/delete.html',
  controller: DeleteContentController,
  controllerAs: 'vm',
  bindings: {}
});

	/* @ngInject */

  function DeleteContentController($location, $routeParams, dataStructure, commonHelper, notificationHelper) {
    let vm = this;

    vm.deleteMode = 'single';
    vm.back = commonHelper.back;
    vm.callFunction = commonHelper.callFunction;
    vm.deleteContent = deleteContent;


    vm.action = {
      labels: 'Confirm or cancel',
      actions: {
        cancel: {
          button: vm.back,
          type: 'button',
          color: 'white'
        },
        submit: {
          button: vm.deleteContent,
          type: 'button',
          color: 'primary'
        }
      }
    };


    init();

    function init() {
      dataStructure.deleteCheckStructure($routeParams.structureId)
				.then(function (data) {
  if (data.status) {
    vm.contentHasChildren = data.children;
    vm.contentToDelete = data.title;
  } else {
    notificationHelper.error('Error: ' + data.error);
  }
});
    }

    function deleteContent() {
      dataStructure.deleteStructure($routeParams.structureId, vm.deleteMode)
				.then(function (resp) {
  if (resp.status) {
    notificationHelper.success('Content has been successfully deleted.');
    $location.url('/contents');
  } else {
    notificationHelper.error('Error: delete content failed');
  }
});
    }
  }
})();
