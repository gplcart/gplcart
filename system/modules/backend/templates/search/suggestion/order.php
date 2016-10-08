<span class="suggestion-order-<?php echo $order['order_id']; ?>">
  <a href="<?php echo $this->url("admin/sale/order/{$order['order_id']}"); ?>">
  <b>#<?php echo $order['order_id']; ?></b>
  <?php echo $this->text('Created'); ?>: <?php echo $this->date($order['created']); ?>,
  <?php echo $this->text('Total'); ?>: <?php echo $order['total_formatted']; ?>
  </a>
  <?php if(!empty($order['is_new'])) { ?>
  <span class="label label-danger"><?php echo $this->text('new'); ?></span>
  <?php } ?>
</span>