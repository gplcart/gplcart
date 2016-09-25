<?php if (!empty($products)) { ?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Featured products'); ?></div>
  <div class="panel-body">
<div class="products row">
  <?php foreach ($products as $product) { ?>
  <?php echo $product['rendered']; ?>
  <?php } ?>
</div>
  </div>
</div>
<?php } ?>

