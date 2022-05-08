const contentTypeMock = {
  'data': {
    'content_type': {
      'id': 5,
      'name': 'Repeatable fields',
      'description': 'Repeatable fields content type\n',
      'thumbnail': null,
      'showChildren': false,
      'template_files': [],
      'isChild': false,
      'custom_fields': [{
        'id': 14,
        'name': 'title',
        'description': 'title\n',
        'fieldType': 'text',
        'label': 'Title',
        'defaultValue': null,
        'required': false,
        'options': null,
        'order': null,
        'adminOnly': false,
        'parent': null,
        'children': {},
        'repeatable': true
      }, {
        'id': 15,
        'name': 'group',
        'description': 'group',
        'fieldType': 'group',
        'label': 'Carousel',
        'defaultValue': null,
        'required': false,
        'options': null,
        'order': null,
        'adminOnly': false,
        'parent': null,
        'children': {},
        'repeatable': true,
        'subfields': [{
          'id': 22,
          'name': 'image',
          'description': 'image',
          'fieldType': 'image',
          'label': 'Image',
          'defaultValue': null,
          'required': false,
          'options': null,
          'order': null,
          'adminOnly': false,
          'parent': {
            'id': 15,
            'name': 'group',
            'description': 'group',
            'fieldType': 'group',
            'label': 'Carousel',
            'defaultValue': null,
            'required': false,
            'options': null,
            'order': null,
            'adminOnly': false,
            'parent': null,
            'children': {},
            'repeatable': true
          },
          'children': {},
          'repeatable': false
        }]
      }]
    },
    'required': {
      'id': true,
      'name': true,
      'description': true,
      'custom_fields': true
    }
  },
  'token': 'hvqhvwdkqwdkqbwkjb'
}
