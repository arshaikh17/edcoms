describe('Update content type component', () => {
  let $componentController;
  let controller;
  let dataContentType;
  let $q;
  let contentTypeFieldsHelper;

  beforeEach(() => {
    module('cms.contentType');
    module('common');
    module('cms.core');
    module('cms.data');

    inject(($injector) => {
      $q = $injector.get('$q');
      contentTypeFieldsHelper = $injector.get('contentTypeFieldsHelper');
      dataContentType = $injector.get('dataContentType');
      $componentController = $injector.get('$componentController');

      const dependendies = {};
      const bindings = {
        data: contentTypeMock
      };

      controller = $componentController('cmsUpdateContentType',
        dependendies, bindings);
    });
  });

  it('should have a list of data to update', () => {
    expect(controller.data).toEqual(contentTypeMock);
  });

  it('should add a template', () => {
    const count = controller.template_files.length;
    controller.addTemplate();

    expect(controller.template_files.length).toBe(count + 1);
  });

  it('should delete a template', () => {
    controller.addTemplate();
    const count = controller.template_files.length;

    controller.deleteTemplate(0);

    expect(controller.template_files.length).toBe(count - 1);
  });

  it('should send updated data to BE', () => {
    spyOn(dataContentType, 'updateContentType').and.returnValues($q.when());

    controller.ContentTypeCreate = {
      $valid: true,
    };
    controller.updateContentType();
    expect(dataContentType.updateContentType).toHaveBeenCalled();
  });

  it('should fetch the custom fields data on update', () => {
    spyOn(dataContentType, 'updateContentType').and.returnValues($q.when());
    spyOn(contentTypeFieldsHelper, 'get');

    controller.ContentTypeCreate = {
      $valid: true,
    };    controller.updateContentType();
    expect(contentTypeFieldsHelper.get).toHaveBeenCalled();
  });

  it('should clean up custom fields data on update', () => {
    spyOn(dataContentType, 'updateContentType').and.returnValues($q.when());
    spyOn(contentTypeFieldsHelper, 'prepareFields');

    controller.ContentTypeCreate = {
      $valid: true,
    };
    controller.updateContentType();
    expect(contentTypeFieldsHelper.prepareFields).toHaveBeenCalled();
  });

});
