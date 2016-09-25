<?php if (!empty($products)) { ?>
<div class="panel panel-default collection collection-product products">
  <div class="panel-heading"><?php echo $this->escape($title); ?></div>
  <div class="panel-body">
    <div class="row">
      <?php foreach ($products as $product) { ?>
      <?php echo $product['rendered']; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>

