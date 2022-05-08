(function () {
  'use strict';

  angular
		.module('cms.user')
		.run(appRun);


	/* @ngInject */
  function appRun(routeHelper) {
    routeHelper.configureRoutes(getRoutes());
  }

  function getRoutes() {
    const sidemenu = {
      title: 'User management',
      items: {
        0: {
          url: '/users',
          content: '<i class="m-tree__icon m-tree__icon--small material-icons">&#xE7FD;</i>Users',
          context: 'user'
        }
				//1 : {
				//    url: '/users/groups',
				//    content: '<i class="m-tree__icon m-tree__icon--small material-icons">&#xE7FD;</i>Groups'
				//}
      }
    };

    return [{
      url: '/users',
      config: {
        template: '<cms-users></cms-users>',
        title: 'Users',
        context: 'user',
        settings: {
          nav: 4,
          content: '<i class="m-menu__icon material-icons">&#xE7FB;</i> <label class="m-btn__label">Users</label>'
        },
        sidemenu: sidemenu
      }
    }, {
      url: '/users/view/:userId',
      config: {
        template: '<cms-view-user></cms-view-user>',
        title: 'View user',
        sidemenu: sidemenu,
        context: 'user'
      }
    }, {
      url: '/users/create',
      config: {
        template: '<cms-update-user></cms-update-user>',
        title: 'Create user',
        sidemenu: sidemenu,
        context: 'user'
      }
    }, {
      url: '/users/update/:userId',
      config: {
        template: '<cms-update-user></cms-update-user>',
        title: 'Update user',
        sidemenu: sidemenu,
        context: 'user'
      }
    }, {
      url: '/users/delete/:userId',
      config: {
        template: '<cms-delete-user></cms-delete-user>',
        title: 'Delete user',
        sidemenu: sidemenu,
        context: 'user'
      }
    }];
  }
})();
