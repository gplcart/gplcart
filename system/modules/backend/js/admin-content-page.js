$(function () {
    $('select[name$="[store_id]"]').change(function () {
        var storeId = $(this).find('option:selected').val();

        $.ajax({
            url: GplCart.settings.urn,
            method: 'POST',
            dataType: 'json',
            data: {token: GplCart.settings.token, action: 'categories', store_id: storeId},
            beforeSend: function () {
                $('select[name$="[store_id]"], select[name$="[category_id]"]').prop('disabled', true);
            },
            success: function (data) {
                if (typeof data === 'object') {

                    var options = '';
                    for (var g in data) {
                        options += '<optgroup label="' + g + '">';
                        var cats = data[g];
                        for (var i in cats) {
                            options += '<option value="' + i + '">' + cats[i] + '</option>';
                        }
                    }
                    $('select[name$="[category_id]"]').html(options).selectpicker('refresh');
                }
            },
            complete: function () {
                $('select[name$="[store_id]"], select[name$="[category_id]"]').prop('disabled', false).selectpicker('refresh');
            }
        });
    });


});