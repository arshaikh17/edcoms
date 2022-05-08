describe('Side menu component', () => {
  let $componentController;
  let controller;

  beforeEach(() => {
    module('cms');

    inject(($injector) => {
      $componentController = $injector.get('$componentController');
      controller = $componentController('cmsSideMenu', {
        $scope: {}
      });
    })
  });

  it('should exist', () => {
    expect(controller).toBeDefined();
  });

});
