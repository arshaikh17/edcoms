describe('Update content component', () => {
  let $compile;
  let $rootScope;
  let bindings;
  let contentFieldsHelper;
  let dataContent;
  let notificationHelper;
  let $q;

  beforeEach(() => {
    module('common');
    module('cms.core');
    module('cms.data');
    module('cms.content');
    module('templates');

    inject(($injector) => {
      $compile = $injector.get('$compile');
      $rootScope = $injector.get('$rootScope');
      contentFieldsHelper = $injector.get('contentFieldsHelper');
      dataContent = $injector.get('dataContent');
      notificationHelper = $injector.get('notificationHelper');
      $q = $injector.get('$q');

      bindings = {
        tree: {
          id: 1,
          title: 'Home',
        },
        structure: {},
        contentTypes: {},
      };
    })
  });

  it('should have tree data', () => {
    const { controller } = createComponent(bindings);

    expect(controller.tree).toEqual(bindings.tree);
  });

  it('should have structure data', () => {
    const { controller } = createComponent(bindings);

    expect(controller.structure).toEqual(bindings.structure);
  });

  it('should have content types data', () => {
    const { controller } = createComponent(bindings);

    expect(controller.contentTypes).toEqual(bindings.contentTypes);
  });

  it('should set selected parent', () => {
    const parent = {
      id: 1,
    };
    
    const { controller } = createComponent(bindings);
    controller.setContentParent(parent);

    expect(controller.selectedParent).toBe('Home');
  });

  it('should reset action tree to false when setting content parent', () => {
    const parent = {
      id: 1,
    };
    
    const { controller } = createComponent(bindings);
    controller.setContentParent(parent);

    expect(controller.actionTree).toBe(false);
  });

  it('should set selected parent in the structure', () => {
    const parent = {
      id: 1,
    };
    
    const { controller } = createComponent(bindings);
    controller.setContentParent(parent);

    expect(controller.content.structure.parent).toBe(1);
  });

  it('should show success notification when setting content parent', () => {
    spyOn(notificationHelper, 'success');

    const parent = {
      id: 1,
    };
    
    const { controller } = createComponent(bindings);
    controller.setContentParent(parent);

    expect(notificationHelper.success).toHaveBeenCalledWith('Your content location has been changed, please save to confirm.');
  });

  it('should toggle action tree', () => {
    const { controller } = createComponent(bindings);

    const actionTreeValue = controller.actionTree;

    controller.toggleActionTree();
    expect(controller.actionTree).toBe(!actionTreeValue);
  });

  it('should set fields back to string when updating content', () => {
    spyOn(contentFieldsHelper, 'setFieldsToString');
    const { controller } = createComponent(bindings);

    controller.ContentCreate.$valid = true;
    controller.content = {
      structure: {
        link: 'test',
      }
    };
    
    controller.updateContent();
    expect(contentFieldsHelper.setFieldsToString).toHaveBeenCalled();    
  });

  it('should make API call when updating content', () => {
    spyOn(dataContent, 'updateContent').and.returnValue($q.when({}));
    const { controller } = createComponent(bindings);

    controller.ContentCreate.$valid = true;
    controller.content = {
      structure: {
        link: 'test',
      }
    };
    
    controller.updateContent();
    expect(dataContent.updateContent).toHaveBeenCalled();    
  });

  function createComponent(scope) {
    let $scope = $rootScope.$new();
    $scope.tree = scope.tree;
    $scope.structure = scope.structure;
    $scope.contentTypes = scope.contentTypes;
    const element = $compile('<cms-update-content tree="tree" structure="structure" content-types="contentTypes"></cms-update-content>')($scope);
    $scope.$digest();

    const controller = element.isolateScope().vm;

    return { element, controller };
  }

});
