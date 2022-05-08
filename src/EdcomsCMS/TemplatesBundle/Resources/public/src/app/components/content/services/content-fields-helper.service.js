(function () {
  'use strict';

  /**
  * Provide functions helper to manipulate fields
  */
  angular
    .module('cms.content')
    .factory('contentFieldsHelper', contentFieldsHelper);

  /* @ngInject */
  function contentFieldsHelper(customFields, treeHelper) {
    const service = {
      setFieldsToArray,
      setFieldsToString,
      setFieldsData,
      sortFieldsByOrder,
      addSubfields,
      setFieldsOptions,
      emptyContentFields,
    };

    return service;

    /**
     * Set fields and subfields data to array recursively
     * 
     * @param {Object} fields 
     * @param {Object} content 
     */
    function setFieldsToArray(fields, content) {
      for (let i in fields) {
        let field = fields[i];
        let fieldContent = content[field.name];

        // Transform fields of type array from a string to an Array
        if (customFields.isFileArray(field)
          && angular.isDefined(fieldContent)
          && fieldContent !== '') {
          if (field.repeatable) {
            for (let idx in fieldContent) {
              content[field.name][idx] = angular.fromJson(fieldContent[idx]);
            }
          } else {
            content[field.name] = angular.fromJson(fieldContent);
          }
        }

        // Group
        if (angular.isDefined(field.subfields) && field.subfields.length) {
          if (!field.repeatable) {
            content[field.name] = angular.isDefined(fieldContent) ? [fieldContent] : [''];
            setFieldsToArray(field.subfields, content[field.name]);
          }
          else {
            // Reapeatable group
            for (let idx in fieldContent) {
              setFieldsToArray(field.subfields, content[field.name][idx]);
            }
          }
        } else {
          if (!fieldContent || !field.repeatable) {
            if (customFields.isMultipleSelection(field)) {
              content[field.name] = (fieldContent) ? [fieldContent] : [[]];
            } else {
              // Group content are in an array of subfields 
              // We need to go through each one of them anf transform into an array
              if (angular.isArray(content)) {
                for (let idx in content) {
                  if (!angular.isArray(content[idx][field.name])) {
                    content[idx][field.name] = [content[idx][field.name]];
                  }
                }
              }
              else {
                content[field.name] = angular.isDefined(fieldContent) ? [fieldContent] : [''];
              }
            }
          }
        }

        if (customFields.isMultipleSelection(field) && fieldContent) {
          if (field.repeatable) {
            for (let idx in fieldContent) {
              content[field.name][idx] = (fieldContent[idx].length) ? fieldContent[idx].split(',') : [];
            }
          } else {
            content[field.name] = (fieldContent.length) ? [fieldContent.split(',')] : [];
          }
        }
      }
    }


    /**
     * Convert each field from array to string
     * 
     * @param {any} field 
     * @param {any} content 
     */
    function convertFieldToString(field, content) {
      if (!field.repeatable) {
        content[field.name] = (angular.isDefined(content[field.name][0])) ? content[field.name][0] : '';
      }

      let fieldContent = content[field.name];

      // Change array fields back to string for post
      if (customFields.isFileArray(field)) {
        if (field.repeatable) {
          for (let idx in fieldContent) {
            content[field.name][idx] = angular.toJson(fieldContent[idx]);
          }
        }
        else {
          content[field.name] = angular.toJson(fieldContent);
        }
      }

      // Add CSS scoping class to user generated content
      // For rich text content, we're adding a scope class on submission if not added yet
      // This allow the Front Office to deal with user generated style
      if (customFields.isRichTextArea(field)) {
        if (angular.isDefined(fieldContent) && (fieldContent).indexOf('s-ugc') === -1) {
          if (field.repeatable) {
            for (let idx in fieldContent) {
              content[field.name][idx] = `<div class="s-ugc">${fieldContent[idx]}</div>`;
            }
          }
          else {
            content[field.name] = `<div class="s-ugc">${fieldContent}</div>`;
          }
        }
      }

      if (customFields.isMultipleSelection(field)
        && !customFields.isGroup(field)
        && angular.isArray(fieldContent)) {

        if (field.repeatable) {
          for (let idx in fieldContent) {
            content[field.name][idx] = fieldContent[idx].join(',');
          }
        } else {
          content[field.name] = fieldContent.join(',');
        }
      }
    }

    /**
     * BE expect only the field value and not an array if field is not repeatable
     * Set fields and subfields data to string if needed recursively
     * 
     * @param {Object} fields 
     * @param {Object} content 
     */
    function setFieldsToString(fields, content) {
      for (let i in fields) {
        let field = fields[i];

        // This field is a subfields of a group then
        // We need to go through the content array one by one to replace content with strings
        if (field.parent && angular.isArray(content)) {
          for (let idx in content) {
            convertFieldToString(field, content[idx]);
          }
        } else {
          convertFieldToString(field, content);
        }

        if (angular.isDefined(field.subfields) && field.subfields.length) {
          setFieldsToString(field.subfields, content[field.name]);
        }
      }
    }

    /**
     * Set fields and subfields data recursively, 
     * transforming string into the needed object by FE
     * 
     * @param {Object} fields 
     * @param {Object} data 
     * @param {Object} content 
     */
    function setFieldsData(fields, fieldsData, content) {
      for (let i in fields) {
        const field = fields[i];
        content[field.name] = (angular.isDefined(fieldsData)) ? fieldsData[field.id] : '';

        // set to default value on creation only
        const hasNoFieldData = (angular.isUndefined(fieldsData) || !Object.keys(fieldsData).length);
        if (hasNoFieldData) {
          content[field.name] = field.defaultValue || '';
        }

        // Group
        if (angular.isDefined(field.subfields) && field.subfields.length) {
          const subfieldsData = fieldsData[field.id];
          content[field.name] = {};
          // Group and repeatable
          if (field.repeatable) {
            content[field.name] = [];
            if (subfieldsData) {
              for (let idx in subfieldsData) {
                content[field.name][idx] = {};
                setFieldsData(field.subfields, subfieldsData[idx], content[field.name][idx]);
              }
            }
            else {
              content[field.name][0] = {};
              setFieldsData(field.subfields, subfieldsData, content[field.name][0]);
            }
          } else {
            setFieldsData(field.subfields, subfieldsData, content[field.name]);
          }
        }
      }
    }


    /**
     * Sort order of fields recursively
     * 
     * @param {Object} fields 
     * @returns fields ordered
     */
    function sortFieldsByOrder(fields) {
      fields = _.sortBy(fields, ['order']);

      for (let i in fields) {
        let field = fields[i];

        if (field.subfields) {
          field.subfields = sortFieldsByOrder(field.subfields);
        }
      }
      return fields;
    }

    /**
     * Adding emtpy subfields to content for group and repeatable field
     * 
     * @param {any} subfields List of subfields for that field
     * @param {any} content Current content 
     */
    function addSubfields(subfields, content) {
      let newFieldContent = {};

      for (let i in subfields) {
        const subfield = subfields[i];

        newFieldContent[subfield.name] = customFields.isArray(subfield) ? [[]] : [''];
      }
      content.push(newFieldContent);
    }


    /**
     * All fields value are sent as string from BE
     * Converting options field for display depending on the field type
     * 
     * @param {any} fields 
     * @returns array of relations for fields of content list type
     */
    function setFieldsOptions(fields) {
      let relations = [];

      for (let i in fields) {
        const field = fields[i];
        if (field.options && !angular.isArray(field.options)) {
          field.options = field.options.split(';');
        }

        // Get all the tree for all content_array in an array
        if (customFields.isContentList(field)) {
          let options = angular.fromJson(field.options[0]);
          let tree = treeHelper.getSelectedStructure();

          // Clone the original tree
          relations[i] = angular.copy(tree);
          let ids = options.contentType;

          // Only set the active set if ids exist
          if (ids.length) {
            for (let idx in ids) {
              ids[idx] = parseInt(ids[idx]);
            }
            treeHelper.setTreeActiveState(relations[i], ids);
          }
        }
      }

      return relations;
    }

    function emptyContentFields(content, fieldName) {
      let data = content[fieldName][0];

      if (angular.isArray(data) || !angular.isObject(data)) {
        content[fieldName] = [''];
      } else {
        for(let prop in data) {
          emptyContentFields(content[fieldName][0], prop);
        }
      }
    }

  }
})();
