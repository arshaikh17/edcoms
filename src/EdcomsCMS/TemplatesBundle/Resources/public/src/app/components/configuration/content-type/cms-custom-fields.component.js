(function () {
  'use strict';

  angular
    .module('cms.contentType')
    .component('cmsCustomFields', {
      bindings: {
        fields: '<',
      },
      templateUrl: '/bundles/edcomscmstemplates/src/app/components/configuration/content-type/cms-custom-fields.html',
      controller: CmsCustomFieldsController,
      controllerAs: 'vm',
    });

  function CmsCustomFieldsController(dataContentType, contentTypeFieldsHelper) {
    this.fields = this.fields || [];
    this.fieldsOptionValues = [];
    this.contentSelect = [];
    this.contentTypes = [];

    this.$onInit = function () {
      for (const i in this.fields) {
        this.getFieldExtraData(false, i);

        if (this.fields[i].fieldType === 'content_array') {
          this.getContentSelectionOptions(i);
        }
      }
    };

    this.addSubField = contentTypeFieldsHelper.addSubField;
    this.deleteField = contentTypeFieldsHelper.deleteField;

    /**
     * Display option correct information after field type has changed
     * @param {boolean} reset     Reset option value if true (do not do on load)
     * @param {integer} fieldId   Field id
     */
    this.getFieldExtraData = (reset, fieldId) => {
      let field = this.fields[fieldId];
      let textFields = ['text', 'textarea', 'richtextarea'];

      // Field type has changed, thefore we reset the field
      if (reset) {
        field.options = '';
      }

      if (field
        && ((field.fieldType !== 'content_array'
        && angular.isDefined(field.fieldType)
        && field.fieldType.indexOf('array') > -1)
        || textFields.indexOf(field.fieldType) > -1)) {
        this.fieldsOptionValues[fieldId] = {};

        this.fieldsOptionValues[fieldId].showOptionField = true;
        this.fieldsOptionValues[fieldId].optionFieldName = textFields.indexOf(field.fieldType) > -1 ? 'Character limit' : 'Options';
      }

      // Require list of content type only if needed and not retrived yet
      if (field.fieldType === 'content_array' && !this.contentTypes.length) {
        dataContentType.getContentTypes().then((data) => {
          this.contentTypes = data.content_types;
        });
      }
    };

    /**
     * Cancel edit action
     */
    this.cancelEdit = (index) => {
      const field = this.fields[index];

      // If no value is entered, there is only 3 keys (isEditable, parent, isSubfield)
      if (Object.keys(field).length === 3) {
        this.deleteField(this.fields, index);
      } else {
        field.isEditable = false;
      }
    };

    /**
     * Get linked content selection data from BE
     * Transform data to list content selection in tree format (object)
     * Data are in options property of fields and can be saved as
     * - a JSON Object (new way)
     * - a semi colon seperated list (old way handled for backward compatibility)
     *
     */
    this.getContentSelectionOptions = (fieldId) => {
      let field = this.fields[fieldId];
      const options = field.options;

      if (options.indexOf('{') > -1) {
        field.options = options ? angular.fromJson(options) : {};
      } else {
        // Backward compatibility for fields that are saves with an array and not JSON
        let array = options ? options.split(';') : [];

        for (let i in array) {
          array[i] = parseInt(array[i]);
        }
        field.options = {};
        field.options.contentType = array;
      }
    };
  }
})();
