describe('Main menu component', () => {
  let $componentController;
  let controller;

  beforeEach(() => {
    module('cms');

    inject(($injector) => {
      $componentController = $injector.get('$componentController');
      controller = $componentController('cmsMainMenu', {
        $scope: {}
      });
    })
  });

  it('should exist', () => {
    expect(controller).toBeDefined();
  });

});
