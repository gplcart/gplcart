<?php if (!empty($products)) { ?>
<div class="products row section">
  <?php foreach ($products as $product) { ?>
  <?php echo $product['rendered']; ?>
  <?php } ?>
</div>
<?php } ?>