# CMS Angular app

## Symfony
This angular app lives inside a Symfony 3.x application. Because of symfony architecture and how assets are handled, this app needs to live
in a Symfony bundle and inside `Resources/public` of that bundle (TemplatesBundle in our case).

TemplatesBundle is where all client facing resources lives.

In order to link the Symfony app to the angular app, we have a Symfony route (/cms) pointing to the index.html.twig templates.
After that point, the angular app routing take over.

## TWIG templates

Historically, the app was build fully with twig templates and has been refactored then to use angular templates.
The main index.html.twig template is the point of contact between the 2 apps and kick of the angular app.
The login page is also a twig template and none of the logic for the login page is in the angular app.

All the other pages are handled in angular.

## Environment and tools

- requires node >= 5.6
- ES6
- gulp
- bower
- twig

## How to setup?

`$ npm start` will install bower dependencies, npm dependencies and watch for changes in Sass, javascipt and HTML files, compile and reload automatically with live reload
`$ npm run build` will install bower dependencies, npm dependencies and build for production
`$ npm run build:dev` will build for development
`$ npm test` will run the javascript unit test

## 3rd party

We're using some 3rd party library such as
  - File manager (http://www.responsivefilemanager.com/)
  - TinyMCE (https://www.tinymce.com/)
  - Toaster (http://codeseven.github.io/toastr/)
  - JQuery and JQuery UI
  - JQuery uploadifive (http://www.uploadify.com/documentation/uploadifive/implementing-uploadifive/)

## Vocabulary

Check out https://edcoms.atlassian.net/wiki/display/CC/Glossary+and+terminology

 - **structure**: refer to the tree most of the time and define the relationship parent to child between each created content
 - **UGC**: User generated content
 - **content type**: Define the structure/template of a content by associating fields to a type (ex: Article, Blog, Resource)
 - **custom fields**: The different fields template which define the type  (ex: title, hero image, body)
 - **content**: actual page/content formed of fields which structure is based on a content type
 - **content fields**: field value for each custom fields which together form a content/page
