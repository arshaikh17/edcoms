(function () {
  'use strict';

  /**
  * Constant to hold all custom fields type
  */
  angular
    .module('common')
    .constant('FIELD_TYPES', {
      'TEXT': 'text',
      'TEXTAREA': 'textarea',
      'RICHTEXTAREA': 'richtextarea',
      'NUMBER': 'number',
      'DATE': 'date',
      'IMAGE': 'image',
      'FILE': 'file',
      'CHECKBOX' : 'checkbox',
      'CONTENT_LIST': 'content_array',
      'CHECKBOX_LIST' : 'checkbox_array',
      'RADIO_LIST' : 'radio_array',
      'FILE_LIST' : 'file_array',
      'GROUP': 'group'
    });
})();
