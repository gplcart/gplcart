<?php if (!empty($page['images'])) { ?>
<ul class="list-unstyled" data-slider="true" data-slider-settings='{
    "gallery":false,
    "item":1,
    "loop":true,
    "thumbItem":9,
    "slideMargin":0,
    "currentPagerPosition":"left"
}'>
  <?php foreach ($page['images'] as $image) { ?>
  <li class="thumb" data-thumb="<?php echo $this->escape($image['thumb']); ?>" data-src="<?php echo $this->escape($image['url']); ?>">
    <img src="<?php echo $this->escape($image['thumb']); ?>">
  </li>
  <?php } ?>
</ul>
<?php } ?>