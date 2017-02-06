<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form class="form-horizontal hidden-print" method="post">
  <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Actions'); ?></div>
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
        <div class="col-md-7">
          <select class="form-control" name="order[status]">
            <?php foreach ($statuses as $code => $name) { ?>
            <option value="<?php echo $code; ?>"<?php echo $order['status'] == $code ? ' selected' : ''; ?>>
              <?php echo $this->escape($name); ?>
            </option>
            <?php } ?>
          </select>
        </div>
        <div class="col-md-3">
          <a href="#log-message" data-toggle="collapse"><?php echo $this->text('Log message'); ?> <span class="caret"></span></a>
        </div>
      </div>
      <div id="log-message" class="form-group<?php echo $this->error('log', ' has-error', ' collapse'); ?>">
        <div class="col-md-10 col-md-offset-2">
        <textarea name="order[log]" class="form-control"><?php echo isset($order['log']) ? $this->escape($order['log']) : ''; ?></textarea>
        <div class="help-block">
          <?php echo $this->error('log'); ?>
          <div class="text-muted"><?php echo $this->text('A short explanation of the update you are making for other administrators'); ?></div>
        </div>
        </div>
      </div>
      <?php if ($this->access('order_edit')) { ?>
      <div class="form-group">
        <div class="col-md-10 col-md-offset-2">
          <?php if ($this->access('order_add')) { ?>
          <button class="btn btn-default" name="clone" value="1" onclick="return confirm(GplCart.text('Are you sure? A new order will be created, this order will be canceled. Customer will be notified if you selected «Notify user»'));"><?php echo $this->text('Clone and cancel'); ?></button>
          <?php } ?>
          <button class="btn btn-default" name="status" value="1" onclick="return confirm(GplCart.text('Do you want to change order status? Customer will be notified if you selected «Notify user»'));"><?php echo $this->text('Update status'); ?></button>
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default">
              <input type="checkbox" name="order[notify]" value="1" autocomplete="off"> <?php echo $this->text('Notify user'); ?>
            </label>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
</form>