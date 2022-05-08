(function () {
  'use strict';

  angular.module('cms.media')
    .component("cmsMedia", {
      bindings: {},
      templateUrl: '/bundles/edcomscmstemplates/src/app/components/media/media.html',
      controller: MediaController,
      controllerAs: 'vm',
    });

  /* @ngInject */

  function MediaController(dataMedia, $rootScope, treeHelper, $location, notificationHelper, $timeout) {
    let vm = this;

    vm.media = [];
    vm.title = 'Media';
    vm.link = 'http://' + $location.host();
    vm.selectedFiles = [];
    vm.copiedFiled = false;

    vm.copyFiles = copyFiles;
    vm.pasteFiles = pasteFiles;
    vm.toggleFile = toggleFile;


    vm.loading = true;

    $rootScope.$on('tree.update', function () {
      getMedia();
    });

    getMedia();

    // Get media list
    function getMedia() {
      dataMedia.getAllMedia().then((data) => {
        const selected = treeHelper.getSelectedItem();
        vm.mediaItem = selected ? selected : data;

        dataMedia.getAllMedia(vm.mediaItem.path).then((data) => {
          vm.media = data;
        });
      })
      .finally(function () {
        //Set the loading is false.
        vm.loading = false;
        $timeout(function () {
          uploadFiles();
          angular.element('.uploadifive-queue').hide();
        }, 1000);
      });
    }

    function uploadFiles() {
      let classes = 'm-btn m-btn--sq m-btn--primary u-float-left',
        buttonText = '<i class="material-icons">&#xE2C6;</i>',
        fileType = 'application/msword|application/vnd.openxmlformats-officedocument.wordprocessingml.document|application/vnd.ms-excel|application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|application/vnd.ms-powerpoint|application/vnd.openxmlformats-officedocument.presentationml.presentation|application/pdf|image/jpeg|image/jpg|image/png|image/gif|image/bmp|video/mp4|video/x-m4v|video/quicktime|audio/mp4',
        width = '48px',
        height = '48px',
        fileSizeLimit = '100MB',
        script = '/cms/media/upload/admin/' + vm.mediaItem.path;

      angular.element('#uploadify_button').uploadifive({
        'auto': true,
        'buttonClass': classes,
        'buttonText': buttonText,
        'width': width,
        'height': height,
        'fileSizeLimit': fileSizeLimit,
        'uploadScript': script,
        'fileType': fileType,
        'onAddQueueItem': function () {},
        'onUploadComplete': function (file) {
          notificationHelper.success(file.name + 'successfully uploaded!');
          getMedia();
        },
        'onError': function () {
        },
        'onFallback': function () {
          angular.element('#uploadify_button').uploadify({
            'swf': '/bundles/app/assets/flash/uploadify.swf',
            'uploader': script,
            'buttonClass': classes,
            'buttonText': buttonText,
            'width': width,
            'height': height,
            'fileSizeLimit': fileSizeLimit,
            'removeCompleted': false,
            'fileType': fileType,
            'onUploadSuccess': function (file) {
              notificationHelper.success(file.name + 'successfully uploaded!');
              getMedia();
            },
            'onError': function () {}
          });
        }
      });

    }

    function copyFiles() {
      dataMedia.copyFiles(vm.selectedFiles).then(function (resp) {
        if (resp.status) {
          vm.copiedFiled = true;
          notificationHelper.success('Files have been successfully copied.');
        } else {
          notificationHelper.error('Error: copying files failed' + resp.errors + '.');
        }
      });
    }

    function pasteFiles() {
      dataMedia.pasteFiles(vm.mediaItem.path).then(function (resp) {
        if (resp.status) {
          notificationHelper.success('Files have been successfully pasted.');
        } else {
          notificationHelper.error('Error: pasting files failed' + resp.errors + '.');
        }
      });
    }

    function toggleFile(file) {
      let idx = vm.selectedFiles.indexOf(file.path);

      if (idx > -1) {
        vm.selectedFiles.splice(idx, 1);
      } else {
        vm.selectedFiles.push(file.path);
      }
    }

  }
})();
