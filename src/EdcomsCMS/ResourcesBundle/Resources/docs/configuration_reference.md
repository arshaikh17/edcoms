```
// Example:
// Configuration below is optional. It is needed only in cases  where something needs to be overriden.

edcoms_cms_resources:
  resource_subject:
    form:  EdcomsCMS\ResourcesBundle\Form\Type\ResourceSubjectType
    entity_class:  EdcomsCMS\ResourcesBundle\Entity\ResourceSubject
    used_as_context: false
    show_admin: true
    custom_admin_service: 'edcoms.resources.admin.resource_subject'
    label: Resource subject
    name:  resource_subject
    admin: EdcomsCMS\ResourcesBundle\Admin\ResourceSubjectAdmin
  resource:
    form:  
    entity_class: 
    used_as_context: true
    show_admin: false
    custom_admin_service: 
    label: 
    name:  
    admin: 
  resource_type:
    form:  
    entity_class:  
    used_as_context: false
    show_admin: true
    custom_admin_service: 
    label: 
    name:  
    admin: 
  resource_topic:
    form:  
    entity_class:  
    used_as_context: false
    show_admin: true
    custom_admin_service: 
    label: 
    name:  
    admin: 

```
