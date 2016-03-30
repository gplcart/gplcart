$(function () {
    $('.categories tbody').sortable({
        cursor: 'n-resize',
        handle: '.handle',
        stop: function () {

            var weight = {};
            $('.categories tbody tr').each(function (i) {
                var id = $(this).attr('data-category-id');
                weight[id] = i;
            });

            $.ajax({
                data: {action: 'weight', selected: weight, token: GplCart.settings.token},
                type: 'POST',
                url: GplCart.settings.urn,
                success: function (data) {
                    if ('success' in data) {
                        GplCart.theme.alert(data.success, 'success');

                        // update visible weight values
                        $.each(weight, function (i, v) {
                            $('tr[data-category-id=' + i + ']').find('td .weight').text(v);
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