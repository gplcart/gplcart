(function ($) {

    Backend.include.field = Backend.include.field || {attach: {}};

    /**
     * Makes field values sortable
     * @returns {undefined}
     */
    Backend.include.field.attach.sortable = function () {

        $('.field-values tbody').sortable({
            cursor: 'n-resize',
            handle: '.handle',
            stop: function () {

                var weight = {};
                $('.field-values tbody tr').each(function (i) {
                    var id = $(this).attr('data-field-value-id');
                    weight[id] = i;
                });

                var data = {
                    action: 'weight',
                    selected: weight,
                    token: GplCart.settings.token
                };

                $.ajax({
                    data: data,
                    type: 'POST',
                    url: GplCart.settings.urn,
                    success: function (data) {
                        if ('success' in data) {
                            Backend.ui.alert(data.success, 'success');
                            $.each(weight, function (i, v) {
                                $('tr[data-field-value-id=' + i + ']').find('td .weight').text(v);
                            });
                        }
                    },
                    beforeSend: function () {
                        Backend.ui.loading(true);
                    },
                    complete: function () {
                        Backend.ui.loading(false);
                    }
                });
            }
        });
    };

    /**
     * Adds colorpicker to the field
     * @returns {undefined}
     */
    Backend.include.field.attach.colorpicker = function () {
        $('.input-group.color').colorpicker();
    };

    /**
     * Call attached methods above when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        Backend.init(Backend.include.field);
    });


})(jQuery);
