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
<div class="form-group">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-body">
        <?php if ($this->error('login', true)) { ?>
        <div class="alert alert-danger alert-dismissible clearfix">
          <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
          </button>
          <?php echo $this->error('login'); ?>
        </div>
        <?php } ?>
        <div class="form-inline clearfix">
          <div class="form-group col-md-4<?php echo $this->error("order.user.email", ' has-error'); ?>">
            <label class="col-md-3 control-label"><?php echo $this->text('E-mail'); ?></label>
            <div class="col-md-9">
              <input maxlength="255" class="form-control" name="order[user][email]" value="<?php echo isset($order['user']['email']) ? $order['user']['email'] : ''; ?>" autofocus>
              <div class="help-block"><?php echo $this->error("order.user.email"); ?></div>
            </div>
          </div>
          <div class="form-group col-md-4">
            <label class="col-md-3 control-label"><?php echo $this->text('Password'); ?></label>
            <div class="col-md-9">
              <input type="order[user][password]" maxlength="32" class="form-control" name="order[user][password]" value="">
            </div>
          </div>
          <div class="form-group col-md-4">
            <div class="btn-toolbar">
              <button class="btn btn-default" name="login" value="1">
                <?php echo $this->text('Log in'); ?>
              </button>
              <button class="btn btn-default" name="checkout_anonymous" value="1">
                <?php echo $this->text('Continue as guest'); ?>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>