(function () {
  'use strict';

  angular.module('cms.content')
    .component("cmsUpdateContent", {
      templateUrl: '/bundles/edcomscmstemplates/src/app/components/content/update.html',
      bindings: {
        tree: '<',
        structure: '<',
        contentTypes: '<'
      },
      controllerAs: 'vm',
      controller: updateContentController,
    });

  /* @ngInject */
  function updateContentController($location, $routeParams, dataContent, commonHelper, notificationHelper, treeHelper, contentFieldsHelper) {
    this.content = {};

    this.back = commonHelper.back;
    this.showChildren = treeHelper.showChildren;

    /**
     * Set the form content data if/when a content type is selected
     */
    this.setContentData = () => {
      const id = $routeParams.contentId || -1;

      // remove old custom fields when updating content type
      if (this.fields) {
        for (let i in this.fields) {
          delete this.content[this.fields[i].name];
        }
      }

      // Get content data from API
      dataContent
        .getContent(id, this.selectedContentType)
        .then((data) => {
          this.fields = data.fields || null;
          this.template_files = data.template_files;

          this.content.id = data.content.id || -1;
          this.content.status = data.content.status || 'published';
          this.content.title = data.content.title || '';
          this.content._token = data.token;
          this.selectedContentType = data.content.contentType ? data.content.contentType.id : this.selectedContentType;

          this.title = data.content.title ? `Update ${data.content.title}` : 'Create a new content';
          this.contentTypeEditable = data.content_type_editable || true;

          if (data.content.templateFile) {
            this.content.templateFile = data.content.templateFile.id;
          }

          const fieldsData = data.field_data;
          contentFieldsHelper.setFieldsData(this.fields, fieldsData, this.content);
          contentFieldsHelper.setFieldsToArray(this.fields, this.content);
        });
    };

    /**
     * Initialise form with data received from BE in the format
     * that will be expected to be sent back
     */
    this.$onInit = () => {
      this.actionTree = true;
      // As opposed to null, as null is for the home (Home has no parents)
      this.content.structure = undefined;

      if (!$routeParams.structureId) {
        // Reset selected content type
        this.selectedContentType = null;
        // Reset content
        this.content = {};
        this.content.structure = {};
        this.content.structure.id = -1;
        this.title = 'Create a new content';
        this.contentTypeEditable = true;
      } else {
        this.actionTree = false;
        this.content.structure = {};
        this.content.structure.id = this.structure.id;
        this.content.structure.link = this.structure.link;
        this.content.structure.priority = this.structure.priority;
        this.content.structure.rateable = this.structure.rateable;
        this.content.structure.visible = this.structure.visible;

        if (this.structure.parent) {
          this.content.structure.parent = this.structure.parent.id;
          this.selectedParent = this.structure.parent.title;
        } else if (this.structure.parent === null) {
          this.content.structure.parent = this.structure.parent;
        }
        this.setContentData();
      }
    };

    /**
     * Submit the form, format data in the format expected by the BE
     */
    this.updateContent = () => {
      if (!this.ContentCreate.$valid) {
        return;
      }
      
      if (!this.content.structure.link) {
        // Regexp [^\w] => replace everything which is not in this safe list (digits, alphabet and underscore)
        this.content.structure.link = (this.content.title).replace(/[^\w]/g, '-').toLowerCase();
      }

      // Copy content to avoid modifying model and the view when converting fields to string
      let content = angular.copy(this.content);
      contentFieldsHelper.setFieldsToString(this.fields, content);

      dataContent
        .updateContent(content, this.selectedContentType)
        .then((resp) => {
          if (resp && resp.status) {
            notificationHelper.success(`Content ${content.title} has been successfully created.`);
            $location.url('/contents');
          } else {
            notificationHelper.error(`Error: create content failed ${resp.errors}`);
          }
        })
        .catch((error) => {
          notificationHelper.error(`Error: create content failed ${error}`);
        });
    };

    /**
     * Set the parent content
     * @param {Object} parent  selected structure item
     */
    this.setContentParent = (parent) => {
      this.content.structure.parent = parent.id;
      const parentItem = treeHelper.getItemById(this.tree, parent.id);
      this.selectedParent = parentItem.title;
      this.actionTree = false;
      notificationHelper.success('Your content location has been changed, please save to confirm.');
    };

    /**
     * Toggle the view of tree displayed to select content parent
     */
    this.toggleActionTree = () => {
      this.actionTree = !this.actionTree;
    };
  }

})();
