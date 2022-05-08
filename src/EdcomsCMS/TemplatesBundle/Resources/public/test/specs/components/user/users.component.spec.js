describe('Users component', () => {
  let $componentController;
  let controller;

  beforeEach(() => {
    module('cms');

    inject(($injector) => {
      $componentController = $injector.get('$componentController');
      controller = $componentController('cmsUsers', {
        $scope: {}
      });
    })
  });

  it('should exist', () => {
    expect(controller).toBeDefined();
  });

});
