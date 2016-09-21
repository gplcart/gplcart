<div id="cart-preview">
  <?php if(!empty($cart['items'])) { ?>
  <div class="row items">
    <div class="col-md-12 pre-scrollable">
      <?php foreach($cart['items'] as $item) { ?>
      <div class="media cart-item">
        <div class="media-left image col-md-2">
          <img class="media-object thumbnail img-responsive" alt="<?php echo $this->escape($item['product']['title']); ?>" src="<?php echo $this->escape($item['thumb']); ?>">
        </div>
        <div class="media-body info">
          <div class="media-heading"><?php echo $this->escape($item['product']['title']); ?></div>
          <p><?php echo $this->escape($item['sku']); ?></p>
          <p><?php echo $this->escape($item['quantity']); ?> X <?php echo $this->escape($item['price_formatted']); ?> = <?php echo $this->escape($item['total_formatted']); ?></p>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
  <hr>
  <div class="row subtotal">
    <div class="col-md-12">
      <b><?php echo $this->text('Subtotal'); ?></b> : <span class="price"><?php echo $cart['total_formatted']; ?></span>
      <div class="help-block"><?php echo $this->text('Final price will be shown on checkout page'); ?></div>
    </div>
  </div>
  <div class="row buttons">
    <div class="col-md-12 checkout">
    <a href="<?php echo $this->url('checkout'); ?>" class="btn btn-block btn-success"><?php echo $this->text('Cart / Checkout'); ?></a>
    </div>
  </div>
  <?php } else { ?>
  <div class="row items">
    <div class="col-md-12">
      <?php echo $this->text('Your shopping cart is empty'); ?>
    </div>
  </div>
  <?php } ?>
</div>