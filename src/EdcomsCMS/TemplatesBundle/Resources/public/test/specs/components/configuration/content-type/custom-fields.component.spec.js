describe('Custom fields component', () => {
  let $componentController;
  let controller;
  let contentTypeFieldsHelper;
  let dataContentType;
  let $q;

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
        fields: customFieldsMock
      };

      controller = $componentController('cmsCustomFields',
        dependendies, bindings);
    })
  });

  it('should have a list of custom fields', () => {
    expect(controller.fields).toEqual(customFieldsMock);
  });

  it('should add a subfield', () => {
    const index = 1;
    const fields = controller.fields;
    const count = fields[index].subfields.length;

    controller.addSubField(fields, index);
    expect(fields[index].subfields.length).toBe(count + 1);
  });

  it('should delete a field', () => {
    const fields = controller.fields;
    const count = fields.length;

    controller.deleteField(fields, 0);
    expect(fields.length).toBe(count - 1);
  });

  it('should cancel edit action by making existing fields non editable', () => {
    customFieldsMock[0].isEditable = true;

    controller.cancelEdit(0);
    expect(customFieldsMock[0].isEditable).toBe(false);
  });

  it('should cancel edit action by deleting new field', () => {
    contentTypeFieldsHelper.set(controller.fields);
    contentTypeFieldsHelper.addField();
    const fields = contentTypeFieldsHelper.get();
    const index = fields.length - 1;
    controller.cancelEdit(index);

    expect(controller.fields[index]).toBeUndefined();
  });

  it ('should reset fields options value to an empty string', () => {
    const index = 0;
    controller.fields[index].options = 10;

    controller.getFieldExtraData(true, index);

    expect(controller.fields[index].options).toBe('');
  });

  it ('should set fields options value for field of type array of text', () => {
    spyOn(dataContentType, 'getContentTypes').and.returnValues($q.when());

    for (const i in controller.fields) {
      controller.getFieldExtraData(false, i);
    }
    // Filter by filed type which is using options
    const collection = controller.fields.filter((o) => {
      const filteringFieldTypes = ['text', 'textarea', 'richtextarea', 'checkbox_array'];

      return filteringFieldTypes.indexOf(o.fieldType) > -1;
    });

    const fieldsOptionValues = controller.fieldsOptionValues.filter((o) => {
      return angular.isDefined(o);
    });

    expect(fieldsOptionValues.length).toBe(collection.length);
  });

  it ('should transform options property from string into a usuable object ', () => {
    const index = _.findIndex(controller.fields, (o) => {
      return o.fieldType === 'content_array';
    });
    const options = controller.fields[index].options;

    controller.getContentSelectionOptions(index);
    expect(controller.fields[index].options).toEqual(angular.fromJson(options));
  });
});
