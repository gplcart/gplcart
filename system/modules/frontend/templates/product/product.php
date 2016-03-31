<div class="row">
  <div class="col-md-5">
    <div class="row">
      <div class="col-md-12">
        <?php echo $images; ?>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="row">
      <div class="col-md-6">
        <div id="sku" class="small"><?php echo $this->escape($product['sku']); ?></div>
      </div>
      <div class="col-md-6 text-right">
        <?php echo $share; ?>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <h1 class="h3"><?php echo $this->escape($product['title']); ?></h1>
      </div>
    </div>
    
    <div class="panel panel-default">
      
      <div class="panel-body">
    
    <div class="row">
      <div class="col-md-12">
        <div id="price" class="h3"><?php echo $price; ?></div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12"><?php echo $rating; ?></div>
    </div>
    <div class="row">
      <div class="col-md-12"><?php echo $cart_form; ?></div>
    </div>
        
      </div>
  
    </div>
      
    
    <?php if ($shipping_quotes) {
    ?>
        <?php echo $shipping_quotes;
    ?>
    <?php 
} ?>
  
  
  </div>
</div>
<?php if (!empty($product['description'])) {
    ?>
<div class="row">
  <div class="col-md-12">
      <h2 class="h3"><?php echo $this->text('Description');
    ?></h2>
      <?php echo $this->xss($product['description']);
    ?>
  </div>
</div>
<?php 
} ?>
<?php if (!empty($related)) {
    ?>
<?php echo $related;
    ?>
<?php 
} ?>
<?php if (!empty($recent)) {
    ?>
<?php echo $recent;
    ?>
<?php 
} ?>
<?php if (!empty($reviews)) {
    ?>
<?php echo $reviews;
    ?>
<?php 
} ?>



<script>
$(function () {
    
    $('.star-rating.static').tooltip();

    $('form#add-to-cart').unbind('submit').submit(function (e) {

        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: '<?php $urn; ?>',
            dataType: 'json',
            data: $(this).serialize(),
            success: function (data) {
                if (typeof data === 'object') {
                    if ('preview' in data) {
                        modal(data.preview, 'cart-preview', '<?php echo $this->text('Your cart'); ?>');
                        $('#cart-quantity-summary').text(data.quantity);
                    }
                }
            },
            error: function () {}
        });
    });

    $('form#add-to-cart [name^="product[options]"]').change(function () {

        var values = $('[name^="product[options]"]:checked, [name^="product[options]"] option:selected').map(function () {
            return this.value;
        }).get();

        $.ajax({
            url: '<?php echo $this->url('ajax'); ?>',
            dataType: 'json',
            method: 'post',
            data: {
                token: '<?php echo $token; ?>',
                action: 'switchProductOptions',
                values: values,
                product_id: '<?php echo $product['product_id']; ?>',
            },
            success: function (data) {
                if (!jQuery.isEmptyObject(data)) {
                    if ('message' in data && data.message) {

                    }

                    if ('combination' in data) {
                        if (data.combination.sku) {
                            $('#sku').text(data.combination.sku);
                        }

                        $('#price').text(data.combination.price);

                        if ('image' in data.combination) {
                            $('#main-image').attr('src', data.combination.image);
                        }
                    }
                }
            },
            error: function (error) {}
        });
    });
});

</script>

