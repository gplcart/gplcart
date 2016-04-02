<?php if (!empty($results)) {
    ?>
<?php echo $navbar;
    ?>
<?php echo $results;
    ?>
<?php if ($pager) {
    ?>
<div class="row">
  <div class="col-md-12">
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
<div class="row margin-top-20">
  <div class="col-md-12">
    <?php echo $this->text('No products found. Try another search keyword');
    ?>
  </div>
</div>
<?php 
} ?>