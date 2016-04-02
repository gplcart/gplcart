<div class="row wishlist">
  <div class="col-md-12">
    <?php if ($products) {
    ?>
    <?php echo $products;
    ?>
    <?php 
} else {
    ?>
    <p><?php echo $this->text('Your wishlist is empty');
    ?></p>
    <a href="<?php echo $this->url('/');
    ?>" class="btn btn-default"><?php echo $this->text('Continue shopping');
    ?></a>
    <?php 
} ?>
  </div>
</div>
