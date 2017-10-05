/* global window, document, Gplcart, jQuery */
(function (window, document, Gplcart, $) {

    "use strict";

    var selected_option_values = [];

    /**
     * Returns HTML of modal pop-up
     * @param {String} content
     * @param {String} id
     * @param {String} header
     * @returns {String}
     */
    var htmlModal = function (content, id, header) {

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
    };

    /**
     * Returns HTML of gallery pop-up
     * @param {String} src
     * @param {String} id
     * @returns {String}
     */
    var htmlGalleryModal = function (src, id) {

        var html = '<div class="modal fade gallery" id="' + id + '">';
        html += '<div class="modal-dialog">';
        html += '<div class="modal-content">';
        html += '<div class="modal-body text-center">';
        html += '<img class="img-responsive" src="' + src + '">';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        return html;
    };

    /**
     * Returns HTML of "In comparison" button
     * @returns {String}
     */
    var htmlBtnInCompare = function () {

        var html = '';
        html += '<a title="' + Gplcart.text('Already in comparison') + '" href="' + Gplcart.settings.base + 'compare" class="btn btn-default active">';
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
                url = Gplcart.settings.base + 'wishlist',
                title = Gplcart.text('Already in wishlist');

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
        btn += '<span title="' + Gplcart.text('Remove') + '" data-reset-field-id="' + fid + '" class="btn btn-default btn-xs">';
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
     * Set a message that displays a selected option combination
     * @param {Object} data
     * @returns {undefined}
     */
    var setSelectedMessage = function (data) {
        var text = '';
        if (!$.isEmptyObject(data.titles)) {
            text = Gplcart.text('Selected: !combination', {'!combination': data.titles.join(' ')});
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
                    token: Gplcart.settings.token
                };

                $.post(Gplcart.settings.base + 'ajax', params, function (data) {
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
     * Set gallery image modal
     * @param {String} src
     * @param {String} id
     * @returns {undefined}
     */
    var setGalleryModal = function (src, id) {

        $('.modal').remove();
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').removeAttr('style');

        $('body').append(htmlGalleryModal(src, id));
        $('#' + id).modal('show');
    };

    /**
     * Fix equal height of items
     * @returns {undefined}
     */
    Gplcart.onload.equalHeight = function () {
        if ($.fn.matchHeight) {
            $('[data-equal-height="true"]').matchHeight();
        }
    };

    /**
     * Adds hash to pager links inside panels
     * @returns {undefined}
     */
    Gplcart.onload.addPagerHash = function () {

        var links, id, href;
        $('.panel').each(function () {
            id = $(this).attr('id');
            if (id) {
                links = $(this).find('.pagination a');
                if (links) {
                    links.each(function () {
                        href = $(this).attr('href');
                        href += '#' + id;
                        $(this).attr('href', href);
                    });
                }
            }
        });
    };

    /**
     * Loads cart preview on demand
     * @returns {undefined}
     */
    Gplcart.onload.cartPreview = function () {

        $('#cart-link').click(function () {

            $.ajax({
                type: 'POST',
                url: Gplcart.settings.base + 'ajax',
                dataType: 'json',
                data: {
                    action: 'getCartPreviewAjax',
                    token: Gplcart.settings.token
                },
                success: function (data) {
                    if (typeof data === 'object' && data.preview) {
                        setModal(data.preview, 'cart-preview', Gplcart.text('Cart'));
                    }
                },
                error: function () {}
            });

            return false;
        });
    };

    /**
     * Handles various submit events
     * @returns {undefined}
     */
    Gplcart.onload.submit = function () {

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
                url: Gplcart.settings.urn,
                data: button.closest('form').serialize() + '&' + action + '=1',
                success: function (data) {

                    if (typeof data !== 'object') {
                        return false;
                    }

                    if (data.redirect) {
                        window.location.replace(Gplcart.settings.base + data.redirect);
                        return false;
                    }

                    if ('modal' in data) {
                        if (action === 'add_to_cart') {
                            header = Gplcart.text('Cart');
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
                error: function () {}
            });

            return false;
        });
    };

    /**
     * Handles changing product options
     * @returns {undefined}
     */
    Gplcart.onload.updateOption = function () {

        var athumb, amain, selected = getSelectedOptions(), message = $('.add-to-cart .message');

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
                method: 'POST',
                dataType: 'json',
                url: Gplcart.settings.base + 'ajax',
                data: {
                    values: selected.values,
                    action: 'switchProductOptionsAjax',
                    token: Gplcart.settings.token,
                    product_id: Gplcart.settings.product.product_id
                },
                success: function (data) {

                    if (typeof data !== 'object') {
                        console.warn('Response in not object');
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
                        athumb = $('a[data-file-id="' + data.combination.file_id + '"][data-gallery][data-gallery-thumb]');
                        if (athumb.length) {
                            amain = $('[data-gallery="' + athumb.data('gallery') + '"][data-gallery-main-image="true"]');
                            if (amain.length) {
                                amain.find('img').attr('src', athumb.find('img').attr('src'));
                            }
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
                error: function () {},
                beforeSend: function () {
                    $('[data-field-id]').prop('disabled', true);
                    message.html('<span class="loading">' + Gplcart.text('Checking availability...') + '</span>');
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
    Gplcart.onload.resetFieldOptions = function () {
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
    Gplcart.onload.compareDiff = function () {

        var row, togglable, values, count;

        $('#compare-difference').change(function () {
            togglable = $('table.compare-products tr.togglable');
            if ($(this).is(':checked')) {
                togglable.each(function () {
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
            } else {
                togglable.show();
            }
        });
    };

    /**
     * Blocks submit for empty imputs
     * @returns {undefined}
     */
    Gplcart.onload.blockEmptyInput = function () {
        var input;
        $(document).on('click', '[data-block-if-empty]', function () {
            input = $('[name="' + $(this).data('block-if-empty') + '"]');
            if (input.length === 0 || input.val().length === 0) {
                return false;
            }
        });
    };

    /**
     * Add state change listener that updates city autocomplete field
     * @returns {undefined}
     */
    Gplcart.onload.setCityAutocomplete = function () {
        $(document).on('change', '[name="address[state_id]"]', function () {
            setCityAutocomplete();
        });
    };

    /**
     * Handles checkout form submits
     * @returns {undefined}
     */
    Gplcart.onload.submitCheckout = function () {

        var clicked;

        $(document).on('change', 'form#checkout :input:not([data-ajax="false"])', function () {
            $('form#checkout').submit();
        });

        $(document).on('click', 'form#checkout :submit', function () {
            clicked = $(this);
            $(this).closest('form').append($('<input>').attr({
                type: 'hidden',
                name: $(this).attr('name'),
                value: $(this).attr('value')
            }));
        });

        $(document).off('submit').on('submit', 'form#checkout', function (e) {

            if (clicked && clicked.data('ajax') === false) {
                return true;
            }

            e.preventDefault();

            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: Gplcart.settings.urn,
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
    Gplcart.onload.plusMinusInput = function () {

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

    /**
     * Handles simple image gallery
     * @returns {undefined}
     */
    Gplcart.onload.gallery = function () {
        var el, id, target, a;

        $('[data-gallery]').on('click', function () {
            el = $(this);
            id = el.data('gallery');
            if (el.data('gallery-main-image')) {
                target = el.attr('href');
                setGalleryModal(target, id);
            } else {
                a = $('[data-gallery-main-image="true"][data-gallery="' + id + '"]');
                a.find('img').attr('src', el.data('gallery-thumb'));
                a.attr('href', el.attr('href'));
            }

            return false;
        }).on('mouseover', function () {

            el = $(this);
            id = el.data('gallery');

            if (!el.data('gallery-main-image')) {
                a = $('[data-gallery-main-image="true"][data-gallery="' + id + '"]');
                a.find('img').attr('src', el.data('gallery-thumb'));
                a.attr('href', el.attr('href'));
            }
        });
    };

})(window, document, Gplcart, jQuery);