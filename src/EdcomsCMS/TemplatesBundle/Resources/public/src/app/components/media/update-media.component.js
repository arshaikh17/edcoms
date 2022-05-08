(function () {
  'use strict';

  angular.module('cms.media')
		.component("cmsUpdateMedia", {
  bindings: {},
  templateUrl: '/bundles/edcomscmstemplates/src/app/components/media/update.html',
  controller: UpdateMediaController,
  controllerAs: 'vm'
});

	/* @ngInject */

  function UpdateMediaController($location, $routeParams, dataMedia, commonHelper, notificationHelper, treeHelper) {
    let vm = this;

    vm.media = {};

    vm.title = 'Create new directory';
    vm.back = commonHelper.back;
    vm.callFunction = commonHelper.callFunction;
    vm.updateMedia = updateMedia;
    vm.setMedia = setMedia;
    vm.showChildren = treeHelper.showChildren;

    vm.action = vm.topAction = {
      labels: 'Save or cancel',
      actions: {
        cancel: {
          button: vm.back,
          type: 'button',
          color: 'white'
        },
        submit: {
          button: vm.updateMedia,
          type: 'submit',
          color: 'primary'
        }
      }
    };


    initForm();
		// Create new user
    function initForm() {
      vm.actionTree = true;
      vm.media.path = undefined;

      treeHelper.getFromAPI('media').then(function (data) {
        vm.tree = data[0];
      });
    }

		// Update media
    function updateMedia() {
			// Get data if any
      if (vm.mediaForm.$valid) {
        dataMedia.createFolder(vm.media)
					.then(function (resp) {
  if (resp.status) {
    notificationHelper.success('Folder ' + vm.media.name + ' has been successfully created.');
    $location.url('/media');
  } else {
    notificationHelper.error('Error: create folder failed.' + resp.errors);
  }
});
      }
    }

    function setMedia(item) {
      vm.media.path = item.path;
    }
  }
})();
