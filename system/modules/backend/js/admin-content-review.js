$(function () {

    /******************************** Review edit form ********************************/

    $('form#edit-review input[name$="[created]"]').datepicker({dateFormat: 'dd.mm.yy'});

    var email_input = $('form#edit-review input[name$="[email]"]');

    email_input.autocomplete({
        minLength: 2,
        source: function (request, response) {

            $.post(GplCart.settings.base + 'ajax', {
                term: request.term,
                action: 'getUsers',
                token: GplCart.settings.token}, function (data) {

                response($.map(data, function (value, key) {
                    return {
                        label: value.name + ' (' + value.email + ')',
                        value: value.email
                    }
                }));
            });
        },
        select: function (event, ui) {
            email_input.val(ui.item.value);
            return false;
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
    };

    var product_input = $('form#edit-review input[name$="[product]"]');
    var product_id_input = $('form#edit-review input[name$="[product_id]"]');

    product_input.autocomplete({
        minLength: 2,
        source: function (request, response) {

            $.post(GplCart.settings.base + 'ajax', {
                term: request.term,
                action: 'getProductsAjax',
                token: GplCart.settings.token}, function (data) {

                response($.map(data, function (value, key) {
                    return {
                        label: value.title ? value.title + ' (' + value.product_id + ')' : '--',
                        value: value.product_id
                    }
                }));
            });
        },
        select: function (event, ui) {
            product_input.val(ui.item.label);
            product_id_input.val(ui.item.value);
            return false;
        },
        search: function (event, ui) {
            product_id_input.val('');
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
    };

    /******************************** Reviews overview ********************************/

    $('#reviews input[name="user"]').autocomplete({
        minLength: 2,
        source: function (request, response) {

            $.post(GplCart.settings.base + 'ajax', {
                term: request.term,
                action: 'getUsers',
                token: GplCart.settings.token}, function (data) {

                response($.map(data, function (value, key) {
                    return {
                        label: value.name + ' (' + value.email + ')',
                        value: value.user_id
                    };
                }));
            });
        },
        select: function (event, ui) {
            $('#reviews input[name="user"]').val(ui.item.label);
            $('#reviews input[name="user_id"]').val(ui.item.value);
            return false;
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
    };

    $('#reviews input[name="product"]').autocomplete({
        minLength: 2,
        source: function (request, response) {

            $.post(GplCart.settings.base + 'ajax', {
                term: request.term,
                action: 'getProductsAjax',
                token: GplCart.settings.token}, function (data) {

                response($.map(data, function (value, key) {
                    return {
                        label: value.title ? value.title + ' (' + value.product_id + ')' : '--',
                        value: value.product_id
                    };
                }));
            });
        },
        select: function (event, ui) {
            $('#reviews input[name="product"]').val(ui.item.label);
            $('#reviews input[name="product_id"]').val(ui.item.value);
            return false;
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
    };

});

