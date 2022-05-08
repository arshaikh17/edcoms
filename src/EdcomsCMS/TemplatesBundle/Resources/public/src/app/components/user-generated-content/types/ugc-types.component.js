(function () {
  'use strict';

  angular.module('cms.userGeneratedContent')
		.component("cmsUgcTypes", {
  templateUrl: '/bundles/edcomscmstemplates/src/app/components/user-generated-content/types/ugcTypes.html',
  controller: UgcTypesController,
  controllerAs: 'vm',
  bindings: {}
});

	/* @ngInject */

	// UGC stand for User Generated Content
  function UgcTypesController(dataUserGeneratedContent) {
    let vm = this;
    vm.title = 'User generated content types';

    vm.action = {
      labels: 'Add',
      actions: {
        add: {
          tag: 'a',
          link: '#user-generated-content/type/create',
          color: 'primary'
        }
      }
    };

    getUgcTypes();

		// Get the ugc types list
    function getUgcTypes() {
      dataUserGeneratedContent.getUserGeneratedContentTypes().then(function (data) {
        vm.ugcTypes = data.user_generated_content_forms;
      });
    }

  }
})();
