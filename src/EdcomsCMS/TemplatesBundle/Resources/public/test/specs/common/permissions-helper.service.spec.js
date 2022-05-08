describe('permissionsHelper', function () {
  let permissionsHelper = null;

  beforeEach(() => {
    module('common.permissions');
    inject(($injector) => {
      permissionsHelper = $injector.get('permissionsHelper');
      
      permissionsHelper.set(permissionsMock);
    });
  });


  it('should set list of permissions', function () {
    const permissions = permissionsHelper.get();
    expect(permissions).toBe(permissionsMock);
  });

  it('should say user can read dashboard', function () {
    expect(permissionsHelper.hasPermissions('index', 'read')).toBe(true);
  });

  it('should say user can NOT edit content', function () {
    expect(permissionsHelper.hasPermissions('content', 'edit')).toBe(false);
  });

}); 
