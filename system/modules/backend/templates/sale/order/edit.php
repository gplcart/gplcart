<div id="order-form-wrapper"><?php echo $order_form; ?></div>
<script>
$(function () {

    var clicked, queueSubmit;

    $(document).on('focus', 'form#edit-order [name$="[quantity]"]', function () {
        clearTimeout(queueSubmit);
    });

    $(document).on('blur', 'form#edit-order [name$="[quantity]"]', function () {
        queueSubmit = setTimeout(function () {

            $('form#edit-order').append($("<input type='hidden'>").attr({name: 'update', value: 1}));
            $('form#edit-order').submit();

        }, 100);
    });

    $(document).on('change', 'form#edit-order input[type="radio"], form#edit-order select', function () {
        $('form#edit-order').submit();
    });

    $(document).on('click', 'form#edit-order :submit', function (e) {
        clicked = $(this).attr('name');
        $(this).closest('form').append($("<input type='hidden'>").attr({name: $(this).attr('name'), value: $(this).attr('value')}));
    });

    $(document).off('submit').on('submit', 'form#edit-order', function (e) {

        if (clicked === 'save') {
            return true;
        }

        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: '<?php $urn; ?>',
            dataType: 'html',
            data: $('form#edit-order').serialize(),
            beforeSend: function () {
                $('form#edit-order :input').prop('disabled', true);
            },
            success: function (data) {
                if (data) {
                    $('#order-form-wrapper').html(data);
                }

                $('form#edit-order :input').prop('disabled', false);
            },
            error: function () {}
        });
    });

    // Clear input on focus, restore on blur
    $(document).on('focus', 'input[name="order[user]"]', function () {
        $(this).attr('data-user', $(this).val()).val('');
    });

    $(document).on('blur', 'input[name="order[user]"]', function () {
        $(this).val($(this).attr('data-user'));
    });

    $(document).on('focus', 'input[name="order[user]"]', function () {
        if (!$(this).data('autocomplete')) {
            $(this).autocomplete({
                minLength: 2,
                source: function (request, response) {

                    var storeId = $('select[name="order[store_id]"]').val();

                    $.post('<?php echo $urn; ?>', {search_user: request.term, store_id: storeId, token: '<?php echo $token; ?>'}, function (data) {

                        response($.map(data, function (value, key) {
                            return {
                                label: value.name + ' (' + value.email + ')',
                                value: value.user_id
                            }
                        }));
                    });
                },
                select: function (event, ui) {

                    $('input[name="order[user_id]"]').val(ui.item.value);
                    $('input[name$="order[user]"]').val(ui.item.label);

                    $.post('<?php echo $urn; ?>', {order_user_id: ui.item.value, token: '<?php echo $token; ?>'}, function (data) {
                        location.reload(false);
                    });

                    return false;
                }
            }).autocomplete('instance')._renderItem = function (ul, item) {
                return $('<li>').append('<a>' + item.label + '</a>').appendTo(ul);
            };
        }

    });
});
</script>