/**
 * Displays modal popup
 * @param {type} content
 * @param {type} id
 * @param {type} header
 * @returns {undefined}
 */
GplCart.theme.modal = function (content, id, header) {

    var html = GplCart.theme.html.modal(content, id, header);

    $('.modal').remove();
    $('body').append(html);
    $('#' + id + '').modal('show');
}

GplCart.theme.html = {};

/**
 * Returns HTML of modal pop-up
 * @param {type} content
 * @param {type} id
 * @param {type} header
 * @returns {String}
 */
GplCart.theme.html.modal = function(content, id, header){
    
    var html = '<div class="modal fade" id="' + id + '">';
        html += '<div class="modal-dialog">';
        html += '<div class="modal-content">';
        html += '<div class="modal-header clearfix">';
        html += '<a href="#" class="pull-right" data-dismiss="modal">';
        html += '<i class="fa fa-times"></i></a>';

    if (typeof header !== 'undefined') {
        html += '<h4 class="modal-title pull-left">' + header + '</h4>';
    }

    html += '</div><div class="modal-body">' + content + '</div></div></div>';
    return html;
}

/**
 * 
 * @returns {String}
 */
GplCart.theme.html.compare = function(){
    return '<a title="' + GplCart.text('Already in comparison') + '" href="' + GplCart.settings.base + 'compare" class="btn btn-default active"><i class="fa fa-balance-scale"></i></a>';
}

/**
 * 
 * @returns {String}
 */
GplCart.theme.html.wishlist = function(){
    return '<a title="' + GplCart.text('Already in wishlist') + '" href="' + GplCart.settings.base + 'wishlist" class="btn btn-default active"><i class="fa fa-heart"></i></a>';
}

/**
 * 
 * @param {type} action
 * @param {type} button
 * @param {type} data
 * @returns {undefined}
 */
GplCart.theme.submit = function(action, button, data){
    
    if (data.severity === 'success') {
        switch (action) {
            case 'add_to_compare':
                $('#compare-quantity').text(data.quantity).show();
                button.replaceWith(GplCart.theme.html.compare);
                break;
            case 'add_to_compare':
                $('#wishlist-quantity').text(data.quantity).show();
                button.replaceWith(GplCart.theme.html.wishlist);
                break;
        }
    }
}

$(function () {

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
    
    $(':button[name][data-ajax="true"]').click(function (e) {
        
        e.preventDefault();
        
        var button = $(this);
        var action = button.attr('name');
        
        if(!action){
            return false;
        }
        
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: GplCart.settings.urn,
            data: button.closest('form').serialize() + '&' + action + '=1',
            success: function (data) {
                
                if (typeof data !== 'object') {
                    alert(GplCart.text('An error occurred'));
                    return false;
                }
                
                if('redirect' in data && data.redirect.length){
                    window.location.replace(data.redirect);
                    return false;
                }
                
                if ('modal' in data) {
                    GplCart.theme.modal(data.modal, 'cart-preview', GplCart.text('Your cart'));
                } else if('message' in data){
                    GplCart.theme.modal(data.message, 'cart-message');
                }

                GplCart.theme.submit(action, button, data);
            },
            error: function () {
                alert(GplCart.text('An error occurred'));
            }
        });
        
        return false;
    });

    $('form.product-action [name^="product[options]"]').change(function () {

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
                
                if (jQuery.isEmptyObject(data)) {
                    alert(GplCart.text('An error occurred'));
                    return false;
                }
                    
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
            },
            error: function (request, status, error) {
                alert('Error: ' + request.responseText);
            }
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

