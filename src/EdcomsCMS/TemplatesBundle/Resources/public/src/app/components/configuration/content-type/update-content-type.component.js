(function () {
  'use strict';

  angular
    .module('cms.contentType')
    .component("cmsUpdateContentType", {
      templateUrl: '/bundles/edcomscmstemplates/src/app/components/configuration/content-type/update.html',
      bindings: {
        data: '<',
      },
      controllerAs: 'vm',
      controller: UpdateContentTypeController,
    });

  /* @ngInject */
  function UpdateContentTypeController($location, dataContentType, commonHelper, notificationHelper, contentTypeFieldsHelper) {
    this.contentType = {};
    this.template_files = [];

    this.back = commonHelper.back;
    this.callFunction = commonHelper.callFunction;
    this.addField = contentTypeFieldsHelper.addField;

    /**
    * Initialise form
    */
    this.$onInit = () => {
      const contentTypeData = this.data.data;

      this.required = contentTypeData.required;
      this.customfields = contentTypeData.content_type ? contentTypeData.content_type.custom_fields : [];
      contentTypeFieldsHelper.set(this.customfields);

      this.template_files = contentTypeData.content_type ? contentTypeData.content_type.template_files : [];
      this.title = contentTypeData.content_type ? `Update ${contentTypeData.content_type.name}` : 'Create new content type';

      this.contentType = contentTypeData.content_type || {};
      this.contentType._token = this.data.token;
    };

    /**
     * Update content type - transfor data to format
     * required by Symfony form and send to BE
     */
    this.updateContentType = () => {
      if (this.ContentTypeCreate.$valid) {
        this.contentType.custom_fields = contentTypeFieldsHelper.get();
        contentTypeFieldsHelper.prepareFields(this.contentType.custom_fields);

        dataContentType.updateContentType(this.contentType)
          .then((resp) => {
            if (angular.isDefined(resp)) {
              if (resp.status) {
                notificationHelper.success(`Content type ${this.contentType.name} has been successfully updated.`);
                $location.url('/configuration/content-type');
              } else {
                // Handling errors
                if (resp.errors === 'constaint_error') {
                  contentTypeFieldsHelper.restoreFields(resp.data);
                }
              }
            }
          });
      }
    };

    /**
    * Add an empty template
    */
    this.addTemplate = () =>{
      let newItemNo = this.template_files.length;
      this.template_files.push({
        'id': newItemNo
      });
    };

    /**
    * Delete a template
    */
    this.deleteTemplate = (index) => {
      this.template_files.splice(index, 1);
    };

    /**
    * Define list of actions user can do
    */
    this.action = {
      labels: 'Save or cancel',
      actions: {
        cancel: {
          button: this.back,
          type: 'button',
          color: 'white'
        },
        submit: {
          button: this.updateContentType,
          type: 'submit',
          color: 'primary'
        }
      }
    };
  }
})();
