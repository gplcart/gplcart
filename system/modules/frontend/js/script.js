/* global GplCart */
(function (window, document, GplCart, $) {

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

        var html = '',
                url = GplCart.settings.base + 'compare',
                title = GplCart.text('Already in comparison');

        html += '<a title="' + title + '" href="' + url + '" class="btn btn-default active">';
        html += '<i class="fa fa-balance-scale"></i></a>';

        return html;
    };

    /**
     * Returns HTML of "In wishlist" button
     * @returns {String}
     */
    Frontend.html.buttonInWishlist = function () {

        var html = '',
                url = GplCart.settings.base + 'wishlist',
                title = GplCart.text('Already in wishlist');

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
     * Sets up lightSlider
     * @returns {undefined}
     */
    Frontend.attach.slider = function () {

        if (!$.fn.lightSlider) {
            return;
        }

        var slider = $('[data-slider="true"]'),
                settings,
                gSettings;

        slider.each(function () {

            settings = $(this).data('slider-settings') || {};
            gSettings = $(this).data('slider-gallery');

            if (!$.isEmptyObject(gSettings) && $.fn.lightGallery) {
                settings.onSliderLoad = function (gallery) {
                    gallery.lightGallery(gallery.data('slider-gallery'));
                };
            }

            $(this).lightSlider(settings);
        });
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

        var post = {
            action: 'getCartPreviewAjax',
            token: GplCart.settings.token
        };

        $('#cart-link').click(function () {

            $.ajax({
                type: 'POST',
                url: GplCart.settings.base + 'ajax',
                dataType: 'json',
                data: post,
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

        var button, action, header = '';

        $(':button[name][data-ajax="true"]').click(function (e) {

            e.preventDefault();

            button = $(this);
            action = button.attr('name');

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

        var element, input, data, values, wrapper;

        $('form.add-to-cart [name^="product[options]"]').change(function () {

            element = $(this);
            input = '[name^="product[options]"]:checked, [name^="product[options]"] option:selected';

            values = $(input).map(function () {
                return this.value;
            }).get();

            data = {
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

                    wrapper = $('#combination-message');
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

        var row, values, count = 0;

        $('#compare-difference').change(function () {

            if ($(this).not(':checked')) {
                $('table.compare tr.togglable').show();
                return;
            }

            $('table.compare tr.togglable').each(function () {

                row = this;

                values = $('.value', this).map(function () {
                    return $(this).text();
                });

                count = 0;
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

        var data, form,
                input = '#edit-address [name$="[country]"]',
                wrapper = '#address-form-wrapper';

        $(document).on('change', input, function () {

            data = {
                country: $(this).val(),
                token: GplCart.settings.token
            };

            $.ajax({
                data: data,
                method: 'POST',
                dataType: 'html',
                url: GplCart.settings.urn,
                success: function (data) {
                    form = $(data).find(wrapper).html();
                    $(wrapper).html(form);
                    Frontend.helper.cityAutocomplete();
                }
            });
        });
    };

    /**
     * Search autocomplete field
     * @returns {undefined}
     */
    Frontend.attach.searchAutocomplete = function () {

        var params,
                url = GplCart.settings.base + 'ajax',
                input = $('input[name="q"]');

        if (input.length === 0) {
            return;
        }

        input.autocomplete({
            minLength: 2,
            source: function (request, response) {

                params = {
                    term: request.term,
                    action: 'searchProductsAjax',
                    token: GplCart.settings.token
                };

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
     * Add state change listener that updates city autocomplete field
     * @returns {undefined}
     */
    Frontend.attach.cityAutocomplete = function () {
        $(document).on('change', '[name="address[state_id]"]', function () {
            Frontend.helper.cityAutocomplete();
        });
    };

    /**
     * City autocomplete field handler
     * @returns {undefined}
     */
    Frontend.helper.cityAutocomplete = function () {

        var params,
                url = GplCart.settings.base + 'ajax',
                city = $('[name="address[city_id]"]'),
                country = $('[name="address[country]"]'),
                state_id = $('[name="address[state_id]"]');

        if (city.length === 0 || country.length === 0 || state_id.length === 0) {
            return;
        }

        city.val('');

        city.autocomplete({
            minLength: 2,
            source: function (request, response) {

                params = {
                    country: country.val(),
                    state_id: state_id.val(),
                    action: 'searchCityAjax',
                    token: GplCart.settings.token
                };

                $.post(url, params, function (data) {
                    response($.map(data, function (value, key) {
                        return {name: value.name};
                    }));
                });
            },
            select: function (event, ui) {
                city.val(ui.item.name);
                return false;
            }
        }).autocomplete('instance')._renderItem = function (ul, item) {
            return $('<li>').append('<a>' + item.name + '</a>').appendTo(ul);
        };
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

        var clicked, queueSubmit, settings;

        $(document).on('focus', 'form#checkout [name$="[quantity]"]', function () {
            clearTimeout(queueSubmit);
        });

        $(document).on('blur', 'form#checkout [name$="[quantity]"]', function () {
            queueSubmit = setTimeout(function () {
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

                    settings = $(data).data('settings');

                    if (typeof settings === "object" && settings.quantity) {
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

})(window, document, GplCart, jQuery);