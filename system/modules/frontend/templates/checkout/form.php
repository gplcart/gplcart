<?php if (!empty($cart['items'])) { ?>
<form method="post" class="form-horizontal" id="checkout" data-settings='<?php echo $settings; ?>'>
  <input type="hidden" name="token" value="<?php echo $this->token; ?>">
  <div class="row">
    <div class="col-md-12">
      <?php if ($login_form) { ?>
      <?php echo $pane_login; ?>
      <?php } else if (empty($this->uid)) { ?>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <label class="col-md-2 control-label"><?php echo $this->text('Already registered?'); ?></label>
            <div class="col-md-3">
              <button class="btn btn-default form-control" name="checkout_login" value="1">
                <?php echo $this->text('Click to login'); ?>
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4"><?php echo $pane_shipping_address; ?></div>
    <?php if ($shipping_methods || $payment_methods) { ?>
        <div class="col-md-3">
          <div class="form-group">
            <div class="col-md-12">
              <?php if ($shipping_methods) { ?><?php echo $pane_shipping_methods; ?><?php } ?>
              <?php if ($payment_methods) { ?><?php echo $pane_payment_methods; ?><?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    <div class="col-md-5"><?php echo $pane_review; ?></div>
  </div>
</form>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('Your shopping cart is empty'); ?>
  </div>
</div>
<?php } ?>