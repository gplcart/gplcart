/* global GplCart, Backend */
(function ($) {

    Backend.include.review = Backend.include.review || {attach: {}};

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

        var inputName = $('#edit-review input[name$="[email]"], #reviews input[name="email"]');
        var inputId = $('#edit-review input[name$="[user_id]"], #reviews input[name="user_id"]');

        inputName.autocomplete({
            minLength: 2,
            source: function (request, response) {

                var params = {
                    term: request.term,
                    action: 'getUsersAjax',
                    token: GplCart.settings.token
                };

                $.post(GplCart.settings.base + 'ajax', params, function (data) {

                    response($.map(data, function (value, key) {

                        var result = {
                            value: value.email,
                            label: value.email
                        };

                        return result;
                    }));
                });
            },
            select: function (event, ui) {
                inputName.val(ui.item.label);
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

        var inputName = $('#edit-review [name$="[product]"], #reviews input.product');
        var inputId = $('#edit-review [name$="[product_id]"], #reviews [name="product_id"]');

        inputName.autocomplete({
            minLength: 2,
            source: function (request, response) {

                var params = {
                    term: request.term,
                    action: 'getProductsAjax',
                    token: GplCart.settings.token
                };

                $.post(GplCart.settings.base + 'ajax', params, function (data) {

                    response($.map(data, function (value, key) {

                        var result = {
                            value: value.product_id,
                            label: value.title ? value.title + ' (' + value.product_id + ')' : '--'
                        };

                        return result;
                    }));
                });
            },
            select: function (event, ui) {
                inputName.val(ui.item.label);
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

})(jQuery);
