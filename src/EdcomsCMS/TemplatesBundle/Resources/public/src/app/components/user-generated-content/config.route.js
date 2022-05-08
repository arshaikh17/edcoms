(function () {
  'use strict';

  angular
		.module('cms.userGeneratedContent')
		.run(appRun);


	/* @ngInject */
  function appRun(routeHelper) {
    routeHelper.configureRoutes(getRoutes());
  }

  function getRoutes() {
    const sidemenu  = {
      title: 'Manage User Generated Content',
      items: {
        0: {
          url: '/user-generated-content/entries',
          content: '<i class="m-tree__icon m-tree__icon--small material-icons">&#xE7FD;</i>Entries',
          context: 'userGeneratedContent.entries'
        },
        1: {
          url: '/user-generated-content/types',
          content: '<i class="m-tree__icon m-tree__icon--small material-icons">&#xE7FD;</i>Type',
          context: 'userGeneratedContent.types'
        }
      }
    };

    return [{
      url: '/user-generated-content/entries',
      config: {
        template: '<cms-ugc-entries></cms-ugc-entries>',
        title: 'User Generated Content entries',
        context: 'userGeneratedContent',
        settings: {
          nav: 5,
          content: '<i class="m-menu__icon material-icons">&#xE2C9;</i><label class="m-btn__label">User Generated Content</label>'
        },
        sidemenu: sidemenu
      }
    }, {
      url: '/user-generated-content/entry/update/:ugcEntryId',
      title: 'Configuration',
      config: {
        template: '<cms-update-ugc-entries></cms-update-ugc-entries>',
        title: 'Update User Generated Content entry',
        sidemenu: sidemenu,
        context: 'userGeneratedContent.entries'
      }
    },{
      url: '/user-generated-content/types',
      title: 'Configuration',
      config: {
        template: '<cms-ugc-types></cms-ugc-types>',
        title: 'User Generated Content types',
        context: 'userGeneratedContent.types',
        sidemenu: sidemenu
      }
    }, {
      url: '/user-generated-content/type/create',
      title: 'Configuration',
      config: {
        template: '<cms-update-ugc-type></cms-update-ugc-type>',
        title: 'Create User Generated Content type',
        sidemenu: sidemenu,
        context: 'userGeneratedContent.types'
      }
    }, {
      url: '/user-generated-content/type/update/:ugcTypeId',
      title: 'Configuration',
      config: {
        template: '<cms-update-ugc-type></cms-update-ugc-type>',
        title: 'Update User Generated Content type',
        sidemenu: sidemenu,
        context: 'userGeneratedContent.types'
      }
    }];
  }
})();
