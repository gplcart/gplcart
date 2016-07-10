<?php if (!empty($products)) { ?>
<div class="panel panel-default related-products">
  <div class="panel-heading"><?php echo $this->text('Related'); ?></div>
  <div class="panel-body">
    <div class="row multi-item-carousel products">
      <?php foreach ($products as $product) { ?>
      <?php echo $product; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>
