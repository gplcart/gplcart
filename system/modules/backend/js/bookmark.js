$(function () {

    $('input[name="user"]').autocomplete({
        minLength: 1,
        source: function (request, response) {
            $.post(GplCart.settings.base + 'ajax',
                    {term: request.term, action: 'getUsers', token: GplCart.settings.token}, function (data) {
                response($.map(data, function (value, key) {
                    return {
                        label: value.email,
                        value: value.user_id
                    };
                }));
            });
        },
        select: function (event, ui) {
            $('input[name="user"]').val(ui.item.label);
            $('input[name="user_id"]').val(ui.item.value);
            return false;
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
    };

    $('input[name="title"]').autocomplete({
        minLength: 1,
        source: function (request, response) {
            $.post(GplCart.settings.base + 'ajax',
                    {term: request.term, action: 'getProducts', token: GplCart.settings.token}, function (data) {
                response($.map(data, function (value, key) {
                    return {
                        label: value.title ? value.title + ' (' + value.product_id + ')' : '--',
                        value: value.product_id
                    };
                }));
            });
        },
        select: function (event, ui) {
            $('input[name="title"]').val(ui.item.label);
            $('input[name="id_value"]').val(ui.item.value);
            return false;
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
    };

    var filterType = $('select[name="type"]');

    if (filterType.val() !== 'product') {
        $('input[name="title"]').autocomplete('disable');
    }

    filterType.change(function () {
        if ($(this).val() === 'product') {
            $('input[name="title"]').autocomplete('enable');
        } else {
            $('input[name="title"]').autocomplete('disable');
        }
    });

});