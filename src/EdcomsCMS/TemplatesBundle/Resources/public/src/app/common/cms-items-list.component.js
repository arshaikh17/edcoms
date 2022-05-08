(function () {
  'use strict';

  angular
    .module('common')
    .component('cmsItemsList', {
      bindings: {
        items: '=',
        actions: '=',
        call: '&'
      },
      controllerAs: 'vm',
      templateUrl: '/bundles/edcomscmstemplates/src/app/common/cms-items-list.html',
      controller: function ($filter, $rootScope) {
        this.alphabet = "abcdefghijklmnopqrstuvwxyz".split("");
        this.alphabetSorted = "abcdefghijklmnopqrstuvwxyz".split("");
        this.predicate = 'alphabetical';
        this.filterLetter = '';
        this.filteredItems = [];
        this.search = "";
        this.letterList = [];

        $rootScope.$on('CmsItemsList:update', (event, data) => {
          this.items = data.items;
          this.getFilteredItems();
        });

        this.$onInit = () => {
          this.getFilteredItems();
        };

        /**
         * Filter items using search and filter selected by user
         */
        this.getFilteredItems = () => {
          for (let i in this.items) {
            this.letterList.push(this.items[i].displayedName.substring(0, 1).toLowerCase());
          }

          this.filteredItems = $filter('filter')(this.items, this.search);
          this.filteredItems = $filter('firstNameStartsWith')(this.filteredItems, this.filterLetter);
        };


        /**
         * Order items depending on user selection: predicate
         */
        this.order = () => {
          if (this.predicate === 'alphabeticalReverse') {
            //reverse the alphabet
            this.alphabetSorted = this.alphabetSorted.reverse();
          } else if (this.predicate === 'alphabetical') {
            if (this.alphabetSorted[0] === 'z') {
              this.alphabetSorted = this.alphabetSorted.reverse();
            }
          }
        };

        /**
         * Set selected filter
         */
        this.setFilter = (string) => {
          this.filterLetter = string;
          this.getFilteredItems();
        };
      }
    });
})();
