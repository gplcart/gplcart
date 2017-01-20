/* global GplCart, Backend  */
(function (document, GplCart, $) {

    Backend.include.product = {attach: {}, helper: {}, html: {}};

    /**
     * Updates brand and catalog options depending on the selected store
     * @returns {undefined}
     */
    Backend.include.product.attach.updateCategories = function () {

        var id, url, catOptions, brandOptions, selectedCat, selectedBrand;

        $('select[name$="[store_id]"]').change(function () {

            selectedCat = GplCart.settings.product.category_id || '';
            selectedBrand = GplCart.settings.product.brand_category_id || '';

            url = GplCart.settings.urn;
            id = $(this).find('option:selected').val();

            $.get(url, {store_id: id}, function (data) {

                if (!('catalog' in data) || !('brand' in data)) {
                    alert(GplCart.text('An error occurred'));
                    return false;
                }

                catOptions = Backend.html.options(data.catalog, selectedCat);
                $('select[name$="[category_id]"]').html(catOptions).selectpicker('refresh');

                brandOptions = Backend.html.options(data.brand, selectedBrand);
                $('select[name$="[brand_category_id]"]').html(brandOptions).selectpicker('refresh');
            });

        });
    };

    /**
     * Loads product fields via AJAX
     * @param {String} id
     * @returns {undefined}
     */
    Backend.include.product.helper.loadFields = function (id) {

        var opForm, attrForm;

        $.ajax({
            url: GplCart.settings.urn + '?product_class_id=' + id,
            dataType: 'html',
            success: function (data) {

                opForm = $(data).find('#option-form').html();
                attrForm = $(data).find('div#attribute-form').html();

                $('#attribute-form-wrapper').html(attrForm);
                $('#option-form-wrapper').html(opForm);
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
    Backend.include.product.helper.markSelectedCombinationImage = function (modal) {

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
    Backend.include.product.helper.toggleCombinationImageButton = function (button) {

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
    Backend.include.product.attach.updateFieldsProductClass = function () {

        var val,
                wrapper = '#option-form-wrapper',
                selector = $('[name$="[product_class_id]"]');

        selector.change(function () {
            val = $(this).val();
            Backend.include.product.helper.loadFields(val);

            if (val) {
                $('body,html').animate({scrollTop: $(wrapper).offset().top - 60});
            }
        });
    };

    /**
     * Updates product fields on demand
     * @returns {undefined}
     */
    Backend.include.product.attach.updateFields = function () {

        var id;

        $(document).on('click', '.refresh-fields', function () {
            id = $('[name$="[product_class_id]"]').val();
            Backend.include.product.helper.loadFields(id);
            return false;
        });
    };

    /**
     * Makes product fields sortable
     * @returns {undefined}
     */
    Backend.include.product.attach.sortableFields = function () {

        var message;

        $('#product-class-fields tbody').sortable({
            handle: '.handle',
            stop: function () {

                $('input[name$="[weight]"]').each(function (i) {
                    $(this).val(i);
                    $(this).closest('tr').find('td .weight').text(i);
                });

                message = GplCart.text('Changes will not be saved until the form is submitted');
                Backend.ui.alert(message, 'warning');
            }
        });
    };

    /**
     * Removes a product field
     * @returns {undefined}
     */
    Backend.include.product.attach.removeField = function () {

        var selector = '#product-class-fields input[name$="[remove]"]';

        $(document).on('click', selector, function () {
            $(this).closest('tr').toggleClass('danger', this.checked);
        });
    };

    /**
     * Returns a string containing HTML of the product combination row to be appended
     * @returns {String}
     */
    Backend.include.product.html.combinationRow = function () {

        var html = '',
                index = $('#option-form-wrapper tbody tr').length + 1;

        html += '<tr>';
        
        $('#option-form-wrapper tfoot select').each(function () {
            html += '<td class="field-title">';
            html += '<select data-live-search="true" class="form-control selectpicker" name="product[combination][' + index + '][fields][' + $(this).attr('data-field-id') + ']">';
            html += $(this).html();
            html += '</select>';
            html += '</td>';
        });

        html += '<td>';
        html += '<input maxlength="255" class="form-control" name="product[combination][' + index + '][sku]" value="">';
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
        html += '<a href="#" onclick="return false;" class="btn btn-default remove-option-combination"><i class="fa fa-trash"></i></a>';
        html += '</td>';
        html += '</tr>';

        return html;
    };

    /**
     * Returns HTML of image browser modal
     * @returns {String}
     */
    Backend.include.product.html.imageModal = function () {

        var src,
                path,
                html = '',
                images = $(Backend.settings.imageContainer).find('.thumb');

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
    Backend.include.product.html.combinationImage = function (src) {
        return '<img style="height:20px; width:20px;" src="' + src + '" class="img-responsive combination-image">';
    };

    /**
     * Returns HTML of selected related products
     * @param {Object} item
     * @returns {String}
     */
    Backend.include.product.html.relatedProduct = function (item) {

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
    Backend.include.product.attach.addCombination = function () {

        var row,
                tbody = '#option-form-wrapper table tbody',
                button = '#option-form-wrapper .add-option-combination';

        $(document).on('click', button, function () {
            row = Backend.include.product.html.combinationRow();
            $(tbody).append(row);
            $('.selectpicker').selectpicker();
            return false;
        });
    };

    /**
     * Deletes an option combination
     * @returns {undefined}
     */
    Backend.include.product.attach.deleteCombination = function () {

        var button = '#option-form-wrapper .remove-option-combination';

        $(document).on('click', button, function () {
            $(this).closest('tr').remove();
            return false;
        });
    };

    /**
     * Select an option combination image
     * @returns {undefined}
     */
    Backend.include.product.attach.selectCombinationImage = function () {

        var modal,
                position,
                html,
                button = '#option-form-wrapper .select-image';

        $(document).on('click', button, function () {

            if (Backend.include.product.helper.toggleCombinationImageButton($(this))) {
                return false;
            }

            html = Backend.include.product.html.imageModal();

            if (html.length === 0) {
                return false;
            }

            Backend.ui.modal(html, Backend.settings.imageModal);

            modal = $(Backend.settings.imageModal);

            // Memorize clicked row position
            position = $(this).closest('tr').index();
            modal.attr('data-active-row', position);

            Backend.include.product.helper.markSelectedCombinationImage(modal);
            return false;
        });
    };

    /**
     * Sets a selected option combination image
     * @returns {undefined}
     */
    Backend.include.product.attach.setCombinationImage = function () {

        var e, src, path, pos, html, image = 'img.combination-image';

        $(document).on('click', image, function () {

            src = $(this).attr('src');
            path = $(this).attr('data-file-path');

            pos = $(this).closest(Backend.settings.imageModal).attr('data-active-row');
            e = $('#option-form-wrapper tbody tr').eq(pos).find('.select-image');
            html = Backend.include.product.html.combinationImage(src);

            e.html(html);
            e.siblings('input[name$="[path]"]').val(path);
            e.siblings('input[name$="[thumb]"]').val(src);

            $(Backend.settings.imageModal).modal('hide');
        });
    };

    /**
     * Adds autocomplete functionality to the related products input
     * @returns {undefined}
     */
    Backend.include.product.attach.autocompleteRelated = function () {

        var params, html, input = $('.related-product');

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
                html = Backend.include.product.html.relatedProduct(ui.item);
                $('#related-products').append(html);
                $('.related-product').val('');
                return false;
            }

        }).autocomplete("instance")._renderItem = function (ul, item) {
            return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
        };

    };

    /**
     * Removes a related product item
     * @returns {undefined}
     */
    Backend.include.product.attach.removeRelated = function () {
        $(document).on('click', '.related-product-item .remove', function () {
            $(this).closest('.related-product-item').remove();
        });
    };

    /**
     * Call attached above methods when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        GplCart.attach(Backend.include.product);
    });

})(document, GplCart, jQuery);