GplCart.theme.productUpdateCategories = function (element) {

    var id = element.find('option:selected').val();
    var selectedCatId = ('category_id' in GplCart.settings.product) || '';
    var selectedBrandCatId = ('brand_category_id' in GplCart.settings.product) || '';

    $.get(GplCart.settings.urn, {store_id: id}, function (data) {

        var options = '';

        for (var i in data.catalog) {
            if (selectedCatId === i) {
                options += '<option value="' + i + '" selected>' + data.catalog[i] + '</option>';
            } else {
                options += '<option value="' + i + '">' + data.catalog[i] + '</option>';
            }
        }

        $('select[name$="[category_id]"]').html(options).selectpicker('refresh');

        var options = '';

        for (var i in data.brand) {
            if (selectedBrandCatId === i) {
                options += '<option value="' + i + '" selected>' + data.brand[i] + '</option>';
            } else {
                options += '<option value="' + i + '">' + data.brand[i] + '</option>';
            }
        }

        $('select[name$="[brand_category_id]"]').html(options).selectpicker('refresh');
    });
};

GplCart.theme.productLoadFields = function (classId, fieldType) {

    $.ajax({
        url: GplCart.settings.urn + '?product_class_id=' + classId,
        dataType: 'html',
        success: function (data) {

            var attrForm = $(data).find('div#attribute-form').html();
            var opForm = $(data).find('#option-form').html();

            $('#attribute-form-wrapper').html(attrForm);
            $('#option-form-wrapper').html(opForm);
            $('.selectpicker').selectpicker('show');
        },
        error: function (error) {
            alert(GplCart.text('Unable to load product class fields'));
        }
    });
};

