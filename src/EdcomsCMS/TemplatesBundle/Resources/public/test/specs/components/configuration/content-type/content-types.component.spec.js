describe('Content types component', () => {
  let $componentController;
  let controller;

  beforeEach(() => {
    module('cms.contentType', ($provide) => {
      $provide.value('dataContentType', {
        getContentTypes: () => {},
      });
      $provide.value('notificationHelper', {
        error: () => {},
      });
    });

    inject(($injector) => {
      $componentController = $injector.get('$componentController');
      const dependendies = {};
      const bindings = {
        contentTypes: contentTypesMock
      };

      controller = $componentController('cmsContentTypes',
        dependendies, bindings);
    })
  });

  it('should have a list content types', () => {
    expect(controller.contentTypes).toBeDefined(contentTypesMock);
  });

});
