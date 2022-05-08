describe('Update user component', () => {
  let $componentController;
  let controller;

  beforeEach(() => {
    module('cms');

    inject(($injector) => {
      $componentController = $injector.get('$componentController');
      controller = $componentController('cmsUpdateUser', {
        $scope: {}
      });
    })
  });

  it('should exist', () => {
    expect(controller).toBeDefined();
  });

});