$(function () {

    /**************************************** Product class fields ****************************************/

    $('.fields tbody').sortable({
        handle: '.handle',
        stop: function () {
            $('input[name$="[weight]"]').each(function (i) {
                $(this).val(i);
                $(this).closest('tr').find('td .weight').text(i);
            });

            GplCart.theme.alert(GplCart.text('Please submit the form to save the changes you made'), 'warning');
        }
    });

    $(document).on('click', '.fields tbody input[name$="[remove]"]', function () {
        $(this).closest('tr').toggleClass('danger', this.checked);
    });

    /**************************************** Product edit form ****************************************/

    if ($('form#edit-product').lenght && $('select[name$="[store_id]"] option:selected').val() !== "") {
        GplCart.theme.productUpdateCategories($('select[name$="[store_id]"]'));
    }

    $('form#edit-product select[name$="[store_id]"]').change(function () {
        GplCart.theme.productUpdateCategories($(this));
    });

    // Load product class fields
    $('form#edit-product [name$="[product_class_id]"]').change(function () {
        GplCart.theme.productLoadFields($(this).val(), false);
    });

    // Refresh product class fields
    $(document).on('click', 'form#edit-product .refresh-fields', function () {
        GplCart.theme.productLoadFields($('form#edit-product [name$="[product_class_id]"]').val(), $(this).attr('data-field-type'));
        return false;
    });

    // Add new option combination
    $(document).on('click', 'form#edit-product #option-form-wrapper table tfoot .fa-plus', function () {

        var count = $('form#edit-product #option-form-wrapper table tbody tr').size() + 1;
        var html = '<tr>';

        $('form#edit-product #option-form-wrapper table tfoot select').each(function () {
            html += '<td class="active">';
            html += '<select data-live-search="true" class="form-control selectpicker" name="product[combination][' + count + '][fields][' + $(this).attr('data-field-id') + ']">';
            html += $(this).html();
            html += '</select>';
            html += '</td>';
        });

        html += '<td>';
        html += '<input maxlength="255" class="form-control" name="product[combination][' + count + '][sku]" value="">';
        html += '</td>';
        html += '<td>';
        html += '<input class="form-control" name="product[combination][' + count + '][price]" value="">';
        html += '</td>';
        html += '<td>';
        html += '<input class="form-control" name="product[combination][' + count + '][stock]" value="">';
        html += '</td>';
        html += '<td>';
        html += '<a href="#" onclick="return false;" class="btn btn-default select-image"><i class="fa fa-image"></i></a>';
        html += '<input type="hidden" name="product[combination][' + count + '][file_id]" value="">';
        html += '<input type="hidden" name="product[combination][' + count + '][path]" value="">';
        html += '<input type="hidden" name="product[combination][' + count + '][thumb]" value="">';
        html += '</td>';
        html += '<td>';
        html += '<a href="#" onclick="return false;" class="btn btn-danger btn-default"><i class="fa fa-minus"></i></a>';
        html += '</td>';
        html += '</tr>';

        $('form#edit-product #option-form-wrapper table tbody').append(html);
        $('.selectpicker').selectpicker();
        return false;
    });

    // Delete option combination
    $(document).on('click', 'form#edit-product #option-form-wrapper table tbody .fa-minus', function () {
        $(this).closest('tr').remove();
        return false;
    });

    // Select image for option combination
    $(document).on('click', 'form#edit-product #option-form-wrapper .select-image', function () {

        if ($(this).find('img').length) {
            $(this).html('<i class="fa fa-image"></i>');
            $(this).siblings('input').val('');
            return false;
        }

        var images = 0;

        var html = '<div class="row">';
        $('form#edit-product .image-container').find('.thumb').each(function () {

            var src = $(this).find('img').attr('src');
            var path = $(this).find('input[name$="[path]"]').val();

            html += '<div class="col-md-3">';
            html += '<div class="thumbnail">';
            html += '<img data-file-path="' + path + '" src="' + src + '" class="img-responsive combination-image">';
            html += '</div>';
            html += '</div>';

            images++;
        });

        html += '</div>';

        if (images) {
            GplCart.theme.modal(html, 'select-image-modal');
            $('form#edit-product #select-image-modal').attr('data-active-row', $(this).closest('tr').index()); // remember clicked row pos
            $('form#edit-product #select-image-modal img').each(function () {
                if ($('form#edit-product #option-form-wrapper tbody input[name$="[path]"][value="' + $(this).attr('data-file-path') + '"]').size()) {
                    $(this).css('opacity', 0.5);
                }
            });
        }
        return false;
    });

    // Set selected image
    $(document).on('click', 'form#edit-product img.combination-image', function () {

        var src = $(this).attr('src');
        var path = $(this).attr('data-file-path');
        var pos = $(this).closest('#select-image-modal').attr('data-active-row');

        var el = $('form#edit-product #option-form-wrapper tbody tr').eq(pos).find('.select-image');
        el.html('<img style="height:20px;width:20px;" src="' + src + '" class="img-responsive combination-image">');
        el.siblings('input[name$="[path]"]').val(path);
        el.siblings('input[name$="[thumb]"]').val(src);

        $('form#edit-product #select-image-modal').modal('hide');
    });

    $('form#edit-product .related-product').autocomplete({
        minLength: 2,
        source: function (request, response) {
            $.post(GplCart.settings.base + 'ajax',
                    {store_id: $('select[name$="[store_id]"] option:selected').val(),
                        status: 1,
                        term: request.term,
                        action: 'getProducts',
                        token: GplCart.settings.token}, function (data) {
                response($.map(data, function (value, key) {
                    return {
                        label: value.title ? value.title + ' (' + value.product_id + ')' : '--',
                        value: value.product_id,
                        url: value.url,
                    };
                }));
            });
        },
        select: function (event, ui) {

            var html = '<span class="related-product-item tag">';
            html += '<input type="hidden" name="product[related][]" value="' + ui.item.value + '">';
            html += '<span class="btn btn-default">';
            html += '<a target="_blank" href="' + ui.item.url + '">' + ui.item.label + '</a> <span class="badge">';
            html += '<i class="fa fa-times remove"></i>';
            html += '</span></span>';
            html += '</span>';

            $('form#edit-product #related-products').append(html);
            $('form#edit-product .related-product').val('');

            return false;
        }

    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
    };

    // Remove related product item
    $(document).on('click', 'form#edit-product .related-product-item .remove', function () {
        $(this).closest('.related-product-item').remove();
    });

    /*************************** Product listing ******************************/

    $(document).on('change keyup paste', 'form#products :input', function () {
        $(this).closest('tr').find('.save-row').removeClass('disabled');
    });

    $(document).on('click', 'form#products td .cancel-row', function (e) {
        $(this).closest('tr').remove();
    });

    $(document).on('click', 'form#products td .save-row', function (e) {

        var save = $(this);
        var tr = save.closest('tr');
        var inputs = tr.find(':input');

        var values = inputs.serialize() + '&' + $.param({
            token: GplCart.settings.token,
            save: 1
        });

        var message = GplCart.text('Validation errors');
        var messageType = 'danger';

        $.ajax({
            method: 'POST',
            processData: false,
            url: GplCart.settings.urn,
            dataType: 'json',
            data: values,
            beforeSend: function () {
                inputs.prop('disabled', true);
            },
            success: function (data) {
                if (typeof data === 'object') {
                    if ('success' in data) {
                        message = data.success;
                        messageType = 'success';
                        tr.find('.text_error').remove();
                    } else if ('error' in data) {

                        if (typeof data.error === 'string') {
                            message = data.error;
                            messageType = 'danger';
                        } else {
                            $.each(data.error, function (i, v) {
                                var hint = '<div class="small text_error text-danger">' + v + '</div>';
                                var input = tr.find(':input[name$="[' + i + ']"]');
                                input.nextAll('.help-block').remove();
                                input.after(hint);
                            });
                        }
                    }
                }

                GplCart.theme.alert(message, messageType);
            },
            complete: function () {
                inputs.prop('disabled', false);
                save.addClass('disabled');
            }
        });

        return false;
    });

    // Load inline combinations
    $('form#products .load-options').click(function () {

        var row = $(this).closest('tr');
        var productId = row.attr('data-product-id');

        $('tr.product-options-' + productId).remove();
        row.after('<tr class="product-options-' + productId + '"><td colspan="10"></td></tr>');

        $.ajax({
            method: 'POST',
            dataType: 'html',
            url: GplCart.settings.urn,
            data: {
                action: 'get_options',
                'product_id': productId,
                token: GplCart.settings.token
            },
            success: function (data) {
                if ($(data).find('td').length) {
                    $('tr.product-options-' + productId).replaceWith(data);
                    return false;
                }

                $('tr.product-options-' + productId).replaceWith('\
                <tr class="hidden product-options-' + productId + '">\n\
                <td colspan="10"></td></tr>');
            },
            error: function () {
                alert(GplCart.text('An error occurred'));
            },
            beforeSend: function () {
                GplCart.theme.loading(true);
            },
            complete: function () {
                GplCart.theme.loading(false);
            }
        });
    });
});