var EdcomsContent = function(){

    var content = null;

    $( document ).ready(function() {
        console.log( "Content Bundle JS file loaded!" );

        bindFilePicker();

        if($('#dataContent').length>0){
            content = JSON.parse($('#dataContent').attr('data-content'));
        }
    });

    var bindFilePicker = function(scope){

        $('[data-widget="edcoms-file-picker"]').each(function(){
            var fieldid = $(this).attr('data-input-id');
            var dialogURL = $(this).attr('data-dialog-url') ? $(this).attr('data-dialog-url') : '/cms/filemanager/dialog.php?field_id='+fieldid;
            $(this).find('[data-action="openFilePicker"]').unbind( "click" ).on('click',function(){
                $('#myModal').find('.modal-body').html('<iframe class="m-dialog__content" src="'+dialogURL+'"></iframe>');
                $('#myModal').modal('toggle');
            });
            $(this).find('[data-action=remove]').unbind( "click" ).on('click',function(){
                $('#'+fieldid).val('');
                updatePlaceholderImage(fieldid);
            });

        });
    };

    var updatePlaceholderImage = function(fieldid){
        var filePicker = $('[data-widget="edcoms-file-picker"][data-input-id="'+fieldid+'"]');
        var placeholder = $(filePicker).find('[data-item="placeholder"]');
        var placeTitle = $(filePicker).find('[data-item="file-title"]');

        var filePath = $('#'+fieldid).val();
        var placeholderPath = filePicker.attr('data-default-placeholder');
        var extension = filePath.split('.').pop();
        var imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp'];
        if(filePath){
            if(imageExtensions.indexOf(extension)!==-1){
                placeholderPath = filePath;
            }else{
                placeholderPath = filePicker.attr('data-default-file-placeholder');
            }
            placeTitle.html(filePath.split('/').pop());
        }else{
            placeTitle.html('');
        }
        placeholder.attr('src', placeholderPath);
    };

    var getContent = function(){
        return content;
    };

    return {
        bindFilePicker: bindFilePicker,
        updatePlaceholderImage: updatePlaceholderImage,
        getContent: getContent
    }
}();


function responsive_filemanager_callback(field){
    EdcomsContent.updatePlaceholderImage(field);
}

