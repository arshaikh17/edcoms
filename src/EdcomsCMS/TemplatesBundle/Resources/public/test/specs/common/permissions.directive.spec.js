describe('Permissions directive', function () {
  let $scope = null;
  let $rootScope;
  let $compile;
  let permissionsHelper = null;
  let element;

  beforeEach(function () {
    module('common');

    inject(function ($injector) {
      const $httpBackend = $injector.get('$httpBackend');
      permissionsHelper = $injector.get('permissionsHelper');
      $compile = $injector.get('$compile');
      $rootScope = $injector.get('$rootScope');

      $httpBackend.expectGET('/cms/users/get_perms').respond(permissionsMock);
      permissionsHelper.set(permissionsMock);
    });
  });

  it('should check the permission and show the element', function () {
    initDirective('content;read');
    expect(element.hasClass('ng-hide')).toBe(false);
  });

  it('should check the permission and hide the element', function () {
    initDirective('contenttest;read');
    expect(element.hasClass('ng-hide')).toBe(true);
  });

  function initDirective(permissions) {
    $scope = $rootScope.$new();

    element = $compile(`<div permissions="${permissions}">Submit</div>`)($scope);
    $scope.$digest();
  }
});
