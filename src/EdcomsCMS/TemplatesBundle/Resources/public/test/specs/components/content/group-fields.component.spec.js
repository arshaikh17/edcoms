describe('Group fields component', () => {
  let $compile;
  let $rootScope;
  let bindings;
  let contentFieldsHelper;
  let fileManagerHelper;
  let treeHelper;

  beforeEach(() => {
    module('common');
    module('cms.core');
    module('cms.data');
    module('cms.content');
    module('templates');

    inject(($injector) => {
      $compile = $injector.get('$compile');
      $rootScope = $injector.get('$rootScope');
      contentFieldsHelper = $injector.get('contentFieldsHelper');
      fileManagerHelper = $injector.get('fileManagerHelper');
      treeHelper = $injector.get('treeHelper');

      bindings = {
        fields: {},
        contentForm: {},
        form: {},
      };
    })
  });

  it('should have fields data', () => {
    const { controller } = createComponent(bindings);
    let fields = contentFieldsHelper.sortFieldsByOrder(bindings.fields);
    expect(controller.fields).toEqual(fields);
  });

  it('should have content form data', () => {
    const { controller } = createComponent(bindings);

    expect(controller.contentForm).toEqual(bindings.contentForm);
  });

  it('should have form data', () => {
    const { controller } = createComponent(bindings);

    expect(controller.form).toEqual(bindings.form);
  });

  it('should set open file manager function to file manager service function', () => {
    const { controller } = createComponent(bindings);

    expect(controller.openFileManager).toEqual(fileManagerHelper.openFileManager);
  });

  it('should set show tree children function to tree service function', () => {
    const { controller } = createComponent(bindings);

    expect(controller.showChildren).toEqual(treeHelper.showChildren);
  });

  it('should sort fields by order', () => {
    spyOn(contentFieldsHelper, 'sortFieldsByOrder');
    createComponent(bindings);

    expect(contentFieldsHelper.sortFieldsByOrder).toHaveBeenCalledWith(bindings.fields);
  });

  it('should set all fields options in format expected by UI', () => {
    spyOn(contentFieldsHelper, 'setFieldsOptions');

    createComponent(bindings);
    let fields = contentFieldsHelper.sortFieldsByOrder(bindings.fields);
    expect(contentFieldsHelper.setFieldsOptions).toHaveBeenCalledWith(fields);
  });

  it('should add field data to the field content', () => {
    const { controller } = createComponent(bindings);
    const field = {
      fieldType: 'text',
      name: 'test'
    };

    controller.contentForm[field.name] = [];
    controller.addField(field);
    expect(controller.contentForm[field.name]).toEqual(['']);
  });

  it(`should add field data and it's subfields for group to the field content`, () => {
    spyOn(contentFieldsHelper, 'addSubfields');
    const { controller } = createComponent(bindings);
    const field = {
      fieldType: 'group',
      name: 'test',
      subfields: [{
        fieldType: 'text',
        name: 'test_1',
      },
      {
        fieldType: 'text',
        name: 'test_2',
      }]
    };

    controller.contentForm[field.name] = [];
    controller.addField(field);
    expect(contentFieldsHelper.addSubfields).toHaveBeenCalledWith(field.subfields, controller.contentForm[field.name]);
  });

  it('should delete field data from the field content', () => {
    bindings.contentForm = {
      'test': [ '1', '2']
    };
    const { controller } = createComponent(bindings);

    const expectedContentForm = {
      'test': [ '1']
    };
    controller.deleteField({ name: 'test'}, 1);
    expect(controller.contentForm).toEqual(expectedContentForm);
  });

  function createComponent(scope) {
    let $scope = $rootScope.$new();
    $scope.fields = scope.fields;
    $scope.contentForm = scope.contentForm;
    $scope.form = scope.form;
    const element = $compile('<cms-group-fields fields="fields" content-form="contentForm" form="form"></cms-group-fields>')($scope);
    $scope.$digest();

    const controller = element.isolateScope().vm;

    return { element, controller };
  }

});
