<?php if (!empty($product['images'])) { ?>
<ul class="list-unstyled" data-slider="true" data-slider-settings='{
    "gallery":true,
    "item":1,
    "loop":true,
    "thumbItem":9,
    "slideMargin":0,
    "currentPagerPosition":"left"
}' data-slider-gallery='{
    "download":false,
    "actualSize":false,
    "scale":false,
    "autoplayControls":false
}'>
  <?php foreach ($product['images'] as $image) { ?>
  <li class="thumb" data-thumb="<?php echo $this->escape($image['thumb']); ?>" data-src="<?php echo $this->escape($image['url']); ?>">
    <img src="<?php echo $this->escape($image['thumb']); ?>">
  </li>
  <?php } ?>
</ul>
<?php } ?>