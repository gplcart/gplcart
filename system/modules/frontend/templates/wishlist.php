<div class="panel panel-default wishlist">
  <div class="panel-body">
    <?php if (empty($products)) { ?>
    <?php echo $this->text('Your wishlist is empty. <a href="!href">Continue shopping</a>', array(
        '!href' => $this->url('/'))); ?>
    <?php } else { ?>
    <?php echo $products; ?>
    <?php } ?>
  </div>
</div>
