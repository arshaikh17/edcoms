### Form Types
- Known issues is the usage of EntityType in the **type** section. This is a limit of the Dmishh bundle itself. 
Given the above, all other Form Types having entity based dependencies like CollectionType won't work as well.  
The rest of the forms types are tested to work. 

### Validations (Constraints)
- All the validations present in the **Symfony\Component\Validator\Constraints** namespace are able to be used.
- Also, custom validators following that implementation would work as well. 
  The same behavior of working in Symfony Forms Constraints is expected.
- Usage of **constraints** is optional. 

### Minimum required settings
```
edcoms_cms_settings: ~
  
```
### Adding another setting
```
edcoms_cms_settings:
  settings:
      TextArea:
        type: Symfony\Component\Form\Extension\Core\Type\TextareaType
        options:
            required: true
        constraints:
            Symfony\Component\Validator\Constraints\NotBlank: ~
```

### Overriding settings options
```
edcoms_cms_settings:
   settings:
       maintenance:
           options:
               required: false
               choices: ['true', 'false']
               
## It is not possible to override the type and category because of predefined settings 
```
 
### Full settings configuration sample!
```
edcoms_cms_settings:
  serialization: json
  categories: ['Maintenance', 'CMS'] # categories set in the settings below has to be in this collection the 'CMS' should be always present as it is used as fallback
  settings:
      maintenance:
          category: maintenance
          type: choice
          options:
              required: true
              choices: ["on","off"]
      TextArea:
          type: Symfony\Component\Form\Extension\Core\Type\TextareaType
          options:
              required: true
          constraints:
              Symfony\Component\Validator\Constraints\NotBlank: ~
      RichTextArea:
          type: EdcomsCMS\AdminBundle\Form\Type\RichTextAreaType
          options:
              required: true
              
      CheckboxType:
          type: Symfony\Component\Form\Extension\Core\Type\CheckboxType
          options:
              required: true
      EmailType:
          type: Symfony\Component\Form\Extension\Core\Type\EmailType
          options:
              required: true
          constraints:
              Symfony\Component\Validator\Constraints\Email: ~
      DatePicker:
          type: Sonata\CoreBundle\Form\Type\DatePickerType
          options:
              required: true
              widget: choice
              input: string
      DateType:
          type: Symfony\Component\Form\Extension\Core\Type\DateType
          options:
              required: true
              widget: choice
              input: string
      NumberType:
          type: Symfony\Component\Form\Extension\Core\Type\NumberType
          options:
              required: true
          constraints:
              Symfony\Component\Validator\Constraints\Range:
                  min: 1
                  max: 10
      TextType:
          type: Symfony\Component\Form\Extension\Core\Type\TextType
          options:
              required: true
```