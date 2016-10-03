<form action="https://www.2checkout.com/checkout/purchase" method="post">
  <input type="hidden" name="sid" value="<?php echo $settings['account']; ?>">
  <input type="hidden" name="mode" value="2CO">
  <input type="hidden" name="li_0_type" value="product">
  <input type="hidden" name="li_0_name" value="<?php echo $this->text('Order #@order_id', array('@order_id' => $order['order_id'])); ?>">
  <input type="hidden" name="li_0_product_id" value="<?php echo $order['order_id']; ?>">
  <input type="hidden" name="li_0_price" value="<?php echo $order['total_formatted_decimal']; ?>">
  <input type="hidden" name="li_0_quantity" value="1">
  <input type="hidden" name="li_0_tangible" value="N">
  <input type="hidden" name="currency_code" value="<?php echo $order['currency']; ?>">
  <?php if(!empty($settings['demo'])) { ?>
  <input type="hidden" name="demo" value="Y">
  <?php } ?>
  <input type="hidden" name="x_receipt_link_url" value="<?php echo $this->url("transaction/success/{$order['order_id']}", array(), true); ?>">
  <input class="btn btn-default btn-success" name="submit" type="submit" value="<?php echo $this->text('Pay via 2Checkout'); ?>">
</form>