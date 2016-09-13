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

    if (jQuery().lightSlider) {
        $('.multi-item-carousel').lightSlider({
            item: 4,
            pager: false,
            autoWidth: false,
            slideMargin: 0
        });
    }

    if (jQuery().lightGallery) {
        $('#lg-gallery').lightGallery({selector: '.item', thumbnail: true, download: false});
    }

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
            data: {action: 'getCartPreviewAjax', token: GplCart.settings.token},
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

    /********************** Product page **********************/

    $('.star-rating.static').tooltip();

    $('form#add-to-cart').unbind('submit').submit(function (e) {

        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: GplCart.settings.urn,
            dataType: 'json',
            data: $(this).serialize(),
            success: function (data) {
                if (typeof data === 'object') {
                    if ('preview' in data) {
                        GplCart.theme.modal(data.preview, 'cart-preview', GplCart.text('Your cart'));
                        $('#cart-quantity-summary').text(data.quantity);
                    }
                }
            },
            error: function () {}
        });
    });

    $('form#add-to-cart [name^="product[options]"]').change(function () {

        var values = $('[name^="product[options]"]:checked, [name^="product[options]"] option:selected').map(function () {
            return this.value;
        }).get();

        $.ajax({
            url: GplCart.settings.base + 'ajax',
            dataType: 'json',
            method: 'post',
            data: {
                token: GplCart.settings.token,
                action: 'switchProductOptionsAjax',
                values: values,
                product_id: GplCart.settings.product.product_id,
            },
            success: function (data) {
                if (!jQuery.isEmptyObject(data)) {
                    
                    var message;
                    var error = false;
                    
                    if ('error' in data && data.error) {
                        error = true;
                        message = GplCart.text('An error occurred');
                    }

                    if ('message' in data && data.message) {
                        message = data.message;
                    }
                    
                    if(message.length){
                        alert(message);
                    }
                    
                    if(error){
                        return false;
                    }

                    if ('combination' in data) {
                        if (data.combination.sku !== '') {
                            $('#sku').text(data.combination.sku);
                        }

                        $('#price').text(data.combination.price);

                        if ('image' in data.combination) {
                            $('#main-image').attr('src', data.combination.image);
                        }
                    }
                }
            },
            error: function (error) {}
        });
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
            var params = {term: request.term, action: 'searchProductsAjax', token: GplCart.settings.token};
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

    $(document).on('click', '.ui-autocomplete .suggestion', function () {
        window.location.href = $(this).attr('data-url');
    });


    /**************** Account ***************/
    $(document).on('change', '#edit-address [name$="[country]"]', function () {

        $.ajax({
            url: GplCart.settings.urn,
            method: 'POST',
            dataType: 'html',
            data: {
                country: $(this).val(),
                token: GplCart.settings.token
            },
            success: function (data) {
                var form = $(data).find('#address-form-wrapper').html();
                $('#address-form-wrapper').html(form);
            }
        });
    });

});

