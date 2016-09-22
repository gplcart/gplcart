/* global GplCart, Backend  */
(function ($) {

    Backend.include.collection = Backend.include.collection || {attach: {}};

    Backend.include.collection.attach.autocomplete = function () {

        var input = $('form#edit-collection-item input[name$="[input]"]');
        var value = $('form#edit-collection-item input[name$="[value]"]');

        input.autocomplete({
            minLength: 2,
            source: function (request, response) {
                
                var params = {
                    term: request.term,
                    token: GplCart.settings.token,
                    action: 'getCollectionItemAjax',
                    collection_id: GplCart.settings.collection.collection_id
                };
                
                $.post(GplCart.settings.base + 'ajax', params, function (data) {
                    response($.map(data, function (value, key) {
                        
                        var result = {
                            value: key,
                            label: value.title ? value.title + ' (' + key + ')' : '--'
                        };
                        
                        return result;
                    }));
                });
            },
            select: function (event, ui) {
                input.val(ui.item.label);
                value.val(ui.item.value);
                return false;
            },
            search: function () {
                value.val('');
            }
        }).autocomplete("instance")._renderItem = function (ul, item) {
            return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
        };
    }

    /**
     * Call attached above methods when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        GplCart.attach(Backend.include.collection);
    });

})(jQuery);

