<span class="media suggestion small" data-url="<?php echo $this->url("product/{$product['product_id']}"); ?>">
  <span class="media-left">
    <img class="media-object" src="<?php echo $this->escape($product['thumb']); ?>">
  </span>
  <span class="media-body">
    <span class="media-heading title">
      <?php echo $this->escape($product['title']); ?>
    </span>
    <span class="price">
      <?php echo $this->escape($product['price_formatted']); ?>
    </span>
  </span>
</span>