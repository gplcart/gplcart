<div class="panel panel-default">
  <div class="panel-heading">
    <?php echo $this->text('Summary'); ?>
  </div>
  <div class="panel-body">
    <ul class="list-unstyled">
      <li>
        <?php echo $this->text('Orders'); ?>:
        <?php if ($this->access('order')) { ?>
        <a href="<?php echo $this->url('admin/sale/order'); ?>"><?php echo $order_total; ?></a>
        <?php } else { ?>
        <?php echo $order_total; ?>
        <?php } ?>
      </li>
      <li>
        <?php echo $this->text('Users'); ?>:
        <?php if ($this->access('user')) { ?>
        <a href="<?php echo $this->url('admin/user'); ?>"><?php echo $user_total; ?></a>
        <?php } else { ?>
        <?php echo $user_total; ?>
        <?php } ?>
      </li>
      <li>
        <?php echo $this->text('Reviews'); ?>:
        <?php if ($this->access('review')) { ?>
        <a href="<?php echo $this->url('admin/content/review'); ?>"><?php echo $review_total; ?></a>
        <?php } else { ?>
        <?php echo $review_total; ?>
        <?php } ?>
      </li>
      <li>
        <?php echo $this->text('Products'); ?>:
        <?php if ($this->access('product')) { ?>
        <a href="<?php echo $this->url('admin/content/product'); ?>"><?php echo $product_total; ?></a>
        <?php } else { ?>
        <?php echo $product_total; ?>
        <?php } ?>
      </li>
    </ul>
  </div>
</div>