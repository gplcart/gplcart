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

        var vars = {
            '@id': id,
            '@header': header,
            '!content': content
        };

        var html = '<div class="modal fade" id="@id">\n\
                    <div class="modal-dialog">\n\
                    <div class="modal-content">\n\
                    <div class="modal-header clearfix">\n\
                    <a href="#" class="float-right" data-dismiss="modal">\n\
                    <i class="fa fa-times"></i></a>';

        if (typeof header !== 'undefined') {
            html += '<h4 class="modal-title float-left">@header</h4>';
        }

        html += '</div>\n\
                <div class="modal-body">!content</div>\n\
                </div>\n\
                </div>';

        html = Gplcart.format(html, vars);

        var mod = Gplcart.hook('html.modal', html);
        return mod === undefined ? html : mod;
    };

    /**
     * Returns HTML of gallery pop-up
     * @param {String} src
     * @param {String} id
     * @returns {String}
     */
    var htmlModalGallery = function (src, id) {

        var vars = {'@id': id, '@src': src};

        var html = '<div class="modal fade gallery" id="@id">\n\
                    <div class="modal-dialog">\n\
                    <div class="modal-content">\n\
                    <div class="modal-header clearfix">\n\
                    <a href="#" class="float-right" data-dismiss="modal">\n\
                    <i class="fa fa-times"></i></a>\n\
                    </div>\n\
                    <div class="modal-body text-center">\n\
                    <img class="img-fluid" src="@src">\n\
                    </div>\n\
                    </div>\n\
                    </div>\n\
                    </div>';

        html = Gplcart.format(html, vars);
        var mod = Gplcart.hook('html.modal.gallery', html);
        return mod === undefined ? html : mod;
    };

    /**
     * Returns HTML of "In comparison" button
     * @returns {String}
     */
    var htmlBtnInCompare = function () {

        var vars = {
            '@href': Gplcart.settings.base + 'compare',
            '@title': Gplcart.text('Already in comparison')
        };

        var html = '<a title="@title" href="@href" class="btn active">\n\
                      <i class="fa fa-balance-scale"></i>\n\
                    </a>';

        html = Gplcart.format(html, vars);
        var mod = Gplcart.hook('html.btn.in.compare', html);
        return mod === undefined ? html : mod;
    };

    /**
     * Returns HTML of "In wishlist" button
     * @returns {String}
     */
    var htmlBtnInWishlist = function () {

        var vars = {
            '@href': Gplcart.settings.base + 'wishlist',
            '@title': Gplcart.text('Already in wishlist')
        };

        var html = '<a title="@title" href="@href" class="btn active">\n\
                    <i class="fa fa-heart"></i>\n\
                    </a>';

        html = Gplcart.format(html, vars);
        var mod = Gplcart.hook('html.btn.in.wishlist', html);
        return mod === undefined ? html : mod;
    };

    /**
     * Returns rendered reset field options button
     * @param {String} fid
     * @param {String} title
     * @returns {String}
     */
    var htmlBtnSelectedOptions = function (fid, title) {

        var vars = {
            '@fid': fid,
            '@label': title,
            '@title': Gplcart.text('Remove')
        };

        var html = '<span title="@title" data-reset-field-id="@fid" class="btn btn-xs">\n\
                    @label<span class="fa fa-times"></span>\n\
                    </span>';

        html = Gplcart.format(html, vars);
        var mod = Gplcart.hook('html.btn.selected.options', html);
        return mod === undefined ? html : mod;
    };

    /**
     * Displays a modal popup
     * @param {String} content
     * @param {String} id
     * @param {String} header
     * @returns {undefined}
     */
    var setModal = function (content, id, header) {

        var res = Gplcart.hook('modal.set.before', content, id, header);

        if (res === undefined) {

            $('.modal').remove();
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').removeAttr('style');

            if (content.length) {
                $('body').append(htmlModal(content, id, header));
                $('#' + id).modal('show');
                Gplcart.hook('modal.set.after', content, id, header);
            }
        }
    };

    /**
     * Set gallery image modal
     * @param {String} src
     * @param {String} id
     * @returns {undefined}
     */
    var setModalGallery = function (src, id) {

        var res = Gplcart.hook('modal.gallery.set.before', src, id);

        if (res === undefined) {

            $('.modal').remove();
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').removeAttr('style');

            $('body').append(htmlModalGallery(src, id));
            $('#' + id).modal('show');
            Gplcart.hook('modal.gallery.set.after', src, id);
        }
    };

    /**
     * Handles "Add to cart" action
     * @param {String} action
     * @param {Object} data
     * @returns {undefined}
     */
    var submitAddToCart = function (action, data) {

        var res = Gplcart.hook('cart.add.before', action, data);

        if (res === undefined && action === 'add_to_cart' && data.hasOwnProperty('quantity')) {
            updateCartQuantity(data.quantity);
            Gplcart.hook('cart.add.after', action, data);
        }
    };

    /**
     * Handles "Remove from cart" action
     * @param {String} action
     * @param {Object} data
     * @returns {undefined}
     */
    var submitDeleteFromCart = function (action, data) {

        var res = Gplcart.hook('cart.delete.before', action, data);

        if (res === undefined && action === 'remove_from_cart' && data.hasOwnProperty('quantity')) {
            updateCartQuantity(data.quantity);
            Gplcart.hook('cart.delete.after', action, data);
        }
    };

    /**
     * Handles "Add to compare" action
     * @param {String} action
     * @param {Object} data
     * @param {Object} button
     * @returns {undefined}
     */
    var submitAddToCompare = function (action, data, button) {

        var res = Gplcart.hook('compare.add.before', action, data, button);

        if (res === undefined && action === 'add_to_compare' && data.hasOwnProperty('quantity')) {
            $('#compare-quantity').text(data.quantity).show();
            button.replaceWith(htmlBtnInCompare());
            Gplcart.hook('compare.add.after', action, data, button);
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

        var res = Gplcart.hook('wishlist.add.before', action, data, button);

        if (res === undefined && action === 'add_to_wishlist' && data.hasOwnProperty('quantity')) {
            updateWishlistQuantity(data.quantity);
            button.replaceWith(htmlBtnInWishlist());
            Gplcart.hook('wishlist.add.after', action, data, button);
        }
    };

    /**
     * Handles "Remove from wishlist" action
     * @param {String} action
     * @param {Object} data
     * @param {Object} button
     * @returns {undefined}
     */
    var submitDeleteFromWishlist = function (action, data, button) {

        var res = Gplcart.hook('wishlist.delete.before', action, data, button);

        if (res === undefined && action === 'remove_from_wishlist' && data.hasOwnProperty('quantity')) {
            updateWishlistQuantity(data.quantity);
            button.closest('.product.item').remove();
            Gplcart.hook('wishlist.delete.after', action, data, button);
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
     * Set a message when selecting an option combination
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
     * Loads the cart preview
     * @returns {undefined}
     */
    Gplcart.onload.cartPreview = function () {

        $(document).on('click', '#cart-link', function (e) {

            if (Gplcart.hook('cart.preview.on.click', e) === undefined) {

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
                        Gplcart.hook('cart.preview.ajax.success', e);
                    },
                    complete: function () {
                        Gplcart.hook('cart.preview.ajax.complete', e);
                    }
                });

                return false;
            }
        });
    };

    /**
     * Handles various submit events
     * @returns {undefined}
     */
    Gplcart.onload.submit = function () {

        var button, action, header;

        $(document).on('click', ':button[name][data-ajax="true"]', function (e) {

            if (Gplcart.hook('submit.on.click', e) === undefined) {

                e.preventDefault();

                button = $(this);
                action = button.attr('name');

                if (!action) {
                    return false;
                }

                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: Gplcart.settings.url,
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
                            submitDeleteFromCart(action, data);
                            submitAddToCompare(action, data, button);
                            submitAddToWishlist(action, data, button);
                            submitDeleteFromWishlist(action, data, button);
                        }

                        Gplcart.hook('submit.ajax.success', e);
                    },
                    complete: function () {
                        Gplcart.hook('submit.ajax.complete', e);
                    }
                });

                return false;
            }
        });
    };

    /**
     * Handles checkout form submits
     * @returns {undefined}
     */
    Gplcart.onload.submitCheckout = function () {

        var clicked;

        $(document).on('change', 'form#checkout :input:not([data-ajax="false"])', function (e) {
            if (Gplcart.hook('checkout.on.change', e) === undefined) {
                $('form#checkout').submit();
            }
        });

        $(document).on('click', 'form#checkout :submit', function (e) {
            if (Gplcart.hook('checkout.on.click', e) === undefined) {
                clicked = $(this);
                clicked.closest('form').append($('<input>').attr({
                    type: 'hidden',
                    name: $(this).attr('name'),
                    value: $(this).attr('value')
                }));
            }
        });

        $(document).off('submit').on('submit', 'form#checkout', function (e) {

            if (Gplcart.hook('checkout.on.submit', e) === undefined) {

                if (clicked && clicked.data('ajax') === false) {
                    return true;
                }

                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    dataType: 'html',
                    url: Gplcart.settings.url,
                    data: $('form#checkout').serialize(),
                    success: function (data) {
                        if (data.length) {
                            $('#checkout-form-wrapper').html(data);
                        }
                        Gplcart.hook('checkout.submit.ajax.success', e);
                    },
                    beforeSend: function () {
                        $('form#checkout :input').prop('disabled', true);
                        Gplcart.hook('checkout.submit.ajax.send', e);
                    },
                    complete: function () {
                        $('form#checkout :input').prop('disabled', false);
                        Gplcart.hook('checkout.submit.ajax.complete', e);
                    }
                });
            }
        });
    };

    /**
     * Handles changing product options
     * @returns {undefined}
     */
    Gplcart.onload.updateProductOptions = function () {

        var athumb, amain, selected = getSelectedOptions(), message = $('.add-to-cart .message');

        setSelectedMessage(selected);

        $(document).on('change', '[name^="product[options]"]', function (e) {

            if (Gplcart.hook('product.option.on.change', e, selected) === undefined) {

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
                        Gplcart.hook('product.option.update.ajax.success', e);
                    },
                    beforeSend: function () {
                        $('[data-field-id]').prop('disabled', true);
                        message.html('<span class="loading">' + Gplcart.text('Checking availability...') + '</span>');
                        Gplcart.hook('product.option.update.ajax.send', e);
                    },
                    complete: function () {
                        $('[data-field-id]').prop('disabled', false);
                        message.find('.loading').remove();
                        Gplcart.hook('product.option.update.ajax.complete', e);
                    }
                });
            }
        });
    };

    /**
     * Add state change listener that updates city autocomplete field
     * @returns {undefined}
     */
    Gplcart.onload.setCityAutocomplete = function () {
        $(document).on('change', '[name="address[state_id]"]', function (e) {
            if (Gplcart.hook('address.state.on.change', e) === undefined) {
                setCityAutocomplete();
            }
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
        $(document).on('click', '[data-block-if-empty]', function () {
            var input = $('[name="' + $(this).data('block-if-empty') + '"]');
            if (input.length === 0 || input.val().length === 0) {
                return false;
            }
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
    Gplcart.onload.setGallery = function () {

        var el, id, target, a;

        $('[data-gallery]').on('click', function () {

            el = $(this);
            id = el.data('gallery');
            if (el.data('gallery-main-image')) {
                target = el.attr('href');
                setModalGallery(target, id);
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

    /**
     * Adds hash to pager links inside panels
     * @returns {undefined}
     */
    Gplcart.onload.addPagerHash = function () {

        var links, id, href;
        $('.card').each(function () {
            id = $(this).attr('id');
            if (id) {
                links = $(this).find('[class^=pagination] a');
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

})(window, document, Gplcart, jQuery);