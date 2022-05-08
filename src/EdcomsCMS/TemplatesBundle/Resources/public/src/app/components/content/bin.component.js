(function () {
  'use strict';

  angular
    .module('cms.content')
    .component("cmsBin", {
      templateUrl: '/bundles/edcomscmstemplates/src/app/components/content/bin.html',
      bindings: {},
      controller: BinController,
      controllerAs: 'vm'
    });

  /* @ngInject */

  function BinController($scope, dataStructure, notificationHelper, commonHelper, $rootScope) {
    let vm = this;
    vm.items = [];
    vm.title = 'Binned content items';
    vm.callFunction = commonHelper.callFunction;

    vm.restoreDeletedStructureItem = restoreDeletedStructureItem;

    vm.message = 'Hey, you can see content items that were deleted here, and restore them if you want to!';

    vm.itemsActions = {
      restore: {
        button: vm.restoreDeletedStructureItem,
        color: 'primary',
        access: 'content;delete'
      }
    };

    /* === Common == */
    vm.loading = true;

    vm.action = {
      labels: 'Return to list of contents',
      actions: {
        view: {
          link: '#contents',
          color: 'primary'
        }
      }
    };

    getDeletedStructureItems();

    function getDeletedStructureItems() {
      dataStructure.getDeletedStructureItems()
        .then(function (data) {
          vm.items = data;
          vm.items = getDisplayedName(vm.items);

          if (!vm.items.length) {
            vm.message = 'Hey there! There is no deleted content yet. Nothing to see here.';
          }

        }).finally(function () {
          vm.loading = false;
        });
    }

    function getDisplayedName(items) {
      for (let i in items) {
        const item = items[i];

        item.displayedName = item.title;
        if (angular.isDefined(item.content.contentType)) {
          item.displayedName += ` <span class="u-subheader">[ ${item.content.contentType.name} ]</span>`;
        }
      }
      return items;
    }

    function restoreDeletedStructureItem(item) {
      dataStructure.restoreDeletedStructureItem(item.id)
        .then(function (data) {
          vm.items = data;
          $rootScope.$broadcast('CmsItemsList:update', { items: getDisplayedName(data) });
          notificationHelper.success(item.title + ' has been succesfully restored.');
        });
    }

  }
})();
