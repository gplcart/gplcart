$(function () {
    $('.field-values tbody').sortable({
        cursor: 'n-resize',
        handle: '.handle',
        stop: function () {

            var weight = {};
            $('.field-values tbody tr').each(function (i) {
                var id = $(this).attr('data-field-value-id');
                weight[id] = i;
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
                    if ('success' in data) {
                        GplCart.theme.alert(data.success, 'success');
                        $.each(weight, function (i, v) {
                            $('tr[data-field-value-id=' + i + ']').find('td .weight').text(v);
                        });
                    }
                },
                beforeSend: function () {
                    GplCart.theme.loading(true);
                },
                complete: function () {
                    GplCart.theme.loading(false);
                }
            });
        }
    });
});

