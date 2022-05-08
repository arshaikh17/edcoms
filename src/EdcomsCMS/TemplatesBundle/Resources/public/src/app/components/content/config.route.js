(function () {
  'use strict';

  angular
		.module('cms.content')
		.run(appRun);

	/* @ngInject */
  function appRun(routeHelper) {
    routeHelper.configureRoutes(getRoutes());
  }

  function getRoutes() {
    const sidemenu = {
      title: 'Manage your content',
      type: 'structure'
    };

    return [{
      url: '/contents',
      config: {
        template: '<cms-contents><cms-contents>',
        title: 'Contents',
        context: 'content',
        settings: {
          nav: 2,
          content: '<i class="m-menu__icon material-icons">&#xE051;</i><label class="m-btn__label">Content</label>'
        },
        sidemenu: sidemenu
      }
    }, {
      url: '/content/create',
      config: {
        template: '<cms-update-content tree="$resolve.tree" structure="$resolve.structure" content-types="$resolve.contentTypes"></cms-update-content>',
        title: 'Create content',
        sidemenu: sidemenu,
        context: 'content',
        resolve: {
          /* @ngInject */
          tree(treeHelper) {
            return treeHelper.getFromAPI('structure').then(tree => tree);
          },
          /* @ngInject */
          contentTypes(dataContent) {
            return dataContent.getContentTypes().then((data) => data.content_types);
          }
        }
      }
    }, {
      url: '/content/update/:contentId/:structureId',
      config: {
        template: '<cms-update-content tree="$resolve.tree" structure="$resolve.structure" content-types="$resolve.contentTypes"></cms-update-content>',
        title: 'Update content',
        sidemenu: sidemenu,
        context: 'content',
        resolve: {
          /* @ngInject */
          tree(treeHelper) {
            return treeHelper.getFromAPI('structure').then(tree => tree);
          },
          /* @ngInject */
          structure(dataStructure, $route) {
            return dataStructure.getStructure($route.current.params.structureId)
            .then(structure => structure);
          },
          /* @ngInject */
          contentTypes(dataContent) {
            return dataContent.getContentTypes().then((data) => data.content_types);
          }
        }
      }
    }, {
      url: '/content/delete/:structureId',
      config: {
        template: '<cms-delete-content></cms-delete-content>',
        title: 'Delete content',
        sidemenu: sidemenu,
        context: 'content'
      }
    }, {
      url: '/contents/bin',
      config: {
        template: '<cms-bin></cms-bin>',
        sidemenu: sidemenu,
        title: 'Bin',
        context: 'content'
      }
    }];
  }
})();
