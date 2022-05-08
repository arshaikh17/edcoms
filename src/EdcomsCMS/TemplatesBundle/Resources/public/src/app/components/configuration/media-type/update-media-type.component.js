(function () {
  'use strict';

  angular.module('cms.mediaType')
		.component("cmsUpdateMediaType", {
  templateUrl: '/bundles/edcomscmstemplates/src/app/components/configuration/media-type/update.html',
  bindings: {},
  controllerAs: 'vm',
  controller: UpdateMediaTypeController
});

	/* @ngInject */
  function UpdateMediaTypeController($location, $routeParams, dataMediaType, commonHelper, notificationHelper) {
    let vm = this;

    vm.mediatype = {};

    vm.back = commonHelper.back;
    vm.callFunction = commonHelper.callFunction;
    vm.updateMediaType = updateMediaType;

    vm.action = {
      labels: 'Save or cancel',
      actions: {
        cancel: {
          button: vm.back,
          type: 'button',
          color: 'white'
        },
        submit: {
          button: vm.updateMediaType,
          type: 'submit',
          color: 'primary'
        }
      }
    };

    initForm();

    function initForm() {
      let id = $routeParams.mediaId || -1;

      dataMediaType.getMediaType(id).then(function (data) {
        vm.required = data.required;
        vm.title = data.data.media_type.id ? 'Update ' + data.data.media_type.target + '-' + data.data.media_type.filetype : 'Create a media type';

        vm.mimetypes = data.data.mimetype;
        vm.mediatype.MediaTypeCreate = data.data.media_type || {};
        vm.mediatype.MediaTypeCreate.id = id;
        vm.mediatype.MediaTypeCreate._token = data.token;
      });
    }

    function updateMediaType() {
      if (vm.MediaTypeCreate.$valid) {
        dataMediaType.updateMediaType(vm.mediatype).then(function (resp) {
          let action = $routeParams.mediaId ? 'update' : 'create';
          if (resp.status) {
            notificationHelper.success('Media type ' + vm.mediatype.MediaTypeCreate.target + '-' + vm.mediatype.MediaTypeCreate.filetype + ' has been successfully ' + action + 'd.');
            $location.url('/configuration/settings');
          } else {
            notificationHelper.error('Error: ' + action + ' media type failed' + resp.data.errors + '.');
          }
        });
      }
    }

  }
})();
