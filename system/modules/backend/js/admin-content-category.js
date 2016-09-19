(function ($) {
    
    Backend.include.category = Backend.include.category || {attach: {}};

    /**
     * Makes categories sortable
     * @returns {undefined}
     */
    Backend.include.category.attach.sortable = function () {

        $('table.categories tbody').sortable({
            cursor: 'n-resize',
            handle: '.handle',
            stop: function () {

                var weight = {};
                $('table.categories tbody tr').each(function (i) {
                    var id = $(this).attr('data-category-id');
                    weight[id] = i;
                });

                var params = {
                    action: 'weight',
                    selected: weight,
                    token: GplCart.settings.token
                };

                $.ajax({
                    data: params,
                    type: 'POST',
                    url: GplCart.settings.urn,
                    success: function (data) {

                        if ('success' in data) {
                            Backend.ui.alert(data.success, 'success');
                            // update visible weight values
                            $.each(weight, function (i, v) {
                                $('tr[data-category-id=' + i + ']').find('td .weight').text(v);
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
     * Call attached methods above when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        Backend.init(Backend.include.category);
    });

})(jQuery);