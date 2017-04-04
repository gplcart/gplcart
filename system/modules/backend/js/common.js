/* global window, document, GplCart, jQuery*/
(function (window, document, GplCart, $) {

    "use strict";

    var theme_settings = {image_container: '.image-container'};

    /**
     * Returns html for modal
     * @param {String} content
     * @param {String} id
     * @param {String} header
     * @param {String} footer
     * @returns {String}
     */
    var htmlModal = function (content, id, header, footer) {

        var html = '';

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

        return html;
    };

    /**
     * Returns HTML of loading indicator
     * @returns {String}
     */
    var htmlLoading = function () {

        var html = '';

        html += '<div class="modal loading show">';
        html += '<div class="modal-dialog">';
        html += '<div class="modal-content">';
        html += '<div class="modal-body">';
        html += '<div class="progress">';
        html += '<div class="progress-bar progress-bar-striped active"></div>';
        html += '</div></div></div></div></div>';
        html += '<div class="modal-backdrop loading fade in"></div>';

        return html;
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

        $('.modal').remove();
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').removeAttr('style');

        var html = htmlModal(content, id, header, footer);

        $('.modal').remove();
        $('body').append(html);
        $('#' + id).modal('show');
    };

    /**
     * Displays a loading indicator
     * @param {Boolean} mode
     */
    var setLoading = function (mode) {

        var html;

        if (mode === false) {
            $('body').find('.loading').remove();
        } else {
            html = htmlLoading();
            $('body').append(html);
        }
    };

    /**
     * Displays an alert popup with a custom message
     * @param {String} message
     * @param {String} type
     * @returns {undefined}
     */
    var setAlert = function (text, severity) {

        if (!$.fn.puigrowl) {
            return;
        }

        var settings = {life: 1000},
                message = [{severity: severity, summary: '', detail: text}];

        $('.growl-message').remove();
        $('body').append('<div class="growl-message"></div>');
        $('.growl-message').puigrowl(settings).puigrowl('show', message);
    };

    /**
     * Updates order view form
     * @returns {undefined}
     */
    GplCart.onload.updateOrder = function () {
        var conf;
        $('[name="order[status]"]').change(function () {
            conf = confirm(GplCart.text('Do you want to change order status?'));
            if (conf) {
                $(this).closest('form').find('[name="status"]:submit').click();
            }
        });
    };

    /**
     * Handles bulk actions
     * @returns {undefined}
     */
    GplCart.onload.bulkAction = function () {

        var conf, selected = [];

        $('[data-action]').click(function () {

            $('input[name^="selected"]').each(function () {
                if ($(this).is(':checked')) {
                    selected.push($(this).val());
                }
            });

            if (selected.length < 1) {
                return false;
            }

            conf = confirm($(this).data('action-confirm'));

            if (!conf) {
                return false;
            }

            $.ajax({
                method: 'POST',
                url: GplCart.settings.urn,
                data: {
                    selected: selected,
                    token: GplCart.settings.token,
                    action: $(this).data('action'),
                    value: $(this).data('action-value')
                },
                success: function () {
                    // Skip page numbers
                    window.location = window.location.href.split("?")[0];
                },
                beforeSend: function () {
                    setLoading(true);
                },
                complete: function () {
                    setLoading(false);
                }
            });
            return false;
        });
    };

    /**
     * Check / uncheck multiple checkboxes
     * @returns {undefined}
     */
    GplCart.onload.selectAll = function () {
        $('#select-all').click(function () {
            $('.select-all').prop('checked', $(this).is(':checked'));
        });
    };

    /**
     * Clears all filters
     * @returns {undefined}
     */
    GplCart.onload.clearFilter = function () {
        $('.clear-filter').click(function () {
            window.location.replace(GplCart.settings.urn.split("?")[0]);
        });
    };

    /**
     * Rerforms filter query
     * @returns {undefined}
     */
    GplCart.onload.filterQuery = function () {

        var url, query;

        $('.filters .filter').click(function () {

            query = $('.filters :input').filter(function (i, e) {
                return $(e).val() !== "";
            }).serialize();

            if (!query) {
                return false;
            }

            url = GplCart.settings.urn.split("?")[0] + '?' + query;
            window.location.replace(url);
            return false;
        });
    };

    /**
     * Adds WYSIWYG editor to a textarea
     * @returns {undefined}
     * @todo Inline settings in data attr
     */
    GplCart.onload.setWysiwyg = function () {

        if (!$.fn.summernote) {
            return;
        }

        var selector = $('textarea[data-wysiwyg="true"]');

        if (selector.length === 0) {
            return;
        }

        var settings = {
            height: 150,
            lang: GplCart.settings.lang_region,
            toolbar: [
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['style', ['style']],
                ['para', ['ul', 'ol']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'hr']],
                ['view', ['fullscreen', 'codeview']]
            ]};

        var inline_settings = selector.data('wysiwyg-settings') || {};

        if (typeof inline_settings === 'object') {
            settings = $.extend(settings, inline_settings);
        }
        selector.summernote(settings);
    };

    /**
     * Delete uploaded images
     * @returns {undefined}
     */
    GplCart.onload.deleteUploadedImages = function () {
        $(document).on('click', '[name="delete_images[]"]', function () {
            if (!$(this).val()) {
                $(this).closest('div.thumb').remove();
                return false;
            }
        });
    };

    /**
     * AJAX image upload
     * @returns {undefined}
     */
    GplCart.onload.handleFileUpload = function () {

        if (!$.fn.fileupload) {
            return;
        }

        var fileinput = $('#fileinput'), container = $(theme_settings.image_container);

        if (fileinput.length === 0) {
            return;
        }

        fileinput.fileupload({
            dataType: 'json',
            url: GplCart.settings.base + 'ajax',
            formData: {
                type: fileinput.attr('data-entity-type'),
                action: 'uploadImageAjax',
                token: GplCart.settings.token
            },
            done: function (e, data) {
                if (typeof data === 'object' && 'result' in data && data.result.files) {
                    $.each(data.result.files, function (index, file) {
                        if (file.html) {
                            container.append(file.html);
                        }
                    });
                    container.find('input[name$="[weight]"]').each(function (i) {
                        $(this).val(i);
                    });
                }
            }
        });
    };

    /**
     * Set up Google Map
     * @returns {undefined}
     */
    GplCart.onload.drawMap = function () {

        if (!GplCart.settings.map) {
            return;
        }

        if (GplCart.settings.map.address) {
            $.each(GplCart.settings.map.address, function (type, address) {
                GplCart.gmap(address, false, GplCart.settings.map.key, 'map-container-' + type);
            });
        }

        if (GplCart.settings.map[0] && GplCart.settings.map[1]) {
            GplCart.gmap(GplCart.settings.map[0], GplCart.settings.map[1], GplCart.settings.map.key);
        }
    };

    /**
     * Handles CLI terminal
     * @returns {undefined}
     */
    GplCart.onload.handleTerminal = function () {

        if (!$.fn.puiterminal) {
            return;
        }

        var handler;

        $('*[data-terminal]').click(function () {
            setModal('<div id="terminal"></div>', 'terminal-wrapper');
            $('#terminal').puiterminal({
                prompt: '> ',
                handler: function (request, response) {
                    request = $.trim(request);
                    if (request === 'clear') {
                        $('#terminal').puiterminal('clear');
                        return false;
                    }

                    handler = this;

                    $.ajax({
                        method: 'POST',
                        url: GplCart.settings.urn,
                        data: {
                            command: 'gplcart ' + request,
                            cli_token: GplCart.settings.token
                        },
                        success: function (data) {
                            response.call(handler, data);
                        }
                    });
                }
            });
            return false;
        });
    };

    /**
     * Makes uploaded images sortable
     * @returns {undefined}
     */
    GplCart.onload.handleSortableImages = function () {

        var settings = {
            items: '> div > div',
            handle: '.handle',
            stop: function () {
                $('input[name$="[weight]"]').each(function (i, v) {
                    $(this).val(i);
                });
            }
        };

        $(theme_settings.image_container).sortable(settings);
    };

    /**
     * Makes sortable table rows containing weigth value
     * @returns {undefined}
     */
    GplCart.onload.handleSortableTableWeigth = function () {

        var weight = {}, selector = $('table[data-sortable-weight="true"] tbody');

        if (selector.length === 0) {
            return;
        }

        selector.sortable({
            cursor: 'n-resize',
            handle: '.handle',
            stop: function () {

                selector.find('tr').each(function (i) {
                    weight[$(this).attr('data-id')] = i;
                });

                $.ajax({
                    data: {
                        action: 'weight',
                        selected: weight,
                        token: GplCart.settings.token
                    },
                    type: 'POST',
                    url: GplCart.settings.urn,
                    success: function (data) {
                        if (typeof data === 'object' && data.success) {
                            setAlert(data.success, 'success');
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
    };

    /**
     * Makes sortable table rows containing weigth input
     * @returns {undefined}
     */
    GplCart.onload.handleSortableTableWeigthInput = function () {

        var message, selector = $('table[data-sortable-input-weight="true"] tbody');

        if (selector.length === 0) {
            return;
        }

        selector.sortable({
            handle: '.handle',
            stop: function () {
                $('input[name$="[weight]"]').each(function (i) {
                    $(this).val(i);
                    $(this).closest('tr').find('td .weight').text(i);
                });
                message = GplCart.text('Changes will not be saved until the form is submitted');
                setAlert(message, 'warning');
            }
        });
    };

    /**
     * Checks status checkbox when the corresponding "required" checkbox is checked
     * @returns {undefined}
     */
    GplCart.onload.ensureCountryRequiredStatus = function () {
        $('table.country-format input[name$="[required]"]').click(function () {
            if ($(this).is(':checked')) {
                $(this).closest('tr').find('input[name$="[status]"]').prop('checked', true);
            }
        });
    };

    /**
     * Setup code mirror plugin
     * @returns {undefined}
     */
    GplCart.onload.handleCodemirror = function () {

        if (typeof CodeMirror === 'undefined') {
            return;
        }

        var textarea,
                map,
                ext,
                mode,
                settings,
                default_settings,
                readonly = false,
                element = $('*[data-codemirror="true"]');

        textarea = element.get(0);

        if ($.isEmptyObject(textarea)) {
            return;
        }

        map = {
            css: {name: 'css'},
            twig: {name: 'twig'},
            js: {name: 'javascript'},
            php: {name: 'htmlmixed'}
        };

        if (GplCart.settings.editor) {

            if (GplCart.settings.editor.file_extension) {
                ext = GplCart.settings.editor.file_extension;
            }

            if (GplCart.settings.editor.readonly) {
                readonly = true;
            }
        }

        mode = map[ext] || map.php;

        default_settings = {
            mode: mode,
            lineNumbers: true,
            theme: 'dracula',
            readOnly: readonly
        };

        // Allow to rewrite default setting with inline configuration
        settings = element.data('codemirror-settings') || {};

        if (typeof settings === 'object') {
            settings = $.extend(default_settings, settings);
        }

        CodeMirror.fromTextArea(textarea, settings);
    };

    /**
     * Adds colorpicker to the field
     * @returns {undefined}
     */
    GplCart.onload.handleColorpicker = function () {
        $('.input-group.color').colorpicker();
    };

    /**
     * Updates categories depending on chosen store
     * @returns {undefined}
     */
    GplCart.onload.handleUpdateCategories = function () {

        var i, g, cats, options,
                store = $('select[name$="[store_id]"]'),
                category = $('select[name$="[category_id]"]');

        store.change(function () {
            $.ajax({
                url: GplCart.settings.urn,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'categories',
                    token: GplCart.settings.token,
                    store_id: $(this).find('option:selected').val()
                },
                beforeSend: function () {
                    store.prop('disabled', true);
                    category.prop('disabled', true);
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
                        category.html(options).selectpicker('refresh');
                    }
                },
                complete: function () {
                    store.prop('disabled', false).selectpicker('refresh');
                    category.prop('disabled', false).selectpicker('refresh');
                }
            });
        });
    };

    /**
     * Adds a datepicker popup to the field
     * @returns {undefined}
     */
    GplCart.onload.handleDatepicker = function () {

        var el = $('[data-datepicker="true"]');
        var settings = {dateFormat: 'dd.mm.yy'},
                inline_settings = el.data('datepicker-settings') || {};

        if (typeof inline_settings === 'object') {
            settings = $.extend(settings, inline_settings);
        }

        el.datepicker(settings);
    };

    /**
     * Loads product fields via AJAX
     * @param {String} id
     * @returns {undefined}
     */
    var loadProductFields = function (id) {
        $.ajax({
            dataType: 'html',
            url: GplCart.settings.urn + '?product_class_id=' + id,
            success: function (data) {
                $('#attribute-form-wrapper').html($(data).find('div#attribute-form').html());
                $('#option-form-wrapper').html($(data).find('#option-form').html());
                $('.selectpicker').selectpicker('show');
            },
            error: function () {
                alert(GplCart.text('Unable to load product class fields'));
            }
        });
    };

    /**
     * Marks those images in modal which are already set in product options
     * @returns {undefined}
     */
    var markSelectedCombinationImage = function (modal) {
        var path;
        modal.find('img').each(function () {
            path = $(this).attr('data-file-path');
            if ($('#option-form-wrapper tbody input[name$="[path]"][value="' + path + '"]').length) {
                $(this).css('opacity', 0.5);
            }
        });
    };

    /**
     * Check if the button already has an image.
     * If so, remove it
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
     * Updates product fields when product class was changed
     * @returns {undefined}
     */
    GplCart.onload.updateProductClassFields = function () {
        var val;
        $('[name$="[product_class_id]"]').change(function () {
            val = $(this).val();
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
    GplCart.onload.updateProductFields = function () {
        $(document).on('click', '.refresh-fields', function () {
            loadProductFields($('[name$="[product_class_id]"]').val());
            return false;
        });
    };

    /**
     * Removes a product field
     * @returns {undefined}
     */
    GplCart.onload.removeProductField = function () {
        $(document).on('click', '#product-class-fields input[name$="[remove]"]', function () {
            $(this).closest('tr').toggleClass('danger', this.checked);
        });
    };

    /**
     * Returns a string containing HTML of the product combination row to be appended
     * @returns {String}
     */
    var htmlProductCombinationRow = function () {

        var html = '', index = $('#option-form-wrapper tbody tr').length + 1;

        html += '<tr>';

        $('#option-form-wrapper tfoot select').each(function () {
            html += '<td class="field-title">';
            html += '<select data-live-search="true" class="form-control selectpicker" name="product[combination][' + index + '][fields][' + $(this).attr('data-field-id') + ']">';
            html += $(this).html();
            html += '</select>';
            html += '</td>';
        });

        html += '<td>';
        html += '<input maxlength="255" class="form-control" name="product[combination][' + index + '][sku]" value="" placeholder="' + GplCart.text('Generate automatically') + '">';
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

        return html;
    };

    /**
     * Returns HTML of image browser modal
     * @returns {String}
     */
    var htmlProductImageModal = function () {

        var src,
                path,
                html = '',
                images = $(theme_settings.image_container).find('.thumb');

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
        return html;
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
     * Returns HTML of selected related products
     * @param {Object} item
     * @returns {String}
     */
    var htmlRelatedProducts = function (item) {

        var html = '';

        html += '<span class="related-product-item tag">';
        html += '<input type="hidden" name="product[related][]" value="' + item.value + '">';
        html += '<span class="btn btn-default">';
        html += '<a target="_blank" href="' + item.url + '">' + item.label + '</a> <span class="badge">';
        html += '<i class="fa fa-times remove"></i>';
        html += '</span></span>';
        html += '</span>';

        return html;
    };

    /**
     * Adds one more combination row to the table
     * @returns {undefined}
     */
    GplCart.onload.handleProductAddCombination = function () {
        $(document).on('click', '#option-form-wrapper .add-option-combination', function () {
            $('#option-form-wrapper table tbody').append(htmlProductCombinationRow());
            $('.selectpicker').selectpicker();
            return false;
        });
    };

    /**
     * Ensure that only one combination default radio button is selected
     * @returns {undefined}
     */
    GplCart.onload.checkDefaultProductCombination = function () {

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
    GplCart.onload.deleteProductCombination = function () {
        $(document).on('click', '#option-form-wrapper .remove-option-combination', function () {
            $(this).closest('tr').remove();
            return false;
        });
    };

    /**
     * Highlight rows with disabled option combinations
     * @returns {undefined}
     */
    GplCart.onload.markProductCombinationStatus = function () {
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
    GplCart.onload.selectProductCombinationImage = function () {

        var modal, html;

        $(document).on('click', '#option-form-wrapper .select-image', function () {

            if (toggleCombinationImageButton($(this))) {
                return false;
            }

            html = htmlProductImageModal();

            if (html.length === 0) {
                return false;
            }

            setModal(html, 'select-image-modal');
            modal = $('#select-image-modal').attr('data-active-row', $(this).closest('tr').index());
            markSelectedCombinationImage(modal);
            return false;
        });
    };

    /**
     * Sets a selected option combination image
     * @returns {undefined}
     */
    GplCart.onload.setProductCombinationImage = function () {
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
     * Removes a related product item
     * @returns {undefined}
     */
    GplCart.onload.removeRelated = function () {
        $(document).on('click', '.related-product-item .remove', function () {
            $(this).closest('.related-product-item').remove();
        });
    };

    /**
     * Adds autocomplete functionality to the related products input
     * @returns {undefined}
     */
    GplCart.onload.handleAutocompleteRelatedProducts = function () {

        var params, input = $('.related-product');
        if (input.length === 0) {
            return;
        }
        input.autocomplete({
            minLength: 2,
            source: function (request, response) {
                params = {
                    status: 1,
                    term: request.term,
                    action: 'getProductsAjax',
                    token: GplCart.settings.token,
                    store_id: $('select[name$="[store_id]"] option:selected').val()
                };
                $.post(GplCart.settings.base + 'ajax', params, function (data) {
                    response($.map(data, function (value, key) {
                        return {
                            url: value.url,
                            value: value.product_id,
                            label: value.title ? value.title + ' (' + value.product_id + ')' : '--'
                        };
                    }));
                });
            },
            select: function (event, ui) {
                $('#related-products').append(htmlRelatedProducts(ui.item));
                $('.related-product').val('');
                return false;
            }
        }).autocomplete("instance")._renderItem = function (ul, item) {
            return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
        };
    };

    /**
     * Adds autocomplete functionality to collection item fields
     * @returns {undefined}
     */
    GplCart.onload.handleAutocompleteCollectionItem = function () {

        var params = {},
                input = $('form#edit-collection-item input[name$="[input]"]'),
                value = $('form#edit-collection-item input[name$="[value]"]');

        if (input.length === 0 || !GplCart.settings.collection) {
            return;
        }

        input.autocomplete({
            minLength: 2,
            source: function (request, response) {
                params = {
                    term: request.term,
                    token: GplCart.settings.token,
                    action: 'getCollectionItemAjax',
                    collection_id: GplCart.settings.collection.collection_id
                };
                $.post(GplCart.settings.base + 'ajax', params, function (data) {
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
     * Adds autocomplete functionality to a user input
     * @returns {undefined}
     */
    GplCart.onload.handleAutocompleteUser = function () {

        var params, input = $('[data-autocomplete-source="user"]');

        if (input.length === 0) {
            return;
        }

        input.autocomplete({
            minLength: 2,
            source: function (request, response) {
                params = {
                    term: request.term,
                    action: 'getUsersAjax',
                    token: GplCart.settings.token
                };
                $.post(GplCart.settings.base + 'ajax', params, function (data) {
                    response($.map(data, function (value, key) {
                        return {
                            value: value.email,
                            label: value.email
                        };
                    }));
                });
            },
            select: function (event, ui) {
                input.val(ui.item.label);
                return false;
            }
        }).autocomplete("instance")._renderItem = function (ul, item) {
            return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
        };
    };

    /**
     * Adds autocomplete functionality to a product input
     * @returns {undefined}
     */
    GplCart.onload.handleAutocompleteProduct = function () {

        var params,
                input = $('[data-autocomplete-source="product"]'),
                inputId = $('[data-autocomplete-target="product"]');

        if (input.length === 0) {
            return;
        }

        input.autocomplete({
            minLength: 2,
            source: function (request, response) {

                params = {
                    term: request.term,
                    action: 'getProductsAjax',
                    token: GplCart.settings.token
                };

                $.post(GplCart.settings.base + 'ajax', params, function (data) {

                    response($.map(data, function (value, key) {
                        return {
                            value: value.product_id,
                            label: value.title ? value.title + ' (' + value.product_id + ')' : '--'
                        };
                    }));
                });
            },
            select: function (event, ui) {
                input.val(ui.item.label);
                inputId.val(ui.item.value);
                return false;
            },
            search: function () {
                inputId.val('');
            }
        }).autocomplete("instance")._renderItem = function (ul, item) {
            return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
        };
    };

    /**
     * Init Google Map on address details click
     * @returns {undefined}
     */
    GplCart.onload.handleAddressDetails = function () {
        var address_id, id;
        $('[data-address-details]').click(function () {
            address_id = $(this).data('address-details');
            if (GplCart.settings.map && GplCart.settings.map.key && GplCart.settings.map.addresses[address_id]) {
                id = 'map-container-address-' + address_id;
                GplCart.gmap(GplCart.settings.map.addresses[id], '', GplCart.settings.map.key, id);
            }
        });
    };

})(window, document, GplCart, jQuery);