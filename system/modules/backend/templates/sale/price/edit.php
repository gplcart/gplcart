<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" id="edit-price-rule" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Status'); ?>
        </label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($price_rule['status']) ? '' : ' active'; ?>">
              <input name="price_rule[status]" type="radio" autocomplete="off" value="1"<?php echo empty($price_rule['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($price_rule['status']) ? ' active' : ''; ?>">
              <input name="price_rule[status]" type="radio" autocomplete="off" value="0"<?php echo empty($price_rule['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
            <?php echo $this->text('Disabled rules will not affect store prices'); ?>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('trigger_id', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Trigger'); ?></label>
        <div class="col-md-4">
          <select name="price_rule[trigger_id]" class="form-control">
            <option value="0"><?php echo $this->text('None'); ?></option>
            <?php foreach ($triggers as $trigger_id => $trigger) { ?>
            <option value="<?php echo $trigger_id; ?>"<?php echo isset($price_rule['trigger_id']) && $price_rule['trigger_id'] == $trigger_id ? ' selected' : ''; ?>>
            <?php echo $this->e($trigger['name']); ?>
            </option>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->error('trigger_id'); ?>
            <div class="text-muted"><?php echo $this->text('Select an enabled <a href="!href">trigger</a> that applies this rule. Keep in mind that triggers are per store', array('!href' => $this->url('admin/settings/trigger'))); ?></div>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('name', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-4">
          <input maxlength="255" name="price_rule[name]" class="form-control" value="<?php echo isset($price_rule['name']) ? $this->e($price_rule['name']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('name'); ?>
            <div class="text-muted">
              <?php echo $this->text('The name will be shown to administrators and customers during checkout'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('code', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Code'); ?></label>
        <div class="col-md-4">
          <input maxlength="255" name="price_rule[code]" class="form-control" value="<?php echo isset($price_rule['code']) ? $this->e($price_rule['code']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('code'); ?>
            <div class="text-muted">
              <?php echo $this->text('A unique code you want to associate with this price rule. The code (i.e coupon) must be specified by a customer during checkout'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group required<?php echo $this->error('value', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Value'); ?>
        </label>
        <div class="col-md-4">
          <input maxlength="32" name="price_rule[value]" class="form-control" value="<?php echo isset($price_rule['value']) ? $this->e($price_rule['value']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('value'); ?>
            <div class="text-muted">
              <?php echo $this->text('A numeric value to be added to the original price when the rule is applied. To substract use negative numbers'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Value type'); ?></label>
        <div class="col-md-4">
          <select name="price_rule[value_type]" class="form-control">
            <option value="percent"<?php echo isset($price_rule['value_type']) && $price_rule['value_type'] == 'percent' ? ' selected' : ''; ?>><?php echo $this->text('Percent'); ?></option>
            <option value="fixed"<?php echo isset($price_rule['value_type']) && $price_rule['value_type'] == 'fixed' ? ' selected' : ''; ?>><?php echo $this->text('Fixed'); ?></option>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select how to add/subtract the price rule value. I.e for discount 10% use "Percent"'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Currency'); ?></label>
        <div class="col-md-4">
          <select name="price_rule[currency]" class="form-control">
            <?php foreach ($currencies as $code => $currency) { ?>
            <option value="<?php echo $this->e($code); ?>"<?php echo isset($price_rule['currency']) && $price_rule['currency'] == $code ? ' selected' : ''; ?>>
            <?php echo $this->e($code); ?>
            </option>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->text('The price value will be converted according to the selected currency'); ?>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('weight', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Weight'); ?></label>
        <div class="col-md-4">
          <input maxlength="2" name="price_rule[weight]" class="form-control" value="<?php echo isset($price_rule['weight']) ? $this->e($price_rule['weight']) : '0'; ?>">
          <div class="help-block">
            <?php echo $this->error('weight'); ?>
            <div class="text-muted">
              <?php echo $this->text('A position of the rule among other enabled rules. Rules with lower weight are applied earlier'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <?php if (isset($price_rule['price_rule_id']) && $this->access('price_rule_delete')) { ?>
          <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Delete? It cannot be undone!'));">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-10">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url('admin/sale/price'); ?>" class="btn btn-default cancel">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('price_rule_edit') || $this->access('price_rule_add')) { ?>
            <button class="btn btn-default save" name="save" value="1">
              <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
            </button>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>