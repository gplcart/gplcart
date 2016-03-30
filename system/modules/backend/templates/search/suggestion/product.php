<span onclick="window.location.href = '<?php echo $product['url']; ?>';" class="media product-search-suggestion">
  <span class="media-left"><img class="media-object" src="<?php echo $product['thumb']; ?>"></span>
  <span class="media-body">
    <span class="media-heading small">
      <span class="title"><?php echo $this->truncate($this->escape($product['title'])); ?></span>
      <span class="price small"><?php echo $product['price_formatted']; ?></span>
    </span>
  </span>
</span>