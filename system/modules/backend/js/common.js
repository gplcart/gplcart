/* global window, document, Gplcart, jQuery*/
(function (window, document, Gplcart, $) {

    "use strict";

    var image_container = '.image-container';

    /**
     * Returns html for modal
     * @param {String} content
     * @param {String} id
     * @param {String} header
     * @param {String} footer
     * @returns {String}
     */
    var htmlModal = function (content, id, header, footer) {

        var mod, html = '';

        html += '<div class="modal fade" id="' + id + '">';
        html += '<div class="modal-dialog">';
        html += '<div class="modal-content">';
        html += '<div class="modal-header clearfix">';
        html += '<button type="button" class="btn btn-default pull-right" data-dismiss="modal">';
        html += '<i class="fa fa-times"></i></button>';

        if (typeof header !== 'undefined') {
            html += '<h3 class="modal-title pull-left">' + header + '</h3>';
        }

        html += '</div>';
        html += '<div class="modal-body">' + content + '</div>';

        if (typeof footer !== 'undefined') {
            html += '<div class="modal-footer">' + footer + '</div>';
        }

        html += '</div>';
        html += '</div>';

        mod = Gplcart.hook('html.modal', html);
        return mod === undefined ? html : mod;
    };

    /**
     * Returns HTML of loading indicator
     * @returns {String}
     */
    var htmlLoading = function () {

        var mod, html = '';

        html += '<div class="modal loading show">';
        html += '<div class="modal-dialog">';
        html += '<div class="modal-content">';
        html += '<div class="modal-body">';
        html += '<div class="progress">';
        html += '<div class="progress-bar progress-bar-striped active"></div>';
        html += '</div></div></div></div></div>';
        html += '<div class="modal-backdrop loading fade in"></div>';

        mod = Gplcart.hook('html.loading', html);
        return mod === undefined ? html : mod;
    };

    /**
     * Returns HTML of alert popup
     * @param {String} message
     * @param {String} id
     * @param {String} type
     * @returns {String}
     */
    var htmlAlert = function (message, id, type) {

        var mod, html = '';

        html += '<div id="' + id + '" class="popup alert alert-' + type + ' alert-dismissible" role="alert">';
        html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
        html += '<span>&times;</span></button>';
        html += message;
        html += '</div>';

        mod = Gplcart.hook('html.alert', html);
        return mod === undefined ? html : mod;
    };

    /**
     * Returns a string containing HTML of the product combination row to be appended
     * @returns {String}
     */
    var htmlProductCombinationRow = function () {

        var mod, html = '', index = $('#option-form-wrapper tbody tr').length + 1;

        html += '<tr>';
        $('#option-form-wrapper tfoot select').each(function () {
            html += '<td class="field-title">';
            html += '<select data-live-search="true" class="form-control" name="product[combination][' + index + '][fields][' + $(this).attr('data-field-id') + ']">';
            html += $(this).html();
            html += '</select>';
            html += '</td>';
        });

        html += '<td>';
        html += '<input maxlength="255" class="form-control" name="product[combination][' + index + '][sku]" value="" placeholder="' + Gplcart.text('Generate automatically') + '">';
        html += '</td>';
        html += '<td>';
        html += '<input class="form-control" name="product[combination][' + index + '][price]" value="">';
        html += '</td>';
        html += '<td>';
        html += '<input class="form-control" name="product[combination][' + index + '][stock]" value="">';
        html += '</td>';
        html += '<td>';
        html += '<a href="#" onclick="return false;" class="btn btn-default select-image"><i class="fa fa-image"></i></a>';
        html += '<input type="hidden" name="product[combination][' + index + '][file_id]" value="">';
        html += '<input type="hidden" name="product[combination][' + index + '][path]" value="">';
        html += '<input type="hidden" name="product[combination][' + index + '][thumb]" value="">';
        html += '</td>';
        html += '<td>';
        html += '<div class="default">';
        html += '<input type="radio" class="form-control" name="product[combination][' + index + '][is_default]">';
        html += '</div>';
        html += '</td>';
        html += '<td>';
        html += '<div class="status">';
        html += '<input type="checkbox" class="form-control" value="1" name="product[combination][' + index + '][status]" checked>';
        html += '</div>';
        html += '</td>';
        html += '<td>';
        html += '<a href="#" onclick="return false;" class="btn btn-default remove-option-combination"><i class="fa fa-trash"></i></a>';
        html += '</td>';
        html += '</tr>';

        mod = Gplcart.hook('html.product.combination', html);
        return mod === undefined ? html : mod;
    };

    /**
     * Returns HTML of image browser modal
     * @returns {String}
     */
    var htmlProductImageModal = function () {

        var src,
                mod,
                path,
                html = '',
                images = $(image_container).find('.thumb');

        if (images.length === 0) {
            return '';
        }

        html += '<div class="row">';

        images.each(function () {

            src = $(this).find('img').attr('src');
            path = $(this).find('input[name$="[path]"]').val();

            html += '<div class="col-md-3">';
            html += '<div class="thumbnail">';
            html += '<img data-file-path="' + path + '" src="' + src + '" class="img-responsive combination-image">';
            html += '</div>';
            html += '</div>';
        });

        html += '</div>';

        mod = Gplcart.hook('html.product.image.modal', html);
        return mod === undefined ? html : mod;
    };

    /**
     * Returns HTML of combination image button
     * @param {String} src
     * @returns {String}
     */
    var htmlProductCombinationImage = function (src) {
        return '<img style="height:20px; width:20px;" src="' + src + '" class="img-responsive combination-image">';
    };

    /**
     * Returns HTML of selected product items
     * @param {Object} item
     * @param {Object} el
     * @returns {String}
     */
    var htmlProductPicker = function (item, el) {

        var mod,
                html = '',
                name = el.data('name'),
                val = item.data[el.data('key')];

        html += '<div class="selected-item">';

        if (el.data('multiple')) {
            html += '<input type="hidden" name="' + name + '[]" value="' + val + '">';
        } else {
            html += '<input type="hidden" name="' + name + '" value="' + val + '">';
        }

        html += item.data.rendered;
        html += '</div>';

        mod = Gplcart.hook('html.product.picker', html);
        return mod === undefined ? html : mod;
    };

    /**
     * Displays a modal popup with a custom content
     * @param {String} content
     * @param {String} id
     * @param {String} header
     * @param {String} footer
     * @returns {undefined}
     */
    var setModal = function (content, id, header, footer) {

        if (Gplcart.hook('modal.set.before', content, id, header, footer) === undefined) {

            $('.modal').remove();
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').removeAttr('style');

            $('.modal').remove();
            $('body').append(htmlModal(content, id, header, footer));
            $('#' + id).modal('show');

            Gplcart.hook('modal.set.after', content, id, header, footer);
        }
    };

    /**
     * Displays a loading indicator
     * @param {Boolean} mode
     */
    var setLoading = function (mode) {

        if (Gplcart.hook('loading.set.before', mode) === undefined) {

            if (mode === false) {
                $('body').find('.loading').remove();
            } else {
                $('body').append(htmlLoading());
            }

            Gplcart.hook('loading.set.after', mode);
        }
    };

    /**
     * Set alert message
     * @param {String} message
     * @param {String} id
     * @param {String} type
     * @returns {undefined}
     */
    var setAlert = function (message, id, type) {

        if (Gplcart.hook('alert.set.before', message, id, type) === undefined) {
            $('body').append(htmlAlert(message, id, type));
            setTimeout(function () {
                $('#' + id + '.popup.alert').remove();
            }, 3000);
            Gplcart.hook('alert.set.after', message, id, type);
        }
    };

    /**
     * Loads product fields via AJAX
     * @param {String} id
     * @returns {undefined}
     */
    var loadProductFields = function (id) {
        $.ajax({
            dataType: 'html',
            url: Gplcart.settings.urn + '?product_class_id=' + id,
            success: function (response) {
                $('#attribute-form-wrapper').html($(response).find('div#attribute-form').html());
                $('#option-form-wrapper').html($(response).find('#option-form').html());
            },
            complete: function (response) {
                Gplcart.hook('product.field.load.ajax.complete', id, response);
            }
        });
    };

    /**
     * Marks those images in modal which are already set in product options
     * @returns {undefined}
     */
    var markSelectedCombinationImage = function (modal) {
        modal.find('img').each(function () {
            var path = $(this).attr('data-file-path');
            if ($('#option-form-wrapper tbody input[name$="[path]"][value="' + path + '"]').length) {
                $(this).css('opacity', 0.5);
            }
        });
    };

    /**
     * Check if the button already has an image. If so, remove it
     * @param {Object} button
     * @returns {Boolean}
     */
    var toggleCombinationImageButton = function (button) {

        if (button.find('img').length === 0) {
            return false;
        }

        button.html('<i class="fa fa-image"></i>');
        button.siblings('input').val('');
        return true;
    };

    /**
     * Adds one more combination row to the table
     * @returns {undefined}
     */
    Gplcart.onload.addProductCombination = function () {
        $(document).on('click', '#option-form-wrapper .add-option-combination', function (e) {
            if (Gplcart.hook('product.combination.add.before', e) === undefined) {
                $('#option-form-wrapper table tbody').append(htmlProductCombinationRow());
                Gplcart.hook('product.combination.add.after', e);
                return false;
            }
        });
    };

    /**
     * Ensure that only one combination default radio button is selected
     * @returns {undefined}
     */
    Gplcart.onload.checkDefaultProductCombination = function () {

        var radio = '.option input[name$="[is_default]"]';

        $(document).on('click', '.uncheck-default-combination', function () {
            $(radio).prop('checked', false);
            return false;
        });

        $(document).on('change', radio, function () {
            $(radio + ':checked').not(this).prop('checked', false);
        });
    };

    /**
     * Deletes an option combination
     * @returns {undefined}
     */
    Gplcart.onload.deleteProductCombination = function () {
        $(document).on('click', '#option-form-wrapper .remove-option-combination', function (e) {
            if (Gplcart.hook('product.combination.delete.before', e) === undefined) {
                $(this).closest('tr').remove();
                Gplcart.hook('product.combination.delete.after', e);
                return false;
            }
        });
    };

    /**
     * Highlight rows with disabled option combinations
     * @returns {undefined}
     */
    Gplcart.onload.markProductCombinationStatus = function () {
        $(document).on('change', '#option-form-wrapper input[name$="[status]"]', function () {
            if ($(this).not(':checked')) {
                $(this).closest('tr').toggleClass('bg-danger');
            }
        });
    };

    /**
     * Select an option combination image
     * @returns {undefined}
     */
    Gplcart.onload.selectProductCombinationImage = function () {
        $(document).on('click', '#option-form-wrapper .select-image', function () {

            if (toggleCombinationImageButton($(this))) {
                return false;
            }

            var html = htmlProductImageModal();

            if (html.length) {
                setModal(html, 'select-image-modal');
                var modal = $('#select-image-modal').attr('data-active-row', $(this).closest('tr').index());
                markSelectedCombinationImage(modal);
            }

            return false;
        });
    };

    /**
     * Sets a selected option combination image
     * @returns {undefined}
     */
    Gplcart.onload.setProductCombinationImage = function () {

        var e, src, path, pos;

        $(document).on('click', 'img.combination-image', function () {

            src = $(this).attr('src');
            path = $(this).attr('data-file-path');
            pos = $(this).closest('#select-image-modal').attr('data-active-row');
            e = $('#option-form-wrapper tbody tr').eq(pos).find('.select-image');

            e.html(htmlProductCombinationImage(src));
            e.siblings('input[name$="[path]"]').val(path);
            e.siblings('input[name$="[thumb]"]').val(src);
            $('#select-image-modal').modal('hide');
        });
    };

    /**
     * Removes a product picker item
     * @returns {undefined}
     */
    Gplcart.onload.removeProductPickerItem = function () {
        $(document).on('click', '.product-picker-results .selected-item', function () {
            $(this).remove();
        });
    };

    /**
     * Adds a hash to pager links inside panels
     * @returns {undefined}
     */
    Gplcart.onload.setPager = function () {

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
     * Set up product picker
     * @returns {undefined}
     */
    Gplcart.onload.setProductPicker = function () {

        var params, store_id, input = $('input.product-picker');

        if (input.length) {
            input.autocomplete({
                minLength: 2,
                classes: {
                    "ui-autocomplete": "product-picker-popup"
                },
                source: function (request, response) {
                    
                    params = {
                        status: 1,
                        term: request.term,
                        action: 'getProductsAjax',
                        token: Gplcart.settings.token
                    };

                    store_id = input.data('store-id') || input.closest('form').find('[name$="[store_id]"]').val();
                    
                    if(store_id){
                        params.store_id = store_id;
                    }

                    $.post(Gplcart.settings.base + 'ajax', params, function (data) {
                        response($.map(data, function (value) {
                            return {data: value};
                        }));
                    });
                },
                select: function (e, ui) {

                    if (input.data('multiple')) {
                        input.prev('.product-picker-results').append(htmlProductPicker(ui.item, input));
                    } else {
                        input.prev('.product-picker-results').html(htmlProductPicker(ui.item, input));
                    }

                    input.val('');
                    return false;
                },
                open: function () {
                    $('.product-picker-popup').css('width', input.closest('div').width());
                }
            }).autocomplete("instance")._renderItem = function (ul, item) {
                return $("<li>").append(item.data.rendered).appendTo(ul);
            };
        }
    };

    /**
     * Adds autocomplete functionality to collection item fields
     * @returns {undefined}
     */
    Gplcart.onload.setAutocompleteCollectionItem = function () {

        var params = {},
                input = $('form#edit-collection-item input[name$="[input]"]'),
                value = $('form#edit-collection-item input[name$="[value]"]');

        if (input.length === 0 || !Gplcart.settings.collection) {
            return;
        }

        input.autocomplete({
            minLength: 2,
            source: function (request, response) {

                params = {
                    term: request.term,
                    token: Gplcart.settings.token,
                    action: 'getCollectionItemAjax',
                    collection_id: Gplcart.settings.collection.collection_id
                };

                $.post(Gplcart.settings.base + 'ajax', params, function (data) {
                    response($.map(data, function (value, key) {
                        return {
                            value: key,
                            label: value.title ? value.title + ' (' + key + ')' : '--'
                        };
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
    };

    /**
     * Updates product fields when product class was changed
     * @returns {undefined}
     */
    Gplcart.onload.updateProductClassFields = function () {
        $(document).on('change', '[name$="[product_class_id]"]', function () {
            var val = $(this).val();
            loadProductFields(val);
            if (val) {
                $('body,html').animate({scrollTop: $('#option-form-wrapper').offset().top - 60});
            }
        });
    };

    /**
     * Updates product fields on demand
     * @returns {undefined}
     */
    Gplcart.onload.updateProductFields = function () {
        $(document).on('click', '.refresh-fields', function () {
            loadProductFields($('[name$="[product_class_id]"]').val());
            return false;
        });
    };

    /**
     * Removes a product field
     * @returns {undefined}
     */
    Gplcart.onload.removeProductField = function () {
        $(document).on('click', '#product-class-fields input[name$="[remove]"]', function () {
            $(this).closest('tr').toggleClass('danger', this.checked);
        });
    };


    /**
     * Updates order view form
     * @returns {undefined}
     */
    Gplcart.onload.updateOrder = function () {
        $('[name="order[status]"]').change(function () {
            if (confirm(Gplcart.text('Do you want to change order status?'))) {
                $(this).closest('form').find('[name="status"]:submit').click();
            }
        });
    };

    /**
     * Delete uploaded images
     * @returns {undefined}
     */
    Gplcart.onload.deleteUploadedImages = function () {
        $(document).on('click', '[name="delete_images[]"]', function (e) {
            if (Gplcart.hook('product.images.delete.before', e)) {
                if (!$(this).val()) {
                    $(this).closest('div.thumb').remove();
                }
                Gplcart.hook('product.images.delete.after', e);
                return false;
            }
        });
    };

    /**
     * Makes uploaded images sortable
     * @returns {undefined}
     */
    Gplcart.onload.setSortableImages = function () {

        var settings = {
            items: '> div > div',
            handle: '.handle',
            stop: function () {
                $('input[name$="[weight]"]').each(function (i, v) {
                    $(this).val(i);
                });
            }
        };

        $(image_container).sortable(settings);
    };

    /**
     * Makes sortable table rows containing weigth value
     * @returns {undefined}
     */
    Gplcart.onload.setSortableTableWeigth = function () {

        var weight = {}, selector = $('table[data-sortable-weight="true"] tbody');

        if (selector.length) {
            selector.sortable({
                cursor: 'n-resize',
                handle: '.handle',
                stop: function () {

                    selector.find('tr').each(function (i) {
                        weight[$(this).attr('data-id')] = i;
                    });

                    $.ajax({
                        type: 'POST',
                        url: Gplcart.settings.urn,
                        data: {
                            token: Gplcart.settings.token,
                            action: {items: weight, name: 'weight'}
                        },
                        success: function (data) {
                            if (typeof data === 'object' && data.success) {
                                setAlert(data.success, 'weight-updating-success', 'success');
                                $.each(weight, function (i, v) {
                                    $('tr[data-id=' + i + ']').find('td .weight').text(v);
                                });
                            }
                        },
                        beforeSend: function () {
                            setLoading(true);
                        },
                        complete: function () {
                            setLoading(false);
                        }
                    });
                }
            });
        }
    };

    /**
     * Makes sortable table rows containing weigth input
     * @returns {undefined}
     */
    Gplcart.onload.setSortableTableWeigthInput = function () {

        var selector = $('table[data-sortable-input-weight="true"] tbody'),
                text = Gplcart.text('Changes will not be saved until the form is submitted');

        if (selector.length) {
            selector.sortable({
                handle: '.handle',
                stop: function () {
                    $('input[name$="[weight]"]').each(function (i) {
                        $(this).val(i);
                        $(this).closest('tr').find('td .weight').text(i);
                    });
                    setAlert(text, 'sort-weigth-input-warning', 'warning');
                }
            });
        }
    };

    /**
     * Checks status checkbox when the corresponding "required" checkbox is checked
     * @returns {undefined}
     */
    Gplcart.onload.ensureCountryRequiredStatus = function () {
        $('table.country-format input[name$="[required]"]').click(function () {
            if ($(this).is(':checked')) {
                $(this).closest('tr').find('input[name$="[status]"]').prop('checked', true);
            }
        });
    };

    /**
     * Updates categories depending on chosen store
     * @returns {undefined}
     */
    Gplcart.onload.updateStoreCategories = function () {

        var i, g, cats, options,
                store = $('select[name$="[store_id]"]'),
                category = $('select[name$="[category_id]"]');

        store.change(function (e) {

            $.ajax({
                url: Gplcart.settings.base + 'ajax',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'getStoreCategoriesAjax',
                    token: Gplcart.settings.token,
                    store_id: $(this).find('option:selected').val()
                },
                beforeSend: function () {
                    store.prop('disabled', true);
                    category.prop('disabled', true);
                    Gplcart.hook('category.store.update.ajax.send', e);
                },
                success: function (response) {
                    options = '';
                    if (typeof response === 'object') {
                        for (g in response) {
                            options += '<optgroup label="' + g + '">';
                            cats = response[g];
                            for (i in cats) {
                                options += '<option value="' + i + '">' + cats[i] + '</option>';
                            }
                        }
                        category.html(options);
                    }

                    Gplcart.hook('category.store.update.ajax.success', e);
                },
                complete: function () {
                    store.prop('disabled', false);
                    category.prop('disabled', false);
                    Gplcart.hook('category.store.update.ajax.complete', e);
                }
            });
        });
    };

    /**
     * Adds a datepicker popup
     * @returns {undefined}
     */
    Gplcart.onload.setDatepicker = function () {

        var el = $('[data-datepicker="true"]'),
                settings = {dateFormat: 'dd.mm.yy'},
                inline = el.data('datepicker-settings') || {};

        if (typeof inline === 'object') {
            settings = $.extend(settings, inline);
        }

        el.datepicker(settings);
    };

    /**
     * Handles filters
     * @returns {undefined}
     */
    Gplcart.onload.setFilter = function () {

        var redirect, params;

        $('thead :submit').click(function () {
            params = $(this).closest('thead').find(':input[name]').filter(function () {
                return this.value !== "";
            }).serialize();

            redirect = window.location.href.split('?')[0];

            if (params) {
                redirect += '?' + params;
            }

            window.location = redirect;
            return false;
        });
    };

})(window, document, Gplcart, jQuery);