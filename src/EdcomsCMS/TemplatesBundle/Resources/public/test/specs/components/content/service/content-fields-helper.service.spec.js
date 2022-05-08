describe('contentFieldsHelper', () => {
  let contentFieldsHelper = null;
  let FIELD_TYPES = null;

  beforeEach(() => {
    module('cms.content');
    module('common');
    module('cms.core');
    module('cms.data');

    inject(($injector) => {
      contentFieldsHelper = $injector.get('contentFieldsHelper');
      FIELD_TYPES = $injector.get('FIELD_TYPES');
    });
  });

  describe('Repeatable and group fields', () => {
    let fieldsdMock;

    describe('Type text', () => {
      beforeEach(() => {
        fieldsdMock = {
          '27': {
            'id': 27,
            'name': 'player',
            'description': 'Player',
            'fieldType': FIELD_TYPES.GROUP,
            'label': 'Player',
            'repeatable': true,
            'parent': null,
            'subfields': [{
              'id': 28,
              'name':
              'player_f_name',
              'description': 'First name',
              'fieldType': FIELD_TYPES.TEXT,
              'label': 'First name',
              'parent': 27,
              'repeatable': false
            }, {
              'id': 29,
              'name': 'year_of_birth',
              'description': 'Year of birth',
              'fieldType': FIELD_TYPES.TEXT,
              'label': 'YOB',
              'parent': 27,
              'repeatable': false
            }, {
              'id': 30,
              'name': 'phone_number',
              'description': 'Phone number',
              'fieldType': FIELD_TYPES.TEXT,
              'label': 'Phone number',
              'parent': 27,
              'repeatable': true
            }]
          },
        };
      });

      describe('With No data', () => {
        it('should convert fields from string to array', () => {
          const content = {
            'title': 'Team',
            'player': [{
              'player_f_name': '',
              'year_of_birth': '',
              'phone_number': ''
            }],
          };

          const expectedContent = {
            'title': 'Team',
            'player': [{
              'player_f_name': [ '' ],
              'year_of_birth': [ '' ],
              'phone_number': [ '' ]
            }],
          };

          contentFieldsHelper.setFieldsToArray(fieldsdMock, content);

          expect(content).toEqual(expectedContent);
        });

        it('should convert fields from array to string', () => {
          const content = {
            'title': 'Team',
            'player': [{
              'player_f_name': [ '' ],
              'year_of_birth': [ '' ],
              'phone_number': [ '' ]
            }],
          };

          const expectedContent = {
            'title': 'Team',
            'player': [{
              'player_f_name': '',
              'year_of_birth': '',
              'phone_number': [ '' ]
            }],
          };

          contentFieldsHelper.setFieldsToString(fieldsdMock, content);

          expect(content).toEqual(expectedContent);
        });
      });

      describe('With existing data', () => {
        it('should convert fields from string to array', () => {
          const content = {
            'title': 'Team',
            'player': [{
              'player_f_name': 'P1 first name',
              'year_of_birth': 'P1 YOB',
              'phone_number': [
                'P1 0988732',
                'P1 09887328762387126']
            }, {
              'player_f_name': 'P2 first name',
              'year_of_birth': 'P2 YOB',
              'phone_number': ['0219u']
            }],
          };

          const expectedContent = {
            'title': 'Team',
            'player': [{
              'player_f_name': ['P1 first name'],
              'year_of_birth': ['P1 YOB'],
              'phone_number': [
                'P1 0988732',
                'P1 09887328762387126']
            }, {
              'player_f_name': ['P2 first name'],
              'year_of_birth': ['P2 YOB'],
              'phone_number': ['0219u']
            }],
          };

          contentFieldsHelper.setFieldsToArray(fieldsdMock, content);

          expect(content).toEqual(expectedContent);
        });

        it('should convert field from array to string', () => {
          const content = {
            'title': 'Team',
            'player': [{
              'player_f_name': ['P1 first name'],
              'year_of_birth': ['P1 YOB'],
              'phone_number': [
                'P1 0988732',
                'P1 09887328762387126']
            }, {
              'player_f_name': ['P2 first name'],
              'year_of_birth': ['P2 YOB'],
              'phone_number': ['0219u']
            }],
          };

          const expectedContent = {
            'title': 'Team',
            'player': [{
              'player_f_name': 'P1 first name',
              'year_of_birth': 'P1 YOB',
              'phone_number': [
                'P1 0988732',
                'P1 09887328762387126']
            }, {
              'player_f_name': 'P2 first name',
              'year_of_birth': 'P2 YOB',
              'phone_number': ['0219u']
            }],
          };

          contentFieldsHelper.setFieldsToString(fieldsdMock, content);

          expect(content).toEqual(expectedContent);
        });
      });

    });

    describe('Type checkbox', () => {
      beforeEach(() => {
        fieldsdMock = {
          '27': {
            'id': 27,
            'name': 'player',
            'description': 'Player',
            'fieldType': FIELD_TYPES.GROUP,
            'label': 'Player',
            'repeatable': true,
            'parent': null,
            'subfields': [{
              'id': 28,
              'name':
              'has_a_car',
              'description': 'Has a car',
              'fieldType': FIELD_TYPES.CHECKBOX,
              'label': 'Has a car',
              'parent': 27,
              'repeatable': true
            }]
          },
        };
      });

      describe('With No data', () => {
        it('should convert fields from string to array', () => {
          const content = {
            'title': 'Team',
            'player': [{
              'has_a_car': '',
            }],
          };

          const expectedContent = {
            'title': 'Team',
            'player': [{
              'has_a_car': [ '' ],
            }],
          };

          contentFieldsHelper.setFieldsToArray(fieldsdMock, content);

          expect(content).toEqual(expectedContent);
        });

        it('should convert fields from array to string', () => {
          const content = {
            'title': 'Team',
            'player': [{
              'has_a_car': [ '' ],
            }],
          };

          const expectedContent = {
            'title': 'Team',
            'player': [{
              'has_a_car': [ '' ],
            }],
          };

          contentFieldsHelper.setFieldsToString(fieldsdMock, content);

          expect(content).toEqual(expectedContent);
        });
      });

      describe('With existing data', () => {
        it('should convert fields from string to array', () => {
          const content = {
            'title': 'Team',
            'player': [{
              'has_a_car': ['0'],
            }],
          };

          const expectedContent = {
            'title': 'Team',
            'player': [{
              'has_a_car': ['0'],
            }],
          };

          contentFieldsHelper.setFieldsToArray(fieldsdMock, content);

          expect(content).toEqual(expectedContent);
        });

        it('should convert field from array to string', () => {
          const content = {
            'title': 'Team',
            'player': [{
              'has_a_car': ['0'],
            }],
          };

          const expectedContent = {
            'title': 'Team',
            'player': [{
              'has_a_car': ['0'],
            }],
          };

          contentFieldsHelper.setFieldsToString(fieldsdMock, content);

          expect(content).toEqual(expectedContent);
        });
      });

    });

    describe('Type single choices (radio button)', () => {
      beforeEach(() => {
        fieldsdMock = {
          '27': {
            'id': 27,
            'name': 'player',
            'description': 'Player',
            'fieldType': FIELD_TYPES.GROUP,
            'label': 'Player',
            'repeatable': true,
            'parent': null,
            'subfields': [{
              'id': 28,
              'name': 'has_a_car',
              'description': 'Has a car',
              'fieldType': FIELD_TYPES.RADIO_LIST,
              'label': 'Has a car',
              'parent': 27,
              'repeatable': false
            }]
          },
        };
      });

      describe('With No data', () => {
        it('should convert fields from string to array', () => {
          const content = {
            'title': 'Team',
            'player': [{
              'has_a_car': '',
            }],
          };

          const expectedContent = {
            'title': 'Team',
            'player': [{
              'has_a_car': [ '' ],
            }],
          };

          contentFieldsHelper.setFieldsToArray(fieldsdMock, content);

          expect(content).toEqual(expectedContent);
        });

        it('should convert fields from array to string', () => {
          const content = {
            'title': 'Team',
            'player': [{
              'has_a_car': [ '' ],
            }],
          };

          const expectedContent = {
            'title': 'Team',
            'player': [{
              'has_a_car': '',
            }],
          };

          contentFieldsHelper.setFieldsToString(fieldsdMock, content);

          expect(content).toEqual(expectedContent);
        });
      });

      describe('With existing data', () => {
        it('should convert fields from string to array', () => {
          const content = {
            'title': 'Team',
            'player': [{
              'has_a_car': '1',
            }],
          };

          const expectedContent = {
            'title': 'Team',
            'player': [{
              'has_a_car': [ '1' ],
            }],
          };

          contentFieldsHelper.setFieldsToArray(fieldsdMock, content);

          expect(content).toEqual(expectedContent);
        });

        it('should convert field from array to string', () => {
          const content = {
            'title': 'Team',
            'player': [{
              'has_a_car': [ '0' ],
            }],
          };

          const expectedContent = {
            'title': 'Team',
            'player': [{
              'has_a_car': '0',
            }],
          };

          contentFieldsHelper.setFieldsToString(fieldsdMock, content);

          expect(content).toEqual(expectedContent);
        });
      });

    });
  });

  describe('Repeatable but no group fields', () => {
    let fieldsdMock;

    beforeEach(() => {
      fieldsdMock = {
        '27': {
          'id': 27,
          'name': 'text',
          'fieldType': FIELD_TYPES.TEXT,
          'repeatable': true,
          'subfields': []
        },
        '28': {
          'id': 28,
          'name': 'date',
          'fieldType': FIELD_TYPES.DATE,
          'repeatable': true,
          'subfields': []
        },
        '29': {
          'id': 29,
          'name': 'number',
          'fieldType': FIELD_TYPES.NUMBER,
          'repeatable': true,
          'subfields': []
        },
        '30': {
          'id': 30,
          'name': 'single_choices',
          'fieldType': FIELD_TYPES.RADIO_LIST,
          'options': 'chocolat;caramel;vanilla',
          'repeatable': true,
          'subfields': []
        },
        '31': {
          'id': 31,
          'name': 'multiple_choices',
          'fieldType': FIELD_TYPES.CHECKBOX_LIST,
          'options': 'chocolat;caramel;vanilla',
          'repeatable': true,
          'subfields': []
        },
        '32': {
          'id': 32,
          'name': 'textarea',
          'fieldType': FIELD_TYPES.TEXTAREA,
          'repeatable': true,
          'subfields': []
        },
        '33': {
          'id': 33,
          'name': 'richtextarea',
          'fieldType': FIELD_TYPES.RICHTEXTAREA,
          'repeatable': true,
          'subfields': []
        },
        '34': {
          'id': 34,
          'name': 'image',
          'fieldType': FIELD_TYPES.IMAGE,
          'repeatable': true,
          'subfields': []
        },
        '35': {
          'id': 35,
          'name': 'file',
          'fieldType': FIELD_TYPES.FILE,
          'repeatable': true,
          'subfields': []
        },
        '36': {
          'id': 36,
          'name': 'checkbox',
          'fieldType': FIELD_TYPES.CHECKBOX,
          'repeatable': true,
          'subfields': []
        },
        '37': {
          'id': 37,
          'name': 'content_list',
          'fieldType': FIELD_TYPES.CONTENT_LIST,
          'repeatable': true,
          'subfields': []
        },
        '38': {
          'id': 38,
          'name': 'file_list',
          'fieldType': FIELD_TYPES.FILE_LIST,
          'repeatable': true,
          'subfields': []
        },
      };
    });

    describe('With No data', () => {
      it('should convert fields from string to array', () => {
        const content = {
          'title': 'Team',
          'text': '',
          'date': '',
          'number': '',
          'single_choices': '',
          'multiple_choices': '',
          'textarea': '',
          'richtextarea': '',
          'image': '',
          'file': '',
          'checkbox': '',
          'content_list': '',
          'file_list': ''
        };

        const expectedContent = {
          'title': 'Team',
          'text': [''],
          'date': [''],
          'number': [''],
          'single_choices': [ '' ],
          'multiple_choices': [[  ]],
          'textarea': [''],
          'richtextarea': [''],
          'image': [''],
          'file': [''],
          'checkbox': [''],
          'content_list': [[ ]],
          'file_list': [ '' ]
        };

        contentFieldsHelper.setFieldsToArray(fieldsdMock, content);

        expect(content).toEqual(expectedContent);
      });

      it('should convert fields from array to string', () => {
        const content = {
          'title': 'Team',
          'text': [''],
          'date': [''],
          'number': [''],
          'single_choices': [ '' ],
          'multiple_choices': [[]],
          'textarea': [''],
          'richtextarea': [''],
          'image': [''],
          'file': [''],
          'checkbox': [''],
          'content_list': [[]],
          'file_list': [ '' ]
        };

        const expectedContent = {
          'title': 'Team',
          'text': [''],
          'date': [''],
          'number': [''],
          'single_choices': [''],
          'multiple_choices': [''],
          'textarea': [''],
          'richtextarea': ['<div class="s-ugc"></div>'],
          'image': [''],
          'file': [''],
          'checkbox': [''],
          'content_list': [''],
          'file_list': [ '""' ]
        };

        contentFieldsHelper.setFieldsToString(fieldsdMock, content);

        expect(content).toEqual(expectedContent);
      });
    });

    describe('With existing data', () => {
      it('should convert fields from string to array', () => {
        const content = {
          'title': 'Team',
          'text': ['test 1'],
          'date': ['test 2'],
          'number': ['test 3'],
          'single_choices': [ 'test4' ],
          'multiple_choices': [ 'test6,test7' ],
          'textarea': ['test8'],
          'richtextarea': ['test9'],
          'image': ['test10'],
          'file': ['test11'],
          'checkbox': ['test12'],
          'content_list': [ '1, 2, 3, 4' ],
          'file_list': [ '[]' ]
        };

        const expectedContent = {
          'title': 'Team',
          'text': ['test 1'],
          'date': ['test 2'],
          'number': ['test 3'],
          'single_choices': ['test4'],
          'multiple_choices': [ ['test6', 'test7'] ],
          'textarea': ['test8'],
          'richtextarea': ['test9'],
          'image': ['test10'],
          'file': ['test11'],
          'checkbox': ['test12'],
          'content_list': [['1', ' 2', ' 3', ' 4']],
          'file_list': [ [  ] ]
        };

        contentFieldsHelper.setFieldsToArray(fieldsdMock, content);

        expect(content).toEqual(expectedContent);
      });

      it('should convert fields from array to string', () => {
        const content = {
          'title': 'Team',
          'text': ['test 1'],
          'date': ['test 2'],
          'number': ['test 3'],
          'single_choices': ['test4'],
          'multiple_choices': [ ['test6', 'test7'] ],
          'textarea': ['test8'],
          'richtextarea': ['test9'],
          'image': ['test10'],
          'file': ['test11'],
          'checkbox': ['test12'],
          'content_list': [[ 1, 2, 3, 4]],
          'file_list': [[]]
        };

        const expectedContent = {
          'title': 'Team',
          'text': ['test 1'],
          'date': ['test 2'],
          'number': ['test 3'],
          'single_choices': ['test4'],
          'multiple_choices': ['test6,test7'],
          'textarea': ['test8'],
          'richtextarea': ['<div class="s-ugc">test9</div>'],
          'image': ['test10'],
          'file': ['test11'],
          'checkbox': ['test12'],
          'content_list': ['1,2,3,4'],
          'file_list': ['[]']
        };

        contentFieldsHelper.setFieldsToString(fieldsdMock, content);

        expect(content).toEqual(expectedContent);
      });
    });

  });

  describe('Group but no repeatable fields', () => {
    let fieldsdMock;

    beforeEach(() => {
      fieldsdMock = {
        '27': {
          'id': 27,
          'name': 'player',
          'description': 'Player',
          'fieldType': FIELD_TYPES.GROUP,
          'label': 'Player',
          'repeatable': false,
          'subfields': [{
            'id': 28,
            'name':
            'player_f_name',
            'description': 'First name',
            'fieldType': FIELD_TYPES.TEXT,
            'label': 'First name',
            'parent': 27,
            'repeatable': false
          }, {
            'id': 30,
            'name': 'phone_number',
            'description': 'Phone number',
            'fieldType': FIELD_TYPES.TEXT,
            'label': 'Phone number',
            'parent': 27,
            'repeatable': false
          }]
        },
      };
    });

    describe('With existing data', () => {
      it('should convert fields from string to array', () => {
        const content = {
          'title': 'Team',
          'player': {
            'player_f_name': 'P1 name',
            'phone_number': '0123456788'
          },
        };

        const expectedContent = {
          'title': 'Team',
          'player': [{
            'player_f_name': ['P1 name'],
            'phone_number': ['0123456788']
          }],
        };

        contentFieldsHelper.setFieldsToArray(fieldsdMock, content);

        expect(content).toEqual(expectedContent);
      });

      it('should convert fields from array to string', () => {
        const content = {
          'title': 'Team',
          'player': [{
            'player_f_name': ['P1 name'],
            'phone_number': ['0123456788']
          }],
        };

        const expectedContent = {
          'title': 'Team',
          'player': {
            'player_f_name': 'P1 name',
            'phone_number': '0123456788'
          },
        };

        contentFieldsHelper.setFieldsToString(fieldsdMock, content);

        expect(content).toEqual(expectedContent);
      });
    });

    describe('With No data', () => {
      it('should convert fields from string to array', () => {
        const content = {
          'title': 'Team',
          'player': {
            'player_f_name': '',
            'phone_number': ''
          },
        };

        const expectedContent = {
          'title': 'Team',
          'player': [{
            'player_f_name': [''],
            'phone_number': ['']
          }],
        };

        contentFieldsHelper.setFieldsToArray(fieldsdMock, content);

        expect(content).toEqual(expectedContent);
      });

      it('should convert fields from array to string', () => {
        const content = {
          'title': 'Team',
          'player': [{
            'player_f_name': [''],
            'phone_number': ['']
          }],
        };

        const expectedContent = {
          'title': 'Team',
          'player': {
            'player_f_name': '',
            'phone_number': ''
          },
        };

        contentFieldsHelper.setFieldsToString(fieldsdMock, content);

        expect(content).toEqual(expectedContent);
      });
    });
  });

  describe('No repeatable or group fields', () => {
    let fieldsdMock;

    beforeEach(() => {
      fieldsdMock = {
        '27': {
          'id': 27,
          'name': 'text',
          'fieldType': FIELD_TYPES.TEXT,
          'repeatable': false,
          'subfields': []
        },
        '28': {
          'id': 28,
          'name': 'date',
          'fieldType': FIELD_TYPES.DATE,
          'repeatable': false,
          'subfields': []
        },
        '29': {
          'id': 29,
          'name': 'number',
          'fieldType': FIELD_TYPES.NUMBER,
          'repeatable': false,
          'subfields': []
        },
        '30': {
          'id': 30,
          'name': 'single_choices',
          'fieldType': FIELD_TYPES.RADIO_LIST,
          'options': 'chocolat;caramel;vanilla',
          'repeatable': false,
          'subfields': []
        },
        '31': {
          'id': 31,
          'name': 'multiple_choices',
          'fieldType': FIELD_TYPES.CHECKBOX_LIST,
          'options': 'chocolat;caramel;vanilla',
          'repeatable': false,
          'subfields': []
        },
        '32': {
          'id': 32,
          'name': 'textarea',
          'fieldType': FIELD_TYPES.TEXTAREA,
          'repeatable': false,
          'subfields': []
        },
        '33': {
          'id': 33,
          'name': 'richtextarea',
          'fieldType': FIELD_TYPES.RICHTEXTAREA,
          'repeatable': false,
          'subfields': []
        },
        '34': {
          'id': 34,
          'name': 'image',
          'fieldType': FIELD_TYPES.IMAGE,
          'repeatable': false,
          'subfields': []
        },
        '35': {
          'id': 35,
          'name': 'file',
          'fieldType': FIELD_TYPES.FILE,
          'repeatable': false,
          'subfields': []
        },
        '36': {
          'id': 36,
          'name': 'checkbox',
          'fieldType': FIELD_TYPES.CHECKBOX,
          'repeatable': false,
          'subfields': []
        },
        '37': {
          'id': 37,
          'name': 'content_list',
          'fieldType': FIELD_TYPES.CONTENT_LIST,
          'repeatable': false,
          'subfields': []
        },
        '38': {
          'id': 38,
          'name': 'file_list',
          'fieldType': FIELD_TYPES.FILE_LIST,
          'repeatable': false,
          'subfields': []
        },
      };
    });

    describe('With No data', () => {
      it('should convert fields from string to array with no data', () => {
        const content = {
          'title': 'Team',
          'text': '',
          'date': '',
          'number': '',
          'single_choices': '',
          'multiple_choices': '',
          'textarea': '',
          'richtextarea': '',
          'image': '',
          'file': '',
          'checkbox': '',
          'content_list': '',
          'file_list': ''
        };

        const expectedContent = {
          'title': 'Team',
          'text': [''],
          'date': [''],
          'number': [''],
          'single_choices': [ '' ],
          'multiple_choices': [[  ]],
          'textarea': [''],
          'richtextarea': [''],
          'image': [''],
          'file': [''],
          'checkbox': [''],
          'content_list': [[  ]],
          'file_list': [ '' ]
        };

        contentFieldsHelper.setFieldsToArray(fieldsdMock, content);

        expect(content).toEqual(expectedContent);
      });

      it('should convert fields from array to string', () => {
        const content = {
          'title': 'Team',
          'text': [''],
          'date': [''],
          'number': [''],
          'single_choices': [ '' ],
          'multiple_choices': [[  ]],
          'textarea': [''],
          'richtextarea': [''],
          'image': [''],
          'file': [''],
          'checkbox': [''],
          'content_list': [[  ]],
          'file_list': [[ '' ]]
        };

        const expectedContent = {
          'title': 'Team',
          'text': '',
          'date': '',
          'number': '',
          'single_choices': '',
          'multiple_choices': '',
          'textarea': '',
          'richtextarea': '<div class="s-ugc"></div>',
          'image': '',
          'file': '',
          'checkbox': '',
          'content_list': '',
          'file_list': '[""]'
        };

        contentFieldsHelper.setFieldsToString(fieldsdMock, content);

        expect(content).toEqual(expectedContent);
      });
    });

    describe('With existing data', () => {
      it('should convert fields from string to array', () => {
        const content = {
          'title': 'Team',
          'text': 'P1 name',
          'date': '12/01/2009',
          'number': '0123456788',
          'single_choices': 'chocolate',
          'multiple_choices': 'chocolate,vanilla',
          'textarea': 'test texteara',
          'richtextarea': 'test richtextarea',
          'image': '/path/to/my/file',
          'file': '/path/to/my/file',
          'checkbox': '1',
          'content_list': '1,2,3,4',
          'file_list': '[]'
        };

        const expectedContent = {
          'title': 'Team',
          'text': ['P1 name'],
          'date': ['12/01/2009'],
          'number': ['0123456788'],
          'single_choices': ['chocolate'],
          'multiple_choices': [['chocolate', 'vanilla']],
          'textarea': ['test texteara'],
          'richtextarea': ['test richtextarea'],
          'image': ['/path/to/my/file'],
          'file': ['/path/to/my/file'],
          'checkbox': ['1'],
          'content_list': [[ '1', '2', '3' , '4' ]],
          'file_list': [ '[]' ]
        };

        contentFieldsHelper.setFieldsToArray(fieldsdMock, content);

        expect(content).toEqual(expectedContent);
      });

      it('should convert fields from array to string', () => {
        const content = {
          'title': 'Team',
          'text': ['P1 name'],
          'date': ['12/01/2009'],
          'number': ['0123456788'],
          'single_choices': ['chocolate'],
          'multiple_choices': ['chocolate,vanilla'],
          'textarea': ['test textarea'],
          'richtextarea': ['test richtextarea'],
          'image': ['/path/to/my/file'],
          'file': ['/path/to/my/file'],
          'checkbox': ['1'],
          'content_list': ['1,2,3,4'],
          'file_list': ['[]']
        };

        const expectedContent = {
          'title': 'Team',
          'text': 'P1 name',
          'date': '12/01/2009',
          'number': '0123456788',
          'single_choices': 'chocolate',
          'multiple_choices': 'chocolate,vanilla',
          'textarea': 'test textarea',
          'richtextarea': '<div class="s-ugc">test richtextarea</div>',
          'image': '/path/to/my/file',
          'file': '/path/to/my/file',
          'checkbox': '1',
          'content_list': '1,2,3,4',
          'file_list': '"[]"'
        };

        contentFieldsHelper.setFieldsToString(fieldsdMock, content);

        expect(content).toEqual(expectedContent);
      });
    });

  });

  it('should sort fields and subfields using order property', () => {
    const fieldsdMock = {
      '27': {
        'id': 27,
        'name': 'player',
        'description': 'Player',
        'fieldType': FIELD_TYPES.GROUP,
        'label': 'Player',
        'repeatable': true,
        'parent': null,
        'order': 2,
        'subfields': [{
          'id': 28,
          'name':
          'player_f_name',
          'description': 'First name',
          'fieldType': FIELD_TYPES.TEXT,
          'label': 'First name',
          'parent': 27,
          'repeatable': false,
          'order': 3,

        }, {
          'id': 29,
          'name': 'year_of_birth',
          'description': 'Year of birth',
          'fieldType': FIELD_TYPES.DATE,
          'label': 'YOB',
          'parent': 27,
          'repeatable': false,
          'order': 1,
        }, {
          'id': 30,
          'name': 'phone_number',
          'description': 'Phone number',
          'fieldType': FIELD_TYPES.TEXT,
          'label': 'Phone number',
          'parent': 27,
          'repeatable': true,
          'order': 2,
        }]
      },
      '31': {
        'id': 31,
        'name': 'other',
        'description': 'Other',
        'fieldType': FIELD_TYPES.TEXT,
        'label': 'Player',
        'repeatable': true,
        'parent': null,
        'order': 1,
        'subfields': [],
      }
    };

    const expectedFieldsResult = [
      {
        'id': 31,
        'name': 'other',
        'description': 'Other',
        'fieldType': FIELD_TYPES.TEXT,
        'label': 'Player',
        'repeatable': true,
        'parent': null,
        'order': 1,
        'subfields': [],
      },
      {
        'id': 27,
        'name': 'player',
        'description': 'Player',
        'fieldType': FIELD_TYPES.GROUP,
        'label': 'Player',
        'repeatable': true,
        'parent': null,
        'order': 2,
        'subfields': [{
          'id': 29,
          'name': 'year_of_birth',
          'description': 'Year of birth',
          'fieldType': FIELD_TYPES.DATE,
          'label': 'YOB',
          'parent': 27,
          'repeatable': false,
          'order': 1,
        }, {
          'id': 30,
          'name': 'phone_number',
          'description': 'Phone number',
          'fieldType': FIELD_TYPES.TEXT,
          'label': 'Phone number',
          'parent': 27,
          'repeatable': true,
          'order': 2,
        }, {
          'id': 28,
          'name':
          'player_f_name',
          'description': 'First name',
          'fieldType': FIELD_TYPES.TEXT,
          'label': 'First name',
          'parent': 27,
          'repeatable': false,
          'order': 3,
        }]
      }];

    const fieldsResult = contentFieldsHelper.sortFieldsByOrder(fieldsdMock);
    expect(fieldsResult).toEqual(expectedFieldsResult);
  });

  it('should set empty data for subfields on content object', () => {
    const content = [];

    const subfields = [{
      'id': 29,
      'name': 'year_of_birth',
      'description': 'Year of birth',
      'fieldType': FIELD_TYPES.DATE,
      'label': 'YOB',
      'parent': 27,
      'repeatable': false,
      'order': 1,
    }, {
      'id': 30,
      'name': 'phone_number',
      'description': 'Phone number',
      'fieldType': FIELD_TYPES.TEXT,
      'label': 'Phone number',
      'parent': 27,
      'repeatable': true,
      'order': 2,
    }, {
      'id': 28,
      'name':
      'player_f_name',
      'description': 'First name',
      'fieldType': FIELD_TYPES.TEXT,
      'label': 'First name',
      'parent': 27,
      'repeatable': false,
      'order': 3,
    }];

    const expectedContent = [{
      'year_of_birth': [''],
      'phone_number': [''],
      'player_f_name': ['']
    }];

    contentFieldsHelper.addSubfields(subfields, content);
    expect(expectedContent).toEqual(content);
  });

});
