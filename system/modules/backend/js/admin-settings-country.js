$(function () {

    $('table.country-format input[name$="[required]"]').click(function () {
        if ($(this).is(':checked')) {
            $(this).closest('tr').find('input[name$="[status]"]').prop('checked', true);
        }
    });

    $('table.country-format tbody').sortable({
        cursor: 'n-resize',
        handle: '.handle',
        stop: function () {
            $('input[name$="[weight]"]').each(function (i) {
                $(this).val(i);
                $(this).closest('tr').find('td .weight').text(i);
            });
            
            GplCart.theme.alert(GplCart.text('Changes will not be saved until the form is submitted'), 'info');
        }
    });

});