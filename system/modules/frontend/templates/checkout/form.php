<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * 
 * To see available variables: <?php print_r(get_defined_vars()); ?>
 * To see the current controller object: <?php print_r($this); ?>
 * To call a controller method: <?php $this->exampleMethod(); ?>
 */
?>
<?php if (empty($cart['items'])) { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('Shopping cart is empty.'); ?>
    <?php if($this->access('order_add')) { ?>
    <p><?php echo $this->text('If you want to add a new order for a customer, add all needed products to your cart then add an order for a <a href="@href">user</a>. Your cart items will be assigned to that user.', array('@href' => $this->url('admin/user/list'))); ?></p>
    <?php } ?>
  </div>
</div>
<?php } else { ?>
<form method="post" class="form-horizontal" id="checkout" data-settings='<?php echo $settings; ?>'>
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="row">
    <div class="col-md-12">
      <?php echo $pane_admin; ?>
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
<?php } ?>