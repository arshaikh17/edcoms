describe('Custom fields helper service', function () {
  let contentTypeFieldsHelper = null;
  let customFields = angular.copy(customFieldsMock);

  beforeEach(function () {
    module('cms.contentType');
    module('common');
    module('cms.core');
    module('cms.data');

    inject(function ($injector) {
      contentTypeFieldsHelper = $injector.get('contentTypeFieldsHelper');
    });
  });

  it('should set the list of fields', function () {
    contentTypeFieldsHelper.set(customFields);
    expect(contentTypeFieldsHelper.get()).toEqual(customFields);
  });

  it('should add a field', function () {
    contentTypeFieldsHelper.set(customFields);
    let orinalLength = customFields.length;
    contentTypeFieldsHelper.addField();

    expect(contentTypeFieldsHelper.get().length).toBe(orinalLength + 1);
  });

  it('should add a sub field', function () {
    contentTypeFieldsHelper.addSubField(customFields, 1);

    expect(customFields[1].subfields).toBeDefined();
  });

  it('should delete a field', function () {
    let orinalLength = customFields.length;
    let fields = contentTypeFieldsHelper.deleteField(customFields, 0);

    expect(fields.length).toBe(orinalLength - 1);
  });

  it('should restore delete fields if needed to be', function () {
    let orinalLength = customFields.length;
    const data = {
      constrained_ids: [1]
    };
    contentTypeFieldsHelper.set(customFields);
    contentTypeFieldsHelper.deleteField(customFields, 0);

    contentTypeFieldsHelper.restoreFields(data);

    expect(contentTypeFieldsHelper.get().length).toBe(orinalLength);
  });

  it('should clean fields from extra flags', function () {
    customFields[0].isEditable = true;
    contentTypeFieldsHelper.set(customFields);
    contentTypeFieldsHelper.prepareFields(contentTypeFieldsHelper.get());

    expect(customFields[0].isEditable).toBeUndefined();
  });

  it('should convert content array fields options property from JSON to String', function () {
    contentTypeFieldsHelper.set(customFields);
    
    const index = _.findIndex(customFields, (o) => {
      return o.fieldType === 'content_array';
    });
    customFields[index].options = angular.fromJson(customFields[index].options);
    const options = angular.copy(customFields[index].options);
    contentTypeFieldsHelper.prepareFields(contentTypeFieldsHelper.get());

    expect(customFields[index].options).toEqual(angular.toJson(options));
  });

});
