<?php if (!empty($products)) { ?>
<div class="panel panel-default related-products">
  <div class="panel-heading"><?php echo $this->text('Related'); ?></div>
  <div class="panel-body">
    <div class="row products" data-slider="true" data-slider-settings='{
        "item": 4,
        "pager": false,
        "autoWidth": false,
        "slideMargin": 0
    }'>
      <?php foreach ($products as $product) { ?>
      <?php echo $product; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>
