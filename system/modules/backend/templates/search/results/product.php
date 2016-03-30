<div class="media product-search-results">
  <div class="media-left"><img class="media-object" src="<?php echo $this->escape($product['thumb']); ?>"></div>
  <div class="media-body">
    <div class="media-heading">
      <div class="title">
        <a target="_blank" href="<?php echo $this->escape($product['url']); ?>">
          <?php echo $this->escape($product['title']); ?>
        </a>
      </div>
      <div class="price"><?php echo $this->text('Base price'); ?>: <?php echo $product['price_formatted']; ?></div>
      <div class="store"><?php echo $this->text('Store'); ?>: <?php echo $this->escape($product['store_name']); ?></div>
      <div class="status">
        <?php if(empty($product['status'])) { ?>
        <span class="text-danger"><?php echo $this->text('Disabled'); ?></span>
        <?php } else { ?>
        <span class="text-success"><?php echo $this->text('Enabled'); ?></span>
        <?php } ?>
      </div>
    </div>
  </div>
</div>