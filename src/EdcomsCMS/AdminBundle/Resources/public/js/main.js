Admin.setup_select2= function(subject) {
    if (window.SONATA_CONFIG && window.SONATA_CONFIG.USE_SELECT2) {
        Admin.log('[core|setup_select2] configure Select2 on', subject);

        jQuery('select:not([data-sonata-select2="false"])', subject).each(function() {
            var select            = jQuery(this);
            var allowClearEnabled = false;
            var popover           = select.data('popover');

            select.removeClass('form-control');

            if (select.find('option[value=""]').length || select.attr('data-sonata-select2-allow-clear')==='true') {
                allowClearEnabled = true;
            } else if (select.attr('data-sonata-select2-allow-clear')==='false') {
                allowClearEnabled = false;
            }

            select.select2({
                width: function(){
                    // Select2 v3 and v4 BC. If window.Select2 is defined, then the v3 is installed.
                    // NEXT_MAJOR: Remove Select2 v3 support.
                    return Admin.get_select2_width(window.Select2 ? this.element : select);
                },
                dropdownAutoWidth: true,
                minimumResultsForSearch: 10,
                allowClear: allowClearEnabled,
                maximumSelectionSize: select.attr('data-sonata-select2-maximumSelectionSize')==='0' ? 0 : parseInt(select.attr('data-sonata-select2-maximumSelectionSize'))
            });

            if (undefined !== popover) {
                select
                    .select2('container')
                    .popover(popover.options)
                ;
            }
        });
    }
};