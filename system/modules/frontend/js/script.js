GplCart.theme = {
    modal: function (content, id, header) {

        var html = '<div class="modal fade" id="' + id + '">\n\
        <div class="modal-dialog">\n\
        <div class="modal-content">\n\
        <div class="modal-header clearfix">\n\
        <button type="button" class="btn btn-primary pull-right" data-dismiss="modal">\n\
        <i class="fa fa-times"></i></button>';

        if (typeof header !== 'undefined') {
            html += '<h3 class="modal-title pull-left">' + header + '</h3>';
        }

        html += '</div><div class="modal-body">' + content + '</div></div></div>';

        $('.modal').remove();
        $('body').append(html);
        $('#' + id + '').modal('show');
    }
}

$(function () {

    // Add Js enabled class
    $('body').addClass('js');

    // Deal with W3C validator warnings
    $('label.btn > input[type="radio"]').attr('autocomplete', 'off');

    /********************** Carousel **********************/
    
    $('.multi-item-carousel').lightSlider({
        item: 4,
        pager: false,
        autoWidth: false,
        slideMargin: 0
    });

    $('.carousel').carousel();

    // Make whole fullscreen carousel clickable except pager
    $('.fullscreen-carousel .item.clickable').click(function (e) {
        if ($(e.target).closest('.carousel-indicators').length === 0) {
            window.location.href = $(this).data('url');
        }
    });

    // ...and text area
    $('.fullscreen-carousel .description .content').click(function (e) {
        e.stopPropagation();
    });

    // Fix catalog equal height
    $('.products .thumbnail .title, label.address').matchHeight();
    

    /********************** Cart **********************/

    $('#cart-link').click(function () {

        $.ajax({
            type: 'POST',
            url: GplCart.settings.base + 'ajax',
            dataType: 'json',
            data: {action: 'getCartPreview', token: GplCart.settings.token},
            success: function (data) {
                if (typeof data === 'object') {
                    if ('preview' in data) {
                        GplCart.theme.modal(data.preview, 'cart-preview', GplCart.text('Cart'));
                    }
                }
            },
            error: function () {}
        });

        return false;
    });

    /********************** Product comparison **********************/

    $('#compare-difference').change(function () {
        if ($(this).is(':checked')) {

            $('table.compare tr.togglable').each(function () {

                var row = this;
                var values = $('.value', this).map(function () {
                    return $(this).text();
                });

                var count = 0;
                $(values).each(function () {
                    if (this === values[0]) {
                        count++;
                    }
                });

                if (values.length > 0 && count === values.length) {
                    $(row).hide();
                }
            });
        } else {
            $('table.compare tr.togglable').show();
        }
    });

    /********************** Search **********************/

    // Prevent submit empty field
    $('form.search').submit(function () {
        if ($('input[name="q"]').val() === "") {
            return false;
        }
    });

    var searchInput = $('input.typeahead');
    
    searchInput.autocomplete({
        minLength: 2,
        source: function (request, response) {
            var params = {term: request.term, action: 'searchProducts', token: GplCart.settings.token};
            $.post(GplCart.settings.base + 'ajax', params, function (data) {
                response($.map(data, function (value) {
                    return {suggestion: value.rendered}
                }));
            });
        },
        select: function (event, ui) {
            return false;
        },
        position: {
            my: 'right top',
            at: 'right bottom'
        },
    }).autocomplete('instance')._renderItem = function (ul, item) {
        return $('<li>').append(item.suggestion).appendTo(ul);
    };
    
    // Retain searching on focus
    searchInput.focus(function () {
        if ($(this).val()) {
            $(this).autocomplete("search");
        }
    });
    
    $(document).on('click', '.ui-autocomplete .suggestion', function(){
        window.location.href = $(this).attr('data-url');
    });

});

