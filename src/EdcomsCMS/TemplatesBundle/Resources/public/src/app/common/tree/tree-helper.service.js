(function () {
  'use strict';

  angular
    .module('common.tree')
    .factory('treeHelper', treeHelper);

  /* @ngInject */
  function treeHelper($q, $rootScope, dataStructure, dataMedia, $route, $location) {
    /**
     * seleted item and structure refer to the one user on the sidemenu and are unique at all time
     */
    let selectedItem = {};
    let selectedStructure = null;
    let currentType = 'structure';

    const service = {
      showChildren,
      getFromAPI,
      getSelectedItem,
      setSelectedItem,
      getSelectedStructure,
      setSelectedStructure,
      getCurrentType,
      setCurrentType,
      setTreeActiveState,
      getItemById,
    };

    return service;
    ///////////////

    /**
     * Call API to retrieve tree structure depending on the type
     */
    function getFromAPI(type) {
      let deferred = $q.defer();
      currentType = type ? type : undefined;

      if (currentType === 'structure') {
        deferred.resolve(dataStructure.getStructure().then((data) => {
          if (!Object.keys(selectedItem).length) {
            selectedItem = data;
            setSelectedStructure(data);
          }
          return data;
        }));
      } else if (currentType === 'media') {
        deferred.resolve(dataMedia.getAllMedia().then((data) => {
          selectedItem = data;
          setSelectedStructure(data);
          return data;
        }));
      }

      return deferred.promise;
    }

    /**
     * Show children for a given subtree depending on type
     */
    function showChildren(tree) {
      tree.isOpen = !tree.isOpen;
      if (currentType === 'media') {
        dataMedia.getAllMedia(tree.path).then(function (data) {
          tree.children = data;
        });
      }
    }

    /**
     * Get current selected tree structure
     */
    function getSelectedStructure() {
      return selectedStructure;
    }

    /**
     * Set current selected tree structure
     */
    function setSelectedStructure(newStructure) {
      selectedStructure = newStructure;
    }

    /**
     * Get current selected item
     */
    function getSelectedItem() {
      return selectedItem || null;
    }

    /**
     * Set selected item in sidemenu
     */
    function setSelectedItem(item) {
      selectedItem = item;
      $rootScope.$broadcast('tree.update');

      /** When in contents states, if we're in a substate and user select a tree item
       * redirect them to contents state to display selected item
       */
      if (angular.isDefined($route.current)) {
        const curentUrl = $route.current.$$route.originalPath;
        if (curentUrl.indexOf('content') > -1 && !curentUrl.indexOf('contents/') > -1) {
          $location.url('/contents');
        }
      }
    }

    /**
     * Get current selected tree type
     */
    function getCurrentType() {
      return currentType || null;
    }

    /**
     * Set current selected tree type
     */
    function setCurrentType(newType) {
      currentType = newType;
    }

    /**
     * Recursive function
     * This set if the tree node is active or not according to content types id
     * Ids is the list of items to be active
     * @param {Objecy} tree   tree structure
     * @param  {Array} ctIds  content type ids
     */

    function setTreeActiveState(tree, ctIds) {
      let idx = ctIds.indexOf(tree.content.contentType.id);

      if (idx === -1) {
        tree.unActive = true;
      }
      if (tree.children.length) {
        for (let key in tree.children) {
          setTreeActiveState(tree.children[key], ctIds);
        }
      }
    }

    /**
     * Find the Item title in the tree from provided id
     * @param {Object} treeItem tree structure
     * @param {Number} id 			selected item id
     */
    function getItemById(treeItem, id) {
      if (treeItem.id === id) {
        return treeItem;
      } else {
        let item;
        for (let key in treeItem.children) {
          item = getItemById(treeItem.children[key], id);
          if (item) {

            // return when item found
            return item;
          }
        }

      }
      return null;
    }

  }
})();
