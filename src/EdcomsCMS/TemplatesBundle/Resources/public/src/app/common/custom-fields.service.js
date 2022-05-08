(function () {
  'use strict';

  /**
  * 
  */
  angular
    .module('common')
    .factory('customFields', customFields);

  /* @ngInject */
  function customFields(FIELD_TYPES) {
    const service = {
      isArray,
      isGenericText,
      isGroup,
      isFileArray,
      isRichTextArea,
      isContentList,
      isMultipleSelection,
    };

    return service;

    function isArray(field) {
      return field.fieldType.indexOf('_array') > -1;
    }

    function isGenericText(field) {
      return [ FIELD_TYPES.TEXT, FIELD_TYPES.TEXTAREA, FIELD_TYPES.RICHTEXTAREA ].indexOf(field.fieldType) > -1;
    }

    function isGroup(field) {
      return field.fieldType === FIELD_TYPES.GROUP;
    }

    function isFileArray(field) {
      return field.fieldType === FIELD_TYPES.FILE_LIST;
    }

    function isRichTextArea(field) {
      return field.fieldType === FIELD_TYPES.RICHTEXTAREA;
    }

    function isContentList(field) {
      return field.fieldType === FIELD_TYPES.CONTENT_LIST;
    }

    function isMultipleSelection(field) {
      return [ FIELD_TYPES.CHECKBOX_LIST, FIELD_TYPES.CONTENT_LIST ].indexOf(field.fieldType) > -1;
    }

  }
})();
