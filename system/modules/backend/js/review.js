/* global GplCart, Backend */
(function (GplCart, $) {

    Backend.include.review = {attach: {}};

    /**
     * Adds a datepicker popup to the field
     * @returns {undefined}
     */
    Backend.include.review.attach.datepicker = function () {
        $('form#edit-review input[name$="[created]"]').datepicker({dateFormat: 'dd.mm.yy'});
    };

    /**
     * Adds autocomplete functionality to the user input
     * @returns {undefined}
     */
    Backend.include.review.attach.autocompleteUser = function () {

        var params,
                input = $('#edit-review input[name$="[email]"], #reviews input[name="email"]'),
                inputId = $('#edit-review input[name$="[user_id]"], #reviews input[name="user_id"]');

        if (input.length === 0) {
            return;
        }

        input.autocomplete({
            minLength: 2,
            source: function (request, response) {

                params = {
                    term: request.term,
                    action: 'getUsersAjax',
                    token: GplCart.settings.token
                };

                $.post(GplCart.settings.base + 'ajax', params, function (data) {
                    response($.map(data, function (value, key) {
                        return {
                            value: value.email,
                            label: value.email
                        };
                    }));
                });
            },
            select: function (event, ui) {
                input.val(ui.item.label);
                inputId.val(ui.item.value);
                return false;
            }
        }).autocomplete("instance")._renderItem = function (ul, item) {
            return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
        };
    };

    /**
     * Adds autocomplete functionality to the product input
     * @returns {undefined}
     */
    Backend.include.review.attach.autocompleteProduct = function () {

        var params,
                input = $('#edit-review [name$="[product]"], #reviews input.product'),
                inputId = $('#edit-review [name$="[product_id]"], #reviews [name="product_id"]');

        if (input.length === 0) {
            return;
        }

        input.autocomplete({
            minLength: 2,
            source: function (request, response) {

                params = {
                    term: request.term,
                    action: 'getProductsAjax',
                    token: GplCart.settings.token
                };

                $.post(GplCart.settings.base + 'ajax', params, function (data) {

                    response($.map(data, function (value, key) {
                        return {
                            value: value.product_id,
                            label: value.title ? value.title + ' (' + value.product_id + ')' : '--'
                        };
                    }));
                });
            },
            select: function (event, ui) {
                input.val(ui.item.label);
                inputId.val(ui.item.value);
                return false;
            },
            search: function () {
                inputId.val('');
            }
        }).autocomplete("instance")._renderItem = function (ul, item) {
            return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
        };
    };

    /**
     * Call attached above methods when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        GplCart.attach(Backend.include.review);
    });

})(GplCart, jQuery);