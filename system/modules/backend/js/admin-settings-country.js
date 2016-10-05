/* global GplCart, Backend */
(function (GplCart, $) {

    Backend.include.country = Backend.include.country || {attach: {}};

    /**
     * Checks status checkbox when the corresponding "required" checkbox is checked
     * @returns {undefined}
     */
    Backend.include.country.attach.checkStatus = function () {
        $('table.country-format input[name$="[required]"]').click(function () {
            if ($(this).is(':checked')) {
                $(this).closest('tr').find('input[name$="[status]"]').prop('checked', true);
            }
        });
    };

    /**
     * Makes country format items sortable
     * @returns {undefined}
     */
    Backend.include.country.attach.sortableFormat = function () {
        $('table.country-format tbody').sortable({
            cursor: 'n-resize',
            handle: '.handle',
            stop: function () {
                $('input[name$="[weight]"]').each(function (i) {
                    $(this).val(i);
                    $(this).closest('tr').find('td .weight').text(i);
                });

                Backend.ui.alert(GplCart.text('Changes will not be saved until the form is submitted'), 'info');
            }
        });
    };

    /**
     * Call attached above methods when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        GplCart.attach(Backend.include.country);
    });

})(GplCart, jQuery);