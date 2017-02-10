/* global window, document, GplCart, jQuery */
(function (window, document, GplCart, $) {

    "use strict";

    var selected_option_values = [],
            loaded_sliders = {},
            theme_settings = {product_gallery_id: 'product-image-gallery'};

    /**
     * Returns HTML of modal pop-up
     * @param {String} content
     * @param {String} id
     * @param {String} header
     * @returns {String}
     */
    var htmlModal = function (content, id, header) {

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
    var htmlBtnInCompare = function () {

        var html = '';
        html += '<a title="' + GplCart.text('Already in comparison') + '" href="' + GplCart.settings.base + 'compare" class="btn btn-default active">';
        html += '<i class="fa fa-balance-scale"></i>';
        html += '</a>';
        return html;
    };

    /**
     * Returns HTML of "In wishlist" button
     * @returns {String}
     */
    var htmlBtnInWishlist = function () {

        var html = '',
                url = GplCart.settings.base + 'wishlist',
                title = GplCart.text('Already in wishlist');

        html += '<a title="' + title + '" href="' + url + '" class="btn btn-default active">';
        html += '<i class="fa fa-heart"></i></a>';
        return html;
    };

    /**
     * Returns rendered reset field options button
     * @param {String} fid
     * @param {String} title
     * @returns {String}
     */
    var htmlBtnSelectedOptions = function (fid, title) {
        var btn = '';
        btn += '<span title="' + GplCart.text('Remove') + '" data-reset-field-id="' + fid + '" class="btn btn-default btn-xs">';
        btn += title;
        btn += '<span class="fa fa-times"></span>';
        btn += '</span>';
        return btn;
    };

    /**
     * Displays a modal popup
     * @param {String} content
     * @param {String} id
     * @param {String} header
     * @returns {undefined}
     */
    var setModal = function (content, id, header) {

        $('.modal').remove();
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').removeAttr('style');

        if (content.length) {
            $('body').append(htmlModal(content, id, header));
            $('#' + id).modal('show');
        }
    };

    /**
     * Handles "Add to cart" action
     * @param {String} action
     * @param {Object} data
     * @returns {undefined}
     */
    var submitAddToCart = function (action, data) {
        if (action === 'add_to_cart' && 'quantity' in data) {
            updateCartQuantity(data.quantity);
        }
    };

    /**
     * Handles "Remove from cart" action
     * @param {String} action
     * @param {Object} data
     * @returns {undefined}
     */
    var submitRemoveFromCart = function (action, data) {
        if (action === 'remove_from_cart' && 'quantity' in data) {
            updateCartQuantity(data.quantity);
        }
    };

    /**
     * Inserts a number of cart items into a HTML element
     * @param {Integer} quantity
     * @returns {undefined}
     */
    var updateCartQuantity = function (quantity) {
        $('#cart-quantity').text(quantity).show();
    };

    /**
     * Handles "Add to compare" action
     * @param {String} action
     * @param {Object} data
     * @param {Object} button
     * @returns {undefined}
     */
    var submitAddToCompare = function (action, data, button) {
        if (action === 'add_to_compare' && 'quantity' in data) {
            $('#compare-quantity').text(data.quantity).show();
            button.replaceWith(htmlBtnInCompare());
        }
    };

    /**
     * Handles "Add to wishlist" action
     * @param {String} action
     * @param {Object} data
     * @param {Object} button
     * @returns {undefined}
     */
    var submitAddToWishlist = function (action, data, button) {
        if (action === 'add_to_wishlist' && 'quantity' in data) {
            updateWishlistQuantity(data.quantity);
            button.replaceWith(htmlBtnInWishlist());
        }
    };

    /**
     * Handles "Remove from wishlist" action
     * @param {String} action
     * @param {Object} data
     * @param {Object} button
     * @returns {undefined}
     */
    var submitRemoveFromWishlist = function (action, data, button) {
        if (action === 'remove_from_wishlist' && 'quantity' in data) {
            updateWishlistQuantity(data.quantity);
            button.closest('.product.item').remove();
        }
    };

    /**
     * Inserts a number of wishlist items into a HTML element
     * @param {Integer} quantity
     * @returns {undefined}
     */
    var updateWishlistQuantity = function (quantity) {
        $('#wishlist-quantity').text(quantity).show();
    };

    /**
     * Returns arrays of selected field values and their titles
     * @returns object
     */
    var getSelectedOptions = function () {

        var value, values = [], titles = [];

        $('[name^="product[options]"]:checked, [name^="product[options]"] option:selected').each(function () {

            value = $(this).val();

            if (value.length) {
                values.push(value);
                titles.push(htmlBtnSelectedOptions($(this).data('field-id'), $(this).data('field-title')));
            }
        });

        return {values: values, titles: titles};
    };

    /**
     * Uncheck all but current checkboxes in the group
     * @param {Object} current
     * @returns {undefined}
     */
    var setSingleCheckedCheckbox = function (current) {
        $(current).closest('.form-group').find('input:checkbox').not(current).prop('checked', false);
    };

    /**
     * 
     * @param {type} data
     * @returns {undefined}
     */
    var setSelectedMessage = function (data) {
        var text = '';
        if (!$.isEmptyObject(data.titles)) {
            text = GplCart.text('Selected: !combination', {'!combination': data.titles.join(' ')});
        }
        $('.selected-combination').html(text);
    };

    /**
     * City autocomplete field handler
     * @returns {undefined}
     */
    var setCityAutocomplete = function () {

        var params,
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
                    action: 'searchCityAjax',
                    country: country.val(),
                    state_id: state_id.val(),
                    token: GplCart.settings.token
                };

                $.post(GplCart.settings.base + 'ajax', params, function (data) {
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
     * Fix equal height of items
     * @returns {undefined}
     */
    GplCart.onload.equalHeight = function () {
        if ($.fn.matchHeight) {
            $('.products .thumbnail .title').matchHeight();
        }
    };

    /**
     * Deal with W3C validator warnings
     * @returns {undefined}
     */
    GplCart.onload.fixRadio = function () {
        $('label.btn > input[type="radio"]').attr('autocomplete', 'off');
    };

    /**
     * Sets up lightSlider
     * @returns {undefined}
     */
    GplCart.onload.slider = function () {

        if (!$.fn.lightSlider) {
            return;
        }

        var slider_settings, gallery_settings;

        $('[data-slider="true"]').each(function () {
            slider_settings = $(this).data('slider-settings') || {};
            if ($.fn.lightGallery) {
                gallery_settings = $(this).data('gallery-settings') || {};
                slider_settings.onSliderLoad = function (gallery) {
                    gallery.lightGallery(gallery_settings);
                };
            }

            loaded_sliders[$(this).attr('id')] = $(this).lightSlider(slider_settings);
        });
    };

    /**
     * Loads cart preview on demand
     * @returns {undefined}
     */
    GplCart.onload.cartPreview = function () {

        $('#cart-link').click(function () {

            $.ajax({
                type: 'POST',
                url: GplCart.settings.base + 'ajax',
                dataType: 'json',
                data: {
                    action: 'getCartPreviewAjax',
                    token: GplCart.settings.token
                },
                success: function (data) {
                    if (typeof data === 'object' && data.preview) {
                        setModal(data.preview, 'cart-preview', GplCart.text('Cart'));
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
    GplCart.onload.tooltip = function () {
        $('.star-rating.static').tooltip();
    };

    /**
     * Handles various submit events
     * @returns {undefined}
     */
    GplCart.onload.submit = function () {

        var button, action, header;

        $(document).on('click', ':button[name][data-ajax="true"]', function (e) {

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

                    if ('modal' in data) {
                        if (action === 'add_to_cart') {
                            header = GplCart.text('Cart');
                        }
                        setModal(data.modal, action + '-content-modal', header);
                    } else if (data.message) {
                        setModal(data.message, action + '-message-modal');
                    }

                    if (data.severity === 'success') {
                        submitAddToCart(action, data);
                        submitRemoveFromCart(action, data);
                        submitAddToCompare(action, data, button);
                        submitAddToWishlist(action, data, button);
                        submitRemoveFromWishlist(action, data, button);
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
     * Handles changing product options
     * @returns {undefined}
     */
    GplCart.onload.updateOption = function () {

        var slider, image, images,
                selected = getSelectedOptions(),
                message = $('.add-to-cart .message');

        setSelectedMessage(selected);

        $(document).on('change', '[name^="product[options]"]', function () {

            setSingleCheckedCheckbox(this);

            selected = getSelectedOptions();
            setSelectedMessage(selected);

            if (selected_option_values.toString() === selected.values.toString()) {
                return; // Already posted this combination
            }

            selected_option_values = selected.values;

            $.ajax({
                data: {
                    values: selected.values,
                    token: GplCart.settings.token,
                    action: 'switchProductOptionsAjax',
                    product_id: GplCart.settings.product.product_id
                },
                method: 'post',
                dataType: 'json',
                url: GplCart.settings.base + 'ajax',
                success: function (data) {

                    if (typeof data !== 'object') {
                        alert(GplCart.text('An error occurred'));
                        return false;
                    }

                    if (data.message) {
                        message.html(data.message);
                    }

                    if (data.modal) {
                        setModal(data.modal, 'product-update-option');
                    }

                    $('#sku').text(data.sku);
                    $('#price').text(data.price_formatted);

                    if (data.original_price_formatted) {
                        $('#original-price').text(data.original_price_formatted);
                    }

                    if (data.combination.file_id) {

                        slider = $('#' + theme_settings.product_gallery_id);
                        image = slider.find('img[data-file-id="' + data.combination.file_id + '"]');

                        if (image.length && loaded_sliders[theme_settings.product_gallery_id]) {
                            images = slider.find('img[data-file-id]');
                            loaded_sliders[theme_settings.product_gallery_id].goToSlide(images.index(image));
                        }
                    }

                    $(':input[data-field-value-id]').closest('label').removeClass('related');

                    if (data.related) {
                        $(':input[data-field-value-id]').each(function () {
                            if ($.inArray($(this).data('field-value-id').toString(), data.related) > -1) {
                                $(this).closest('label').addClass('related');
                            }
                        });
                    }

                    $('[name="add_to_cart"]').prop('disabled', !data.cart_access);
                },
                error: function () {
                    alert(GplCart.text('An error occurred'));
                },
                beforeSend: function () {
                    $('[data-field-id]').prop('disabled', true);
                    message.html('<span class="loading">' + GplCart.text('Checking availability...') + '</span>');
                },
                complete: function () {
                    $('[data-field-id]').prop('disabled', false);
                    message.find('.loading').remove();
                }
            });
        });
    };

    /**
     * Reset selected field options
     * @returns {undefined}
     */
    GplCart.onload.resetFieldOptions = function () {
        var fid;
        $(document).on('click', '[data-reset-field-id]', function () {
            fid = $(this).data('reset-field-id');
            $('input[data-field-id="' + fid + '"]:checkbox').prop('checked', false).trigger('change');
            $('input[data-field-id="' + fid + '"][value=""]:radio').prop('checked', true).trigger('change');
            $('select option[data-field-id="' + fid + '"][value=""]').prop('selected', true).trigger('change');
            return false;
        });
    };

    /**
     * Shows only rows with different values
     * @returns {undefined}
     */
    GplCart.onload.compareDiff = function () {

        var row, values, count;

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
     * Blocks submit for empty imputs
     * @returns {undefined}
     */
    GplCart.onload.blockEmptyInput = function () {
        var input;
        $(document).on('click', '[data-block-if-empty]', function () {
            input = $('[name="' + $(this).data('block-if-empty') + '"]');
            if (input.length === 0 || input.val().length === 0) {
                return false;
            }
        });
    };

    /**
     * Search autocomplete field
     * @returns {undefined}
     */
    GplCart.onload.searchAutocomplete = function () {

        var params, input = $('input[name="q"]');

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

                $.post(GplCart.settings.base + 'ajax', params, function (data) {
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
    GplCart.onload.setCityAutocomplete = function () {
        $(document).on('change', '[name="address[state_id]"]', function () {
            setCityAutocomplete();
        });
    };

    /**
     * Redirects to a page when clicked on the suggested item
     * @returns {undefined}
     */
    GplCart.onload.redirectSuggestions = function () {
        $(document).on('click', '.ui-autocomplete .suggestion', function () {
            window.location.href = $(this).attr('data-url');
        });
    };

    /**
     * Handles checkout form submits
     * @returns {undefined}
     */
    GplCart.onload.submitCheckout = function () {

        var clicked,
                selector = 'form#checkout input[type="radio"], form#checkout select, form#checkout [name$="[quantity]"]';

        $(document).on('change', selector, function () {
            $('form#checkout').submit();
        });

        $(document).on('click', 'form#checkout :submit', function () {
            clicked = $(this).attr('name');
            $(this).closest('form').append($("<input type='hidden'>").attr({
                name: $(this).attr('name'),
                value: $(this).attr('value')
            }));
        });

        $(document).off('submit').on('submit', 'form#checkout', function (e) {

            if (clicked && (clicked === 'save'
                    || clicked === 'login'
                    || clicked.slice(-10) === '[wishlist]')) {
                return true;
            }

            e.preventDefault();

            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: GplCart.settings.urn,
                data: $('form#checkout').serialize(),
                success: function (data) {
                    if (data.length) {
                        $('#checkout-form-wrapper').html(data);
                    }
                },
                beforeSend: function () {
                    $('form#checkout :input').prop('disabled', true);
                },
                complete: function () {
                    $('form#checkout :input').prop('disabled', false);
                }
            });
        });
    };

    /**
     * Adds + - spinner functionality
     * @returns {undefined}
     */
    GplCart.onload.plusMinusInput = function () {

        var btn, group, type, input, val, min, max;

        $(document).on('click', '[data-spinner]', function () {

            btn = $(this);
            group = btn.closest('.input-group');
            group.find('[data-spinner]').attr('disabled', false);

            type = btn.data('spinner');
            input = group.find('input');
            val = parseInt(input.val());
            min = input.data('min') === 'undefined' ? 0 : input.data('min');
            max = input.data('max') === 'undefined' ? 9999999999 : input.data('max');

            if (isNaN(val)) {
                input.val(min);
                return false;
            }

            if (type === '-') {
                if (val > min) {
                    input.val(val - 1).change();
                }
                if (parseInt(input.val()) === min) {
                    btn.attr('disabled', true);
                }

            } else if (type === '+') {
                if (val < max) {
                    input.val(val + 1).change();
                }
                if (parseInt(input.val()) === max) {
                    btn.attr('disabled', true);
                }
            }

            return false;
        });
    };

})(window, document, GplCart, jQuery);