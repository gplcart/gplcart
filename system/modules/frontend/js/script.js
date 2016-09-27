(function ($) {

    /* global GplCart, Frontend */
    var Frontend = Frontend || {html: {}, ui: {}, helper: {}, attach: {}};

    /**
     * Returns HTML of modal pop-up
     * @param {String} content
     * @param {String} id
     * @param {String} header
     * @returns {String}
     */
    Frontend.html.modal = function (content, id, header) {

        var html = '';

        html = '<div class="modal fade" id="' + id + '">';
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
    };

    /**
     * Returns HTML of "In comparison" button
     * @returns {String}
     */
    Frontend.html.buttonInCompare = function () {

        var url = GplCart.settings.base + 'compare';
        var title = GplCart.text('Already in comparison');

        var html = '';
        html += '<a title="' + title + '" href="' + url + '" class="btn btn-default active">';
        html += '<i class="fa fa-balance-scale"></i></a>';

        return html;
    };

    /**
     * Returns HTML of "In wishlist" button
     * @returns {String}
     */
    Frontend.html.buttonInWishlist = function () {

        var url = GplCart.settings.base + 'wishlist';
        var title = GplCart.text('Already in wishlist');

        var html = '';
        html += '<a title="' + title + '" href="' + url + '" class="btn btn-default active">';
        html += '<i class="fa fa-heart"></i></a>';

        return html;
    };

    /**
     * Displays a modal popup
     * @param {String} content
     * @param {String} id
     * @param {String} header
     * @returns {undefined}
     */
    Frontend.ui.modal = function (content, id, header) {

        var html = Frontend.html.modal(content, id, header);

        $('.modal').remove();
        $('body').append(html);
        $('#' + id).modal('show');
    };

    /**
     * Deal with W3C validator warnings
     * @returns {undefined}
     */
    Frontend.attach.fixRadio = function () {
        $('label.btn > input[type="radio"]').attr('autocomplete', 'off');
    };

    /**
     * Setup multi-item (chunked) slider
     * @returns {undefined}
     */
    Frontend.attach.multiSlider = function () {

        if (!$.fn.lightSlider) {
            return;
        }

        var settings = {
            item: 4,
            pager: false,
            autoWidth: false,
            slideMargin: 0
        };

        $('.multi-item-carousel').lightSlider(settings);

    };

    /**
     * Sets up lightSlider
     * @returns {undefined}
     */
    Frontend.attach.slider = function () {

        if (!$.fn.lightSlider) {
            return;
        }

        var settings = {
            auto: false,
            loop: true,
            pager: false,
            autoWidth: true,
            pauseOnHover: true,
            item: 2,
        };

        $('.collection-file .slider').lightSlider(settings);
    };

    /**
     * Setup image gallery
     * @returns {undefined}
     */
    Frontend.attach.gallery = function () {

        if (!$.fn.lightGallery) {
            return;
        }

        var settings = {
            selector: '.item',
            thumbnail: true,
            download: false
        };

        $('#lg-gallery').lightGallery(settings);
    };

    /**
     * Fix equal height of items
     * @returns {undefined}
     */
    Frontend.attach.equalHeight = function () {

        if (!$.fn.matchHeight) {
            return;
        }

        var selector = '.products .thumbnail .title, label.address';
        $(selector).matchHeight();
    };

    /**
     * Loads cart preview on demand
     * @returns {undefined}
     */
    Frontend.attach.cartPreview = function () {
        $('#cart-link').click(function () {

            var data = {
                action: 'getCartPreviewAjax',
                token: GplCart.settings.token
            };

            $.ajax({
                type: 'POST',
                url: GplCart.settings.base + 'ajax',
                dataType: 'json',
                data: data,
                success: function (data) {
                    if (data.preview) {
                        Frontend.ui.modal(data.preview, 'cart-preview', GplCart.text('Cart'));
                    }
                },
                error: function () {
                    alert(GplCart.text('An error occurred'));
                }
            });

            return false;
        });
    };

    /**
     * Setup Bootstrap tooltips
     * @returns {undefined}
     */
    Frontend.attach.tooltip = function () {
        $('.star-rating.static').tooltip();
    };

    /**
     * Handles various submit events
     * @returns {undefined}
     */
    Frontend.attach.submit = function () {

        $(':button[name][data-ajax="true"]').click(function (e) {

            e.preventDefault();

            var button = $(this);
            var action = button.attr('name');

            if (!action) {
                return false;
            }

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: GplCart.settings.urn,
                data: button.closest('form').serialize() + '&' + action + '=1',
                success: function (data) {

                    if (typeof data !== 'object') {
                        return false;
                    }

                    if (data.redirect) {
                        window.location.replace(GplCart.settings.base + data.redirect);
                        return false;
                    }

                    if (data.modal) {

                        var header = '';
                        if (action === 'add_to_cart') {
                            header = GplCart.text('Cart');
                        }

                        Frontend.ui.modal(data.modal, action + '-content-modal', header);
                    } else if (data.message) {
                        Frontend.ui.modal(data.message, action + '-message-modal');
                    }

                    Frontend.helper.submit(action, button, data);
                },
                error: function () {
                    alert(GplCart.text('An error occurred'));
                }
            });

            return false;
        });
    };

    /**
     * Handles submitted actions
     * @param {String} action
     * @param {Object} button
     * @param {Object} data
     * @returns {undefined}
     */
    Frontend.helper.submit = function (action, button, data) {
        if (data.severity === 'success') {
            Frontend.helper.submitAddToCart(action, button, data);
            Frontend.helper.submitAddToCompare(action, button, data);
            Frontend.helper.submitAddToWishlist(action, button, data);
            Frontend.helper.submitRemoveFromWishlist(action, button, data);
        }
    };

    /**
     * Handles "Add to cart" action
     * @param {String} action
     * @param {Object} button
     * @param {Object} data
     * @returns {undefined}
     */
    Frontend.helper.submitAddToCart = function (action, button, data) {
        if (action === 'add_to_cart' && 'quantity' in data) {
            Frontend.helper.updateCartQuantity(data.quantity);
        }
    };

    /**
     * Inserts a number of cart items into a HTML element
     * @param {Integer} quantity
     * @returns {undefined}
     */
    Frontend.helper.updateCartQuantity = function (quantity) {
        $('#cart-quantity').text(quantity).show();
    };

    /**
     * Handles "Add to compare" action
     * @param {String} action
     * @param {Object} button
     * @param {Object} data
     * @returns {undefined}
     */
    Frontend.helper.submitAddToCompare = function (action, button, data) {
        if (action === 'add_to_compare' && 'quantity' in data) {
            $('#compare-quantity').text(data.quantity).show();
            button.replaceWith(Frontend.html.buttonInCompare());
        }
    };

    /**
     * Handles "Add to wishlist" action
     * @param {String} action
     * @param {Object} button
     * @param {Object} data
     * @returns {undefined}
     */
    Frontend.helper.submitAddToWishlist = function (action, button, data) {
        if (action === 'add_to_wishlist' && 'quantity' in data) {
            Frontend.helper.updateWishlistQuantity(data.quantity);
            button.replaceWith(Frontend.html.buttonInWishlist());
        }
    };



    /**
     * Handles "Remove from wishlist" action
     * @param {String} action
     * @param {Object} button
     * @param {Object} data
     * @returns {undefined}
     */
    Frontend.helper.submitRemoveFromWishlist = function (action, button, data) {
        if (action === 'remove_from_wishlist' && 'quantity' in data) {
            Frontend.helper.updateWishlistQuantity(data.quantity);
            button.closest('.product.item').remove();
        }
    };

    /**
     * Inserts a number of wishlist items into a HTML element
     * @param {Integer} quantity
     * @returns {undefined}
     */
    Frontend.helper.updateWishlistQuantity = function (quantity) {
        $('#wishlist-quantity').text(quantity).show();
    };

    /**
     * Handles changing product options
     * @returns {undefined}
     */
    Frontend.attach.updateOptions = function () {

        $('form.add-to-cart [name^="product[options]"]').change(function () {

            var element = $(this);
            var input = '[name^="product[options]"]:checked, [name^="product[options]"] option:selected';

            var values = $(input).map(function () {
                return this.value;
            }).get();

            var data = {
                values: values,
                token: GplCart.settings.token,
                action: 'switchProductOptionsAjax',
                product_id: GplCart.settings.product.product_id
            };

            $.ajax({
                url: GplCart.settings.base + 'ajax',
                dataType: 'json',
                method: 'post',
                data: data,
                success: function (data) {

                    if ($.isEmptyObject(data)) {
                        alert(GplCart.text('An error occurred'));
                        return false;
                    }

                    var wrapper = $('#combination-message');
                    wrapper.empty().hide();

                    if (data.message) {
                        wrapper.toggleClass('text-' + data.severity).html(data.message).show();
                    }

                    if (data.modal) {
                        Frontend.ui.modal(data.modal, 'product-update-option');
                    }

                    if (!data.combination) {
                        return false;
                    }

                    $('#price').text(data.combination.price_formatted);

                    if (data.combination.sku) {
                        $('#sku').text(data.combination.sku);
                    }

                    if (data.combination.thumb) {
                        $('#main-image').attr('src', data.combination.thumb);
                    }

                    element.closest('form').find('[name="add_to_cart"]').prop('disabled', !data.cart_access);

                },
                error: function () {
                    alert(GplCart.text('An error occurred'));
                }
            });
        });
    };

    /**
     * Shows only rows with different values
     * @returns {undefined}
     */
    Frontend.attach.compareDiff = function () {
        $('#compare-difference').change(function () {

            if ($(this).not(':checked')) {
                $('table.compare tr.togglable').show();
                return;
            }

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
        });
    };

    /**
     * Prevents search for empty keyword
     * @returns {undefined}
     */
    Frontend.attach.searchBlockEmpty = function () {
        $('form.search').submit(function () {
            if ($('input[name="q"]').val() === "") {
                return false;
            }
        });
    };

    /**
     * Updates address fields depending on chosen country
     * @returns {undefined}
     */
    Frontend.attach.updateAdressFields = function () {
        $(document).on('change', '#edit-address [name$="[country]"]', function () {

            var data = {
                country: $(this).val(),
                token: GplCart.settings.token
            };

            $.ajax({
                data: data,
                method: 'POST',
                dataType: 'html',
                url: GplCart.settings.urn,
                success: function (data) {
                    var form = $(data).find('#address-form-wrapper').html();
                    $('#address-form-wrapper').html(form);
                }
            });
        });
    };

    /**
     * Search autocomplete field
     * @returns {undefined}
     */
    Frontend.attach.searchAutocomplete = function () {

        var input = $('input[name="q"]');

        input.autocomplete({
            minLength: 2,
            source: function (request, response) {

                var params = {
                    term: request.term,
                    action: 'searchProductsAjax',
                    token: GplCart.settings.token
                };

                var url = GplCart.settings.base + 'ajax';

                $.post(url, params, function (data) {
                    response($.map(data, function (value, key) {
                        return {suggestion: value.rendered};
                    }));
                });
            },
            select: function () {
                return false;
            }
        }).autocomplete('instance')._renderItem = function (ul, item) {
            return $('<li>').append('<a>' + item.suggestion + '</a>').appendTo(ul);
        };

        // Retain searching on focus
        input.focus(function () {
            if ($(this).val()) {
                $(this).autocomplete("search");
            }
        });
    };

    /**
     * Redirects to a page when clicked on the suggested item
     * @returns {undefined}
     */
    Frontend.attach.redirectSuggestions = function () {
        $(document).on('click', '.ui-autocomplete .suggestion', function () {
            window.location.href = $(this).attr('data-url');
        });
    };


    /**
     * Handles checkout form submits
     * @returns {undefined}
     */
    Frontend.attach.submitCheckout = function () {

        var clicked, queueSubmit;

        $(document).on('focus', 'form#checkout [name$="[quantity]"]', function () {
            clearTimeout(queueSubmit);
        });

        $(document).on('blur', 'form#checkout [name$="[quantity]"]', function () {
            queueSubmit = setTimeout(function () {

                $('form#checkout').append($("<input type='hidden'>").attr({name: 'update', value: 1}));
                $('form#checkout').submit();

            }, 100);
        });

        $(document).on('change', 'form#checkout input[type="radio"], form#checkout select', function () {
            $('form#checkout').submit();
        });

        $(document).on('click', 'form#checkout :submit', function (e) {
            clicked = $(this).attr('name');
            $(this).closest('form').append($("<input type='hidden'>").attr({name: $(this).attr('name'), value: $(this).attr('value')}));
        });

        $(document).off('submit').on('submit', 'form#checkout', function (e) {

            if (clicked === 'save' || clicked === 'login') {
                return true;
            }

            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: GplCart.settings.urn,
                dataType: 'html',
                data: $('form#checkout').serialize(),
                beforeSend: function () {
                    $('form#checkout :input').prop('disabled', true);
                },
                success: function (data) {
                    
                    if (!data.length) {
                        return;
                    }
                        
                    $('#checkout-form-wrapper').html(data);

                    var settings = $(data).data('settings');

                    if(typeof settings === "object" && settings.quantity){
                        Frontend.helper.updateWishlistQuantity(settings.quantity.wishlist);
                    }

                    $('form#checkout :input').prop('disabled', false);
                }
            });
        });
    };

    /**
     * Init the module when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        GplCart.attach(Frontend);
    });

})(jQuery);