/* global GplCart, Backend */
(function ($) {

    Backend.include.page = Backend.include.page || {attach: {}, helper: {}};

    /**
     * Refreshes selectpicker state
     * @param {Object} selector
     * @returns {undefined}
     */
    Backend.include.page.helper.selectpickerRefresh = function (selector) {
        selector.selectpicker('refresh');
    }

    /**
     * Updates categories depending on chosen store
     * @returns {undefined}
     */
    Backend.include.page.attach.updateCategories = function () {

        var store = $('select[name$="[store_id]"]');
        var category = $('select[name$="[category_id]"]');

        store.change(function () {

            var storeId = $(this).find('option:selected').val();

            var data = {
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
                success: function (data) {
                    if (typeof data === 'object') {

                        var options = '';
                        for (var g in data) {
                            options += '<optgroup label="' + g + '">';
                            var cats = data[g];
                            for (var i in cats) {
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

})(jQuery);