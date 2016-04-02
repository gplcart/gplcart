<?php if (!empty($products)) {
    ?>
<div class="panel panel-default recent-products">
  <div class="panel-heading"><?php echo $this->text('Recently viewed');
    ?></div>
  <div class="panel-body">
    <div class="row multi-item-carousel products">
      <?php foreach ($products as $product) {
    ?>
      <?php echo $product['rendered'];
    ?>
      <?php 
}
    ?>
    </div>
  </div>
</div>
<?php 
} ?>
