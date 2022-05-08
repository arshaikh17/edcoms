(function () {
  'use strict';

  angular
    .module('common.fileManager')
    .factory('fileManagerHelper', fileManagerHelper);

  /* @ngInject */
  function fileManagerHelper(ngDialog, $rootScope, $window) {
    let service = {
      openFileManager
    };

    return service;

    function openFileManager(parentID, inputID) {
      let dialog = null;

      dialog = ngDialog.open({
        template: '/bundles/edcomscmstemplates/src/app/common/filemanager/file-manager.html',
        className: 'ngdialog-theme-plain',
        /* @ngInject */
        controller: function RessourceManager($sce) {
          let vm = this;

          vm.src = $sce.trustAsResourceUrl(`/cms/filemanager/dialog.php?field_id=file${parentID}-${inputID}`);
        },
        controllerAs: 'vm',
        appendClassName: 'm-dialog',
        disableAnimation: true
      });

      // Using responsive filemanager callback to close window
      $window.responsive_filemanager_callback = function (field_id) {
        dialog.close();
        let ids = field_id.replace('file', '');

        let data = {
          value: angular.element('#' + field_id).val(),
          parentIndex: ids.split('-')[0],
          index: ids.split('-')[1],
        };
        $rootScope.$broadcast('media:update', {
          data: data
        });
      };
    }
  }
})();
