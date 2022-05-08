(function () {
  'use strict';

  angular
    .module('common')
    .filter('firstNameStartsWith', firstNameStartsWith);
    
  function firstNameStartsWith() {
    return function (items, letter) {
      let filtered = [];
      let letterMatch = new RegExp(letter, 'i');
      for (let i = 0; i < items.length; i++) {
        let item = items[i];
        if (angular.isDefined(item.displayedName)) {
          if (letterMatch.test(item.displayedName.substring(0, 1))) {
            filtered.push(item);
          }
        }
      }
      return filtered;
    };
  }
})();
