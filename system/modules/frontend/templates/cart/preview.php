<div id="cart-preview">
  <?php if (!empty($cart['items'])) {
    ?>
  <div class="row items">
    <div class="col-md-12">
      <?php foreach ($cart['items'] as $item) {
    ?>
      <div class="media cart-item">
        <div class="media-left image">
          <img class="media-object thumbnail" alt="<?php echo $this->escape($item['product']['title']);
    ?>" src="<?php echo $this->escape($item['thumb']);
    ?>">
        </div>
        <div class="media-body info">
          <div class="media-heading"><?php echo $this->escape($item['product']['title']);
    ?></div>
          <p><?php echo $this->escape($item['sku']);
    ?></p>
          <p><?php echo $this->escape($item['quantity']);
    ?> X <?php echo $this->escape($item['price_formatted']);
    ?> = <?php echo $this->escape($item['total_formatted']);
    ?></p>
        </div>
      </div>
      <?php 
}
    ?>
    </div>
  </div>
  <?php if (count($cart['items']) > $limit) {
    ?>
  <div class="row more">
    <div class="col-md-12">
    <a href="<?php echo $this->url('cart');
    ?>"><?php echo $this->text('Showing %num from %total', array('%num' => count($cart['items']), '%total' => $limit));
    ?></a>
    </div>
  </div>
  <?php 
}
    ?>
  <div class="row subtotal">
    <div class="col-md-12">
    <h4><?php echo $this->text('Subtotal');
    ?> : <span class="price"><?php echo $cart['total_formatted'];
    ?></span></h4>
    </div>
  </div>
  <hr>
  <div class="row buttons">
    <div class="col-md-12 checkout">
    <a href="<?php echo $this->url('checkout');
    ?>" class="btn btn-block btn-success"><?php echo $this->text('checkout');
    ?></a>
    </div>
  </div>
  <?php 
} else {
    ?>
  <div class="row items">
    <div class="col-md-12">
       <?php echo $this->text('Your shopping cart is empty');
    ?>
    </div>
  </div>
  <?php 
} ?>
</div>