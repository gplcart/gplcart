<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\frontend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if (empty($cart['items'])) { ?>
<div class="row">
  <div class="col-md-12">
    <?php if ($this->access('order_add')) { ?>
    <?php echo $this->text('If you want to add a new order for a user, add all needed products to your cart then add an order for the <a href="@url">user</a>. Your cart items will be assigned to that user.', array('@url' => $this->url('admin/user/list'))); ?>
    <?php } else { ?>
    <?php echo $this->text('Shopping cart is empty. <a href="@url">Shop now</a>', array('@url' => $this->url('catalog'))); ?>
    <?php } ?>
  </div>
</div>
<?php } else { ?>
<form method="post" class="form-horizontal" id="checkout">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <noscript>
  <div class="alert alert-warning">
    <?php echo $this->text('Your browser seems to have Java-Script disabled. You should press "Update" button each time you make changes in the form', array('@title' => $this->text('Update'))); ?>
  </div>
  </noscript>
  <div class="row">
    <div class="col-md-12">
      <?php if ($show_login_form) { ?>
      <?php echo $pane_login; ?>
      <?php } else if (!$_is_logged_in) { ?>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Already registered?'); ?></label>
        <div class="col-md-4">
          <button class="btn btn-default form-control" name="checkout_login" value="1">
            <?php echo $this->text('Click to login'); ?>
          </button>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
  <?php if (!$show_login_form) { ?>
  <div class="row">
    <div class="col-md-4">
      <?php if (!empty($pane_shipping_address)) { ?>
      <?php echo $pane_shipping_address; ?>
      <?php } ?>
      <?php if (!empty($pane_payment_address)) { ?>
      <?php echo $pane_payment_address; ?>
      <?php } ?>
    </div>
    <div class="col-md-3">
      <div class="form-group">
        <div class="col-md-12">
          <?php if(!empty($pane_shipping_methods)) { ?>
          <?php echo $pane_shipping_methods; ?>
          <?php } ?>
          <?php if(!empty($pane_payment_methods)) { ?>
          <?php echo $pane_payment_methods; ?>
          <?php } ?>
        </div>
      </div>
    </div>
    <div class="col-md-5">
      <?php if (!empty($pane_review)) { ?>
      <?php echo $pane_review; ?>
      <?php } ?>
      <?php if (!empty($pane_comment)) { ?>
      <?php echo $pane_comment; ?>
      <?php } ?>
      <?php if (!empty($pane_action)) { ?>
      <?php echo $pane_action; ?>
      <?php } ?>
    </div>
  </div>
  <?php } ?>
</form>
<?php } ?>