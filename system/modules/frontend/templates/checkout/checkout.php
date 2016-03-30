
<div id="checkout-form-wrapper"><?php echo $checkout_form; ?></div>

<script>
$(document).ready(function(){
    
    var clicked, queueSubmit;

    $(document).on('focus', 'form#checkout [name$="[quantity]"]', function(){
        clearTimeout(queueSubmit);
    });
    
    $(document).on('blur', 'form#checkout [name$="[quantity]"]', function(){
        queueSubmit = setTimeout(function(){
            
            $('form#checkout').append($("<input type='hidden'>").attr({name: 'update', value: 1}));
            $('form#checkout').submit();
        
        }, 100);
    });
    
    $(document).on('change', 'form#checkout input[type="radio"], form#checkout select', function(){
        $('form#checkout').submit();
    });
    
    $(document).on('click', 'form#checkout :submit', function(e) {
        clicked = $(this).attr('name');
        $(this).closest('form').append($("<input type='hidden'>").attr({name: $(this).attr('name'), value: $(this).attr('value')})); 
    });
    
    $(document).off('submit').on('submit', 'form#checkout', function(e) {
        
        if(clicked === 'save' || clicked === 'login') {
            return true;
        }
        
        e.preventDefault();
        
        $.ajax({
            type: 'POST',
            url: '<?php $urn; ?>',
            dataType: 'html',
            data: $('form#checkout').serialize(),
            beforeSend: function(){
                $('form#checkout :input').prop('disabled', true);
            },
            success: function(data) {
                if(data) {
                    $('#checkout-form-wrapper').html(data);
                }
                
                $('form#checkout :input').prop('disabled', false);
            },
            error: function(){}
        });
    });
});
</script>