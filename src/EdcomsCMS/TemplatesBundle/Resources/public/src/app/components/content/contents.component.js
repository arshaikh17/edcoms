/**
 * Created by Aurore on 13/08/15.
 */
(function () {
  'use strict';

  angular.module('cms.content')
    .component("cmsContents", {
      bindings: {},
      templateUrl: '/bundles/edcomscmstemplates/src/app/components/content/contents.html',
      controller: ContentsController,
      controllerAs: 'vm'
    });

  /* @ngInject */

  function ContentsController($rootScope, dataContent, treeHelper, dataStructure, notificationHelper) {
    let vm = this;

    /* === Common == */
    vm.loading = true;
    vm.selectedItems = [];
    vm.toggleItem = toggleItem;
    vm.toggleAllItems = toggleAllItems;
    vm.updateStatus = updateStatus;
    vm.selectAllText = 'Select all';

    $rootScope.$on('tree.update', function () {
      getContents();
    });

    getContents();

    function getContents() {
      let item = treeHelper.getSelectedItem() || null;

      dataStructure.getStructure(item.id).then(function (data) {
        vm.structure = data;
        vm.children = vm.structure.children;
        vm.hasParent = vm.structure.parent !== null;
        if (angular.isDefined(data.id)) {
          dataContent.getContents(data.id).then(function (data) {
            // Most recent content version is first
            vm.content_item = data.data[0]; 
          });
        }

      }).finally(function () {
        //Set the loading is false.
        vm.loading = false;
      });
    }

    function toggleItem(id) {
      let idx = vm.selectedItems.indexOf(id);

      // is currently selected
      if (idx > -1) {
        vm.selectedItems.splice(idx, 1);
      }
      // is newly selected
      else {
        vm.selectedItems.push(id);
      }
    }

    function toggleAllItems(isSelected) {
      // Add all items
      if (isSelected) {
        let child = {};

        for (let i in vm.children) {
          child = vm.children[i];
          vm.selectedItems.push(child.content.id);
        }
        vm.selectAllText = 'Deselect all';
      }
      // Reset selected items
      else {
        vm.selectedItems = [];
        vm.selectAllText = 'Select all';
      }
    }

    function updateStatus(status) {
      let data = {
        value: status
      };

      for (let i in vm.selectedItems) {
        dataContent.updateContentStatus(vm.selectedItems[i], data).then(function (resp) {
          if (resp.status) {
            getContents();
            vm.selectedItems = [];
            notificationHelper.success('Status successfully updated to ' + status + '.');
          }
        });
      }
    }
  }
})();
