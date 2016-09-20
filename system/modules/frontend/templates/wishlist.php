<div class="row wishlist">
  <div class="col-md-12">
    <?php if (empty($products)) { ?>
    <?php echo $this->text('Your wishlist is empty. <a href="!href">Continue shopping</a>', array(
        '!href' => $this->url('/'))); ?>
    <?php } else { ?>
    <?php echo $products; ?>
    <?php } ?>
  </div>
</div>
