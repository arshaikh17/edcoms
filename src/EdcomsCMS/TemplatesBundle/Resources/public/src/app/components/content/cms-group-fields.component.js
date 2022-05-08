(function () {
  'use strict';

  angular
    .module('cms.content')
    .component('cmsGroupFields', {
      bindings: {
        fields: '=',
        contentForm: '=',
        form: '='
      },
      templateUrl: '/bundles/edcomscmstemplates/src/app/components/content/cms-group-fields.html',
      controllerAs: 'vm',
      controller: cmsGroupFieldsController,
    });

  /* @ngInject */
  function cmsGroupFieldsController(fileManagerHelper, treeHelper, $rootScope, contentFieldsHelper, customFields) {
    this.errorFields = [];
    this.relations = [];
    this.openFileManager = fileManagerHelper.openFileManager;

    this.$onInit = () => {
      this.showChildren = treeHelper.showChildren;

      // Update media fields with file value after file is selected in file manager
      $rootScope.$on('media:update', (event, args) => {
        const field =  this.fields[args.data.parentIndex];
        if (field) {
          const fieldName = field.name;
          this.contentForm[fieldName][args.data.index] = args.data.value;
        }
      });

      // Get fields ordered by order number specified
      this.fields = contentFieldsHelper.sortFieldsByOrder(this.fields);

      /**
       * Value from BE are sent as strings
       * We need to do some transformation depending on the field type for field options
       */
      this.relations = contentFieldsHelper.setFieldsOptions(this.fields);
    };

    /**
     * Options are sent and store as a string from BE
     * Options can be stored as a list sperated with ; or as a JSON
     * This update the selected options by the user for a field
     * @param {Number} id    field id
     * @param {Object|String|Number} option to be added or remove from the field value
     */
    this.toggleOptions = (parentId, id, option) => {
      option = (angular.isObject(option)) ? option.id : option;
      
      const field = this.fields[parentId];
      let fieldContent = this.contentForm[field.name][id];

      if (!fieldContent) {
        fieldContent = [];
      }

      const idx = fieldContent.indexOf(option.toString());

      if (idx > -1) {
        fieldContent.splice(idx, 1);
        return true;
      } else {
        // Settings custom errors according to fields constraints
        if (customFields.isContentList(field)
            && field.options[0].indexOf('{') > -1) {
          let options = angular.fromJson(field.options[0]);
          const limit = !options.isMultiple ? 1 : options.restriction;
          this.errorFields[parentId] = [];

          if (limit && fieldContent.length >= limit) {
            this.errorFields[parentId][id] = {
              message: `You can select only ${limit} item`,
            };

            if (limit > 1) {
              this.errorFields[parentId][id].message += 's';
            }

            return false;
          } else {
            delete this.errorFields[parentId][id];
          }
        }

        // if no error, add the item
        this.contentForm[field.name][id].push(option.toString());

        return true;
      }
    };

    /**
     * Adding a field to repeatable field data
     */
    this.addField = (field) => {
      if (customFields.isGroup(field)) {
        contentFieldsHelper.addSubfields(field.subfields, this.contentForm[field.name]);
      } else {    
        const data = customFields.isArray(field) ? [] : '';
        this.contentForm[field.name].push(data);
      }
    };

    /**
     * Deleting field from repeatable field data
     */
    this.deleteField = (field, index) => {
      if (this.contentForm[field.name].length > 1) {
        this.contentForm[field.name].splice(index, 1);
      } else {
        contentFieldsHelper.emptyContentFields(this.contentForm, field.name);
      }
    };
  }
})();
