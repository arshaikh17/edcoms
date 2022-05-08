describe('Custom fields service', () => {
  let customFields = null;
  let FIELD_TYPES = null;
  
  beforeEach(() => {
    module('common');

    inject(($injector) => {
      customFields = $injector.get('customFields');
      FIELD_TYPES = $injector.get('FIELD_TYPES')
    });
  });

  
  it('should say field is of type array', () => {
    const field = {
      fieldType: FIELD_TYPES.CHECKBOX_LIST,
    };

    expect(customFields.isArray(field)).toBe(true);
  });

  it('should say field is NOT of type array', () => {
    const field = {
      fieldType: FIELD_TYPES.CHECKBOX,
    };

    expect(customFields.isArray(field)).toBe(false);
  });

  it('should say field text is of type generic text', () => {
    const field = {
      fieldType: FIELD_TYPES.TEXT,
    };

    expect(customFields.isGenericText(field)).toBe(true);
  });

  it('should say field textarea is of type generic text', () => {
    const field = {
      fieldType: FIELD_TYPES.TEXTAREA,
    };

    expect(customFields.isGenericText(field)).toBe(true);
  });

  it('should say field richtextarea is of type text', () => {
    const field = {
      fieldType: FIELD_TYPES.RICHTEXTAREA,
    };

    expect(customFields.isGenericText(field)).toBe(true);
  });

  it('should say field checkbox is NOT of type text', () => {
    const field = {
      fieldType: FIELD_TYPES.CHECKBOX,
    };

    expect(customFields.isGenericText(field)).toBe(false);
  });

  it('should say field is of type group', () => {
    const field = {
      fieldType: FIELD_TYPES.GROUP,
    };

    expect(customFields.isGroup(field)).toBe(true);
  });

  it('should say field is NOT of type group', () => {
    const field = {
      fieldType: FIELD_TYPES.CHECKBOX,
    };

    expect(customFields.isGroup(field)).toBe(false);
  });

  it('should say field is of type file array', () => {
    const field = {
      fieldType: FIELD_TYPES.FILE_LIST,
    };

    expect(customFields.isFileArray(field)).toBe(true);
  });

  it('should say field is NOT of type file array', () => {
    const field = {
      fieldType: FIELD_TYPES.TEXT,
    };

    expect(customFields.isFileArray(field)).toBe(false);
  });

  it('should say field is of type richtextarea', () => {
    const field = {
      fieldType: FIELD_TYPES.RICHTEXTAREA,
    };

    expect(customFields.isRichTextArea(field)).toBe(true);
  });

  it('should say field is NOT of type richtextarea', () => {
    const field = {
      fieldType: FIELD_TYPES.TEXT,
    };

    expect(customFields.isRichTextArea(field)).toBe(false);
  });

  it('should say field is of type content list', () => {
    const field = {
      fieldType: FIELD_TYPES.CONTENT_LIST,
    };

    expect(customFields.isContentList(field)).toBe(true);
  });

  it('should say field is NOT of type content list', () => {
    const field = {
      fieldType: FIELD_TYPES.TEXT,
    };

    expect(customFields.isContentList(field)).toBe(false);
  });
    
});
  