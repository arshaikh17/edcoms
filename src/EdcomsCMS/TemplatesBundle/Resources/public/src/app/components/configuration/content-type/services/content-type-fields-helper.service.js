(function () {
  'use strict';

  /**
  * Service to hold list of custom fields for configuration area
  * Provide functions helper to manipulate fields
  */
  angular
		.module('cms.contentType')
		.factory('contentTypeFieldsHelper', contentTypeFieldsHelper);

	/* @ngInject */
  function contentTypeFieldsHelper(notificationHelper, customFields) {
    let fields = [];
    let deletedFields = [];

    const service = {
      get,
      set,
      addField,
      addSubField,
      deleteField,
      prepareFields,
      restoreFields
    };

    return service;

    function get() {
      return fields;
    }

    function set(newFields) {
      fields = newFields;
      deletedFields = [];
    }

    function addField() {

      fields.push({
        isEditable: true,
        isSubfield: false,
        parent: null,
      });
    }

    function addSubField(fields, index) {
      if (!fields[index].subfields) {
        fields[index].subfields = [];
      }

      let parent = angular.copy(fields[index]);
      delete parent.subfields;

      fields[index].subfields.push({
        isEditable: true,
        isSubfield: true,
        parent: parent.id,
      });

      return fields;
    }

    function deleteField(fields, index) {
      const fieldToDelete = fields[index];
			// Save field to delete
      if (angular.isDefined(fieldToDelete.id)) {
        deletedFields[fieldToDelete.id] = fieldToDelete;
      }

			// Delete it from the list of fields
      fields.splice(index, 1);

      return fields;
    }

		/**
		 * Restoring delete fields which can not be removed
		 */
    function restoreFields(data) {
      let fieldsList = '';
      let field = null;
      let errorMessage = '';


      for (let i = 0, l = data.constrained_ids.length; i < l; i++) {
        field = deletedFields[data.constrained_ids[i]];
        fieldsList += field.label + ' ';

        fields.push(field);

        delete data.constrained_ids[i];
      }

      errorMessage = `Field  ${fieldsList} can not be removed as it is being used. It has been added back to the list of fields.`;

			// Handling pural state
      if (deletedFields.length > 1) {
        errorMessage.replace('Field', 'Fields').replace('it is', 'they are').replace('It has', 'They have');
      }

      notificationHelper.error(errorMessage);
    }

    /**
    * Prepare custom fields to be sent to BE
    * Clean FE flags before saving data
    * Save content array fields options property from JSON to String
    */
    function prepareFields(fields) {
      for (let i in fields) {
        const field = fields[i];

        delete field.isEditable;
        delete field.isSubfield;

        if (customFields.isContentList(field)) {
          field.options = angular.toJson(field.options);
        }

        if (field.subfields) {
          prepareFields(field.subfields);
        }
      }
    }

  }
})();
