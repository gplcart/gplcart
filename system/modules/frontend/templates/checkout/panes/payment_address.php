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
<div class="panel panel-checkout payment-address panel-default">
  <div class="panel-heading clearfix">
    <input type="hidden" name="same_payment_address" value="0">
    <input type="checkbox" name="same_payment_address" value="1"<?php echo $same_payment_address ? ' checked' : ''; ?>> <?php echo $this->text('My shipping and payment addresses are the same'); ?>
    <noscript>
      <button title="<?php echo $this->text('Update'); ?>" class="btn btn-default btn-xs pull-right" name="update" value="1"><i class="fa fa-refresh"></i></button>
    </noscript>
  </div>
  <?php if (!$same_payment_address) { ?>
  <div class="panel-body">
    <?php if ($this->error('payment_address', true)) { ?>
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
      </button>
      <?php echo $this->error('payment_address'); ?>
    </div>
    <?php } ?>
    <?php if (!empty($addresses) && !$show_payment_address_form) { ?>
    <div class="form-group">
      <div class="col-md-12">
        <div class="btn-group saved-addresses">
          <?php foreach ($addresses as $address_id => $address) { ?>
          <div class="radio address">
            <label class="address<?php echo isset($order['payment_address']) && $order['payment_address'] == $address_id ? ' active' : ''; ?>">
              <input type="radio" name="order[payment_address]" value="<?php echo $this->e($address_id); ?>" autocomplete="off"<?php echo isset($order['payment_address']) && $order['payment_address'] == $address_id ? ' checked' : ''; ?>>
              <?php foreach ($address as $name => $value) { ?>
              <span class="clearfix"><?php echo $this->e($name); ?> : <?php echo $this->e($value); ?></span>
              <?php } ?>
            </label>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>
    <?php if ($can_add_address) { ?>
    <div class="form-group">
      <div class="col-md-12">
        <button class="btn btn-default" name="add_address" value="payment"><?php echo $this->text('Add address'); ?></button>
      </div>
    </div>
    <?php } ?>
    <?php } else if ($can_add_address) { ?>
    <div class="form-group">
      <div class="col-md-12">
        <table class="table table-borderless table-condensed table-striped payment-address">
          <?php if (!empty($countries)) { ?>
          <tr>
            <td class="middle"><?php echo $this->text('Country'); ?></td>
            <td>
              <div class="btn-toolbar">
                <div class="btn-group country<?php echo $this->error('address.payment.country', ' has-error'); ?>">
                  <?php if(count($countries) > 1) { ?>
                  <select class="form-control" name="order[address][payment][country]">
                    <option value="" disabled selected><?php echo $this->text('- select -'); ?></option>
                    <?php foreach ($countries as $code => $name) { ?>
                    <option value="<?php echo $this->e($code); ?>"<?php echo $address['payment']['country'] == $code ? ' selected' : ''; ?>><?php echo $this->e($name); ?></option>
                    <?php } ?>
                  </select>
                  <?php } else { ?>
                  <?php echo $this->e(reset($countries)); ?>
                  <input type="hidden" name="order[address][payment][country]" value="<?php echo $this->e(key($countries)); ?>">
                  <?php } ?>
                </div>
                <noscript>
                <div class="btn-group">
                  <button class="btn btn-default" name="get_states" value="payment"><?php echo $this->text('Get states'); ?></button>
                </div>
                </noscript>
              </div>
            </td>
          </tr>
          <?php } ?>
          <?php if (empty($countries) || !empty($address['payment']['country'])) { ?>
          <?php foreach ($format['payment'] as $key => $data) { ?>
          <?php if ($key !== 'country') { ?>
          <tr>
            <td class="middle">
              <span class="<?php echo empty($data['required']) ? '' : 'required'; ?>"><?php echo $this->e($data['name']); ?></span>
            </td>
            <td class="middle">
              <div class="<?php echo $this->e($key); ?><?php echo $this->error("address.payment.$key", ' has-error'); ?>">
                <?php if ($key === 'state_id') { ?>
                <select class="form-control" name="order[address][payment][state_id]">
                  <option value="" disabled selected><?php echo $this->text('- select -'); ?></option>
                  <?php foreach ($states['payment'] as $state_id => $state) { ?>
                  <option value="<?php echo $this->e($state_id); ?>"<?php echo isset($address['payment']['state_id']) && $address['payment']['state_id'] == $state_id ? ' selected' : ''; ?>>
                    <?php echo $this->e($state['name']); ?>
                  </option>
                  <?php } ?>
                </select>
                <?php } else { ?>
                <input name="order[address][payment][<?php echo $this->e($key); ?>]" maxlength="255" data-ajax="false" class="form-control" value="<?php echo isset($address['payment'][$key]) ? $this->e($address['payment'][$key]) : ''; ?>">
                <?php } ?>
              </div>
            </td>
          </tr>
          <?php } ?>
          <?php } ?>
          <?php } ?>
        </table>
      </div>
    </div>
    <div class="btn-toolbar">
      <?php if (!empty($addresses)) { ?>
      <button class="btn btn-default" name="cancel_address_form" value="payment">
        <?php echo $this->text('Cancel'); ?>
      </button>
      <?php } ?>
      <?php if ($can_save_address && (empty($countries) || !empty($address['payment']['country']))) { ?>
      <button class="btn btn-default" name="save_address" value="payment">
        <?php echo $this->text('Save'); ?>
      </button>
      <?php } ?>
    </div>
    <?php } ?>
  </div>
  <?php } ?>
</div>