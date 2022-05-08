describe('Field type constant', () => {
  let FIELD_TYPES = null;

  beforeEach(() => {
    module('common');

    inject(($injector) => {
      FIELD_TYPES = $injector.get('FIELD_TYPES');
    });
  });

  it('should have type text', () => {
    expect(FIELD_TYPES.TEXT).toBeDefined();
  });

  it('should have type textarea', () => {
    expect(FIELD_TYPES.TEXTAREA).toBeDefined();
  });

  it('should have type richtextarea', () => {
    expect(FIELD_TYPES.RICHTEXTAREA).toBeDefined();
  });

  it('should have type number', () => {
    expect(FIELD_TYPES.NUMBER).toBeDefined();
  });

  it('should have type date', () => {
    expect(FIELD_TYPES.DATE).toBeDefined();
  });

  it('should have type image', () => {
    expect(FIELD_TYPES.IMAGE).toBeDefined();
  });

  it('should have type file', () => {
    expect(FIELD_TYPES.FILE).toBeDefined();
  });

  it('should have type checkbox', () => {
    expect(FIELD_TYPES.CHECKBOX).toBeDefined();
  });

  it('should have type content list', () => {
    expect(FIELD_TYPES.CONTENT_LIST).toBeDefined();
  });

  it('should have type checkbox list', () => {
    expect(FIELD_TYPES.CHECKBOX_LIST).toBeDefined();
  });

  it('should have type radio list', () => {
    expect(FIELD_TYPES.RADIO_LIST).toBeDefined();
  });

  it('should have type file list', () => {
    expect(FIELD_TYPES.FILE_LIST).toBeDefined();
  });

  it('should have type group', () => {
    expect(FIELD_TYPES.GROUP).toBeDefined();
  });
});
