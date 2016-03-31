<?php if ($images) {
    ?>
<div class="row section">
  <div class="col-md-2"><?php echo $images;
    ?></div>
  <?php if (!empty($category['description_1'])) {
    ?>
  <div class="col-md-10"><?php echo $this->xss($category['description_1']);
    ?></div>
  <?php 
}
    ?>
</div>
<?php 
} ?>
<?php if ($children) {
    ?>
<?php echo $children;
    ?>
<?php 
} ?>
<?php if ($products) {
    ?>
<?php echo $navbar;
    ?>
<?php echo $products;
    ?>
<?php if ($pager) {
    ?>
<div class="row">
  <div class="col-md-12 text-right">
  <?php echo $pager;
    ?>
  </div>
</div>
<?php 
}
    ?>
<?php 
} else {
    ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('This category has no products yet');
    ?>
  </div>
</div>
<?php 
} ?>
<?php if ($category['description_2']) {
    ?>
<div class="row section description-2">
  <div class="col-md-12">
    <?php echo $category['description_2'];
    ?>
  </div>
</div>
<?php 
} ?>

