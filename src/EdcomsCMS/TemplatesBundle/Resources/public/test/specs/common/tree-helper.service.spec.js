'use strict';

let treeMock = {
  id: 1,
  link: "home",
  title: "Home",
  parent: null,
  priority: null,
  rateable: null,
  rating: 0,
  content: {
    contentType: {
      id: 1
    }
  },
  children: [{
    id: 2,
    link: "about",
    title: "About",
    parent: {
      id: 1
    },
    priority: null,
    rateable: null,
    rating: 0,
    children: [],
    content: {
      contentType: {
        id: 2
      }
    }
  }, {
    id: 3,
    link: "blog",
    title: "Blog",
    parent: {
      id: 1
    },
    priority: null,
    rateable: null,
    rating: 0,
    children: [],
    content: {
      contentType: {
        id: 2
      }
    }
  }
  ]
};

describe('treeHelper', function () {
  let treeHelper;

  beforeEach(function () {
    module('cms');
    inject(function (_treeHelper_) {
      treeHelper = _treeHelper_;
    });
  });

  it('should set the selected item', function () {
    treeHelper.setSelectedItem(1);
    expect(treeHelper.getSelectedItem()).toBe(1);
  });

  it('should set the tree structure', function () {
    treeHelper.setSelectedStructure(treeMock);
    expect(treeHelper.getSelectedStructure()).toEqual(treeMock);
  });

  it('should set the tree type', function () {
    let type = 'structure';

    treeHelper.setCurrentType(type);
    expect(treeHelper.getCurrentType()).toBe(type);
  });

  it('should return item from given id', function () {
    treeHelper.setSelectedStructure(treeMock);
    expect(treeHelper.getItemById(treeMock, 2).title).toEqual('About');
  });

  it('should set the tree active states for given content type ids', function () {
    let ids = [2];

    treeHelper.setTreeActiveState(treeMock, ids);
    expect(treeHelper.getItemById(treeMock, 1).unActive).toBe(true);
  });


});
