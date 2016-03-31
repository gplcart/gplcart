<?php if (!empty($cart['items'])) {
    ?>
<form method="post" class="form-horizontal" id="checkout">
  <input type="hidden" name="token" value="<?php echo $this->token;
    ?>">
  <div class="row">
    <div class="col-md-12">
      <?php if ($login_form) {
    ?>
      <?php echo $pane_login;
    ?>
      <?php 
} elseif (empty($this->uid)) {
    ?>
      <div class="form-group">
        <div class="col-md-12">
          <button class="btn btn-default btn-block" name="checkout_login" value="1">
            <?php echo $this->text('Already registered? Click to login');
    ?>
          </button>
        </div>
      </div>
      <?php 
}
    ?>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4"><?php echo $pane_shipping_address;
    ?></div>
    <?php if ($shipping_services || $payment_services) {
    ?>
        <div class="col-md-3">
          <div class="form-group">
            <div class="col-md-12">
              <?php if ($shipping_services) {
    ?><?php echo $pane_shipping_services;
    ?><?php 
}
    ?>
              <?php if ($payment_services) {
    ?><?php echo $pane_payment_services;
    ?><?php 
}
    ?>
            </div>
          </div>
        </div>
    <?php 
}
    ?>
    <div class="col-md-5"><?php echo $pane_review;
    ?></div>
  </div>
</form>
<?php 
} else {
    ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('Your shopping cart is empty');
    ?>
  </div>
</div>
<?php 
} ?>