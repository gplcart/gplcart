/* global GplCart, Backend */
(function (GplCart, $) {

    Backend.include.page = {attach: {}, helper: {}};

    /**
     * Refreshes selectpicker state
     * @param {Object} selector
     * @returns {undefined}
     */
    Backend.include.page.helper.selectpickerRefresh = function (selector) {
        selector.selectpicker('refresh');
    };

    /**
     * Updates categories depending on chosen store
     * @returns {undefined}
     */
    Backend.include.page.attach.updateCategories = function () {

        var i,
                g,
                data,
                cats,
                storeId,
                options = '',
                store = $('select[name$="[store_id]"]'),
                category = $('select[name$="[category_id]"]');

        store.change(function () {

            storeId = $(this).find('option:selected').val();

            data = {
                store_id: storeId,
                action: 'categories',
                token: GplCart.settings.token
            };

            $.ajax({
                url: GplCart.settings.urn,
                method: 'POST',
                dataType: 'json',
                data: data,
                beforeSend: function () {
                    store.prop('disabled', true);
                    category.prop('disabled', true);
                },
                success: function (response) {
                    if (typeof response === 'object') {
                        for (g in response) {
                            options += '<optgroup label="' + g + '">';
                            cats = response[g];
                            for (i in cats) {
                                options += '<option value="' + i + '">' + cats[i] + '</option>';
                            }
                        }

                        category = category.html(options);
                        Backend.include.page.helper.selectpickerRefresh(category);
                    }
                },
                complete: function () {
                    store = store.prop('disabled', false);
                    category = category.prop('disabled', false);
                    Backend.include.page.helper.selectpickerRefresh(store);
                    Backend.include.page.helper.selectpickerRefresh(category);
                }
            });
        });
    };

    /**
     * Call attached above methods when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        GplCart.attach(Backend.include.page);
    });

})(GplCart, jQuery);