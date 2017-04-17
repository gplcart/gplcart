<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>

<div id="cart-preview">
  <?php if (empty($cart['items'])) { ?>
  <div class="row items">
    <div class="col-md-12"><?php echo $this->text('Shopping cart is empty.'); ?></div>
  </div>
  <?php } else { ?>
  <div class="row items">
    <div class="col-md-12 pre-scrollable">
      <?php foreach ($cart['items'] as $item) { ?>
      <form method="post" class="form-horizontal">
        <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
        <input type="hidden" name="cart[cart_id]" value="<?php echo $this->e($item['cart_id']); ?>">
        <div class="media cart-item">
          <div class="media-left image col-md-2">
            <img class="media-object thumbnail img-responsive" src="<?php echo $this->e($item['thumb']); ?>">
            <button title="<?php echo $this->text('Remove'); ?>" class="btn btn-default" data-ajax="true" name="remove_from_cart" value="1">
              <i class="fa fa-trash"></i>
            </button>
          </div>
          <div class="media-body info">
            <div class="media-heading"><?php echo $this->e($item['product']['title']); ?></div>
            <p><?php echo $this->e($item['sku']); ?></p>
            <p><?php echo $this->e($item['quantity']); ?> X <?php echo $this->e($item['price_formatted']); ?> = <?php echo $this->e($item['total_formatted']); ?></p>
          </div>
        </div>
      </form>
      <?php } ?>
    </div>
  </div>
  <hr>
  <div class="row subtotal">
    <div class="col-md-12">
      <b><?php echo $this->text('Subtotal'); ?></b> : <span id="cart-preview-subtotal"><?php echo $this->e($cart['total_formatted']); ?></span>
      <div class="help-block"><?php echo $this->text('Final price will be shown on checkout page'); ?></div>
    </div>
  </div>
  <div class="row buttons">
    <div class="col-md-12 checkout">
      <a href="<?php echo $this->url('checkout'); ?>" class="btn btn-block btn-success"><?php echo $this->text('Cart / Checkout'); ?></a>
    </div>
  </div>
  <?php } ?>
</div>