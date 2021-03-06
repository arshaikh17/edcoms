const customFieldsMock = [{
  "id": 0,
  "name": "title",
  "description": "title\n",
  "fieldType": "text",
  "label": "Title",
  "defaultValue": null,
  "required": false,
  "options": null,
  "order": null,
  "adminOnly": false,
  "parent": null,
  "children": {},
  "repeatable": true
}, {
  "id": 1,
  "name": "group",
  "description": "group",
  "fieldType": "group",
  "label": "Carousel",
  "defaultValue": null,
  "required": false,
  "options": null,
  "order": null,
  "adminOnly": false,
  "parent": null,
  "children": {},
  "repeatable": true,
  "subfields": [{
    "id": 22,
    "name": "image",
    "description": "image",
    "fieldType": "image",
    "label": "Image",
    "defaultValue": null,
    "required": false,
    "options": null,
    "order": null,
    "adminOnly": false,
    "parent": {
      "id": 15,
      "name": "group",
      "description": "group",
      "fieldType": "group",
      "label": "Carousel",
      "defaultValue": null,
      "required": false,
      "options": null,
      "order": null,
      "adminOnly": false,
      "parent": null,
      "children": {},
      "repeatable": true
    },
    "children": {},
    "repeatable": false
  }]
}, {
  "id": 2,
  "name": "textarea",
  "description": "textarea\n",
  "fieldType": "textarea",
  "label": "Long text",
  "defaultValue": null,
  "required": false,
  "options": null,
  "order": null,
  "adminOnly": false,
  "parent": null,
  "children": {},
  "repeatable": false
}, {
  "id": 3,
  "name": "body",
  "description": "body",
  "fieldType": "richtextarea",
  "label": "Body",
  "defaultValue": null,
  "required": false,
  "options": null,
  "order": null,
  "adminOnly": false,
  "parent": null,
  "children": {},
  "repeatable": false
}, {
  "id": 4,
  "name": "linked_content",
  "description": "linked_content",
  "fieldType": "content_array",
  "label": "Linked content",
  "defaultValue": null,
  "required": false,
  "options": '{"contentType":[1]}',
  "order": null,
  "adminOnly": false,
  "parent": null,
  "children": {},
  "repeatable": false
}, {
  "id": 5,
  "name": "checkboxes",
  "description": "checkboxes",
  "fieldType": "checkbox_array",
  "label": "checkboxes",
  "defaultValue": null,
  "required": false,
  "options": null,
  "order": null,
  "adminOnly": false,
  "parent": null,
  "children": {},
  "repeatable": false
}];
