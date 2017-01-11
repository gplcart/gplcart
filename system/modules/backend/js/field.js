/* global GplCart, Backend  */
(function (GplCart, $) {

    Backend.include.field = {attach: {}};

    /**
     * Makes field values sortable
     * @returns {undefined}
     */
    Backend.include.field.attach.sortable = function () {

        var id,
                weight = {},
                data = {
                    action: 'weight',
                    selected: weight,
                    token: GplCart.settings.token
                },
        selector = $('.field-values tbody');

        if (selector.length === 0) {
            return;
        }

        selector.sortable({
            cursor: 'n-resize',
            handle: '.handle',
            stop: function () {

                $('.field-values tbody tr').each(function (i) {
                    id = $(this).attr('data-field-value-id');
                    weight[id] = i;
                });

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
     * Call attached above methods when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        GplCart.attach(Backend.include.field);
    });


})(GplCart, jQuery);
