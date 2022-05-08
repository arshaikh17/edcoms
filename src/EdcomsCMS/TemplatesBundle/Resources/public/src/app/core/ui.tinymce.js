/**
 * Binds a TinyMCE widget to <textarea> elements.
 */
(function () {
  angular.module('ui.tinymce')
		.value('uiTinymceConfig', {})
		.directive('uiTinymce', uiTinymce);

	/* @ngInject */
  function uiTinymce(uiTinymceConfig, $timeout) {
    uiTinymceConfig = {
      plugins: [
        "advlist autolink link image lists charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking",
        "table contextmenu directionality emoticons paste textcolor code filemanager"
      ],
      toolbar1: "newdocument bold italic underline strikethrough alignleft aligncenter alignright alignjustify  formatselect | cut copy paste pastetext bullist numlist outdent indent blockquote undo redo removeformat",
      toolbar2: "link code | image media",
      style_formats: [{
        title: 'Button link orange',
        selector: 'a',
        classes: 'btn btn--rsd btn--primary'
      }, {
        title: 'Button link grey',
        selector: 'a',
        classes: 'btn btn--rsd btn--grey'
      }, {
        title: 'Orange block',
        selector: 'ul',
        classes: 'block block--primary'
      }],
      extended_valid_elements: "script[type|src]",
      image_advtab: true,
      relative_urls: false,
      paste_as_text: true,
      external_filemanager_path: "/cms/filemanager/",
      filemanager_title: "Responsive Filemanager",
      media_alt_source: false,
      external_plugins: {
        'filemanager': '/bundles/edcomscmstemplates/src/assets/js/tinymce/plugins/responsivefilemanager/plugin.min.js'
      },
      video_template_callback: function(data) {
        return '<video class="video-js js-video video-js m-video vjs-big-play-centered" width="' + data.width + '" height="' + data.height + '" controls preload="auto" data-setup="{}" '+((data.poster) ? 'poster="'+data.poster+'"' : '')+' data-video-id="'+data.source1.split('.')[0].substr(1)+'"></video><script type="text/javascript">'+
              'var dataRes;'+
              'var xhr = new XMLHttpRequest();'+
              'var url = \'/API/sources/video\';'+
              'xhr.open(\'GET\', url, false);'+
              'xhr.onreadystatechange = function(response) {'+
              '    if (xhr.readyState === 4 && xhr.status === 200) {'+
              '        dataRes = JSON.parse(xhr.responseText);'+
              '        if (dataRes.sources.length > 0) {'+
              '           for (var x=0; x<dataRes.sources.length; x++) {'+
              '               if (!window.videoLibrary) {'+
              '                   document.write(\'<script src="\'+dataRes.sources[x]+\'" data-token="Bearer \'+dataRes.token+\'" async><\\/script>\');'+
              '                   window.videoLibrary = true;'+
              '               }'+
              '           }'+
              '        }'+
              '    }'+
              '};'+
              'xhr.send();'+
              'if (typeof videojs === \'undefined\') {'+
              '   document.write(\'<script src="//vjs.zencdn.net/5.16.0/video.min.js" async><\\/script>\');'+
              '   document.write(\'<link href="//vjs.zencdn.net/5.16.0/video-js.css" rel="stylesheet">\');'+
              '};'+
              '</script>';
      }
    };

    let generatedIds = 0;
    return {
      require: 'ngModel',
      link: function (scope, elm, attrs, ngModel) {
        let expression, options, tinyInstance;
				// generate an ID if not present
        if (!attrs.id) {
          attrs.$set('id', 'uiTinymce' + generatedIds++);
        }
        options = {
					// Update model when calling setContent (such as from the source editor popup)
          setup: function (ed) {
            ed.on('init', function () {
              ngModel.$render();
            });
						// Update model on button click
            ed.on('ExecCommand', function () {
              ed.save();
              ngModel.$setViewValue(elm.val());
              if (!scope.$$phase) {
                scope.$apply();
              }
            });
						// Update model on keypress
            ed.on('KeyUp', function () {
              if ((elm[0].value.replace(/<(?:.|\n)*?>/gm, '')).length >= parseInt(attrs.maxHtmlLength)) {
                ngModel.$setValidity("maxlength", false);
              } else {
                ngModel.$setValidity("maxlength", true);
              }

              ed.save();
              ngModel.$setViewValue(elm.val());
              if (!scope.$$phase) {
                scope.$apply();
              }
            });

						// Update model on change
            ed.on('change', function () {
              ed.save();
              ngModel.$setViewValue(elm.val());
              if (!scope.$$phase) {
                scope.$apply();
              }
            });
          },
          mode: 'exact',
          elements: attrs.id
        };
        if (attrs.uiTinymce) {
          expression = scope.$eval(attrs.uiTinymce);
        } else {
          expression = {};
        }
        angular.extend(options, uiTinymceConfig, expression);

        $timeout(function () {
          tinymce.init(options);
        });


        ngModel.$render = function () {
          if (!tinyInstance) {
            tinyInstance = tinymce.get(attrs.id);
          }
          if (tinyInstance) {
            tinyInstance.setContent(ngModel.$viewValue || '');
          }
        };
      }
    };
  }

})();
