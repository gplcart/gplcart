<?php if (!empty($products)) { ?>
<div class="panel panel-default">
  <div class="panel-body">
    <div class="products row section">
      <?php foreach ($products as $product) { ?>
      <?php echo $product['rendered']; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>