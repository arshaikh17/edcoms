let userMock = {
  "id": 3,
  "username": "cms.user",
  "is_active": true,
  "person": {
    "id": 3,
    "firstName": "cms",
    "lastName": "user",
    "contacts": [{
      "type": "email",
      "title": "Email",
      "value": "cms_user@edcoms.co.uk"
    }]
  },
  "groups": [{
    "id": 2,
    "name": "cms_users",
    "description": "",
    "defaultValue": false,
    "user": {},
    "perms": {},
    "role": null
  }],
  "deleted": false
};

describe('userHelper', function () {
  let userHelper = null;

  beforeEach(function () {
    module('cms');
    inject(function ($injector) {
      userHelper = $injector.get('userHelper');
    });
  });


  it('should set the current user', function () {
    userHelper.set(userMock);
    let user = userHelper.get();
    expect(user).toBe(userMock);
  });

});
