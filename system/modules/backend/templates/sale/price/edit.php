<form method="post" id="edit-price-rule" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <label class="col-md-3 control-label">
              <?php echo $this->text('Status'); ?>
            </label>
            <div class="col-md-9">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default<?php echo!empty($price_rule['status']) ? ' active' : ''; ?>">
                  <input name="price_rule[status]" type="radio" autocomplete="off" value="1"<?php echo!empty($price_rule['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
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
          <div class="form-group required<?php echo isset($this->errors['name']) ? ' has-error' : ''; ?>">
            <label class="col-md-3 control-label"><?php echo $this->text('Name'); ?></label>
            <div class="col-md-9">
              <input maxlength="255" name="price_rule[name]" class="form-control" value="<?php echo isset($price_rule['name']) ? $this->escape($price_rule['name']) : ''; ?>">
              <div class="help-block">
                <?php if (isset($this->errors['name'])) { ?>
                <?php echo $this->errors['name']; ?>
                <?php } ?>
                <div class="text-muted">
                  <?php echo $this->text('The name will be shown to administrators and customers during checkout'); ?>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group<?php echo isset($this->errors['code']) ? ' has-error' : ''; ?>">
            <label class="col-md-3 control-label"><?php echo $this->text('Code'); ?></label>
            <div class="col-md-9">
              <input maxlength="255" name="price_rule[code]" class="form-control" value="<?php echo isset($price_rule['code']) ? $this->escape($price_rule['code']) : ''; ?>">
              <div class="help-block">
                <?php if (isset($this->errors['code'])) { ?>
                <?php echo $this->errors['code']; ?>
                <?php } ?>
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
          <div class="form-group">
            <label class="col-md-3 control-label"><?php echo $this->text('Value type'); ?></label>
            <div class="col-md-9">
              <select name="price_rule[value_type]" class="form-control">
                <option value="percent"<?php echo (isset($price_rule['value_type']) && $price_rule['value_type'] == 'percent') ? ' selected' : ''; ?>><?php echo $this->text('Percent'); ?></option>
                <option value="fixed"<?php echo (isset($price_rule['value_type']) && $price_rule['value_type'] == 'fixed') ? ' selected' : ''; ?>><?php echo $this->text('Fixed'); ?></option>
              </select>
              <div class="help-block">
                <?php echo $this->text('Select how to add/subtract the price rule value. I.e for discount 10% use "Percent"'); ?>
              </div>
            </div>
          </div>
          <div class="form-group required<?php echo isset($this->errors['value']) ? ' has-error' : ''; ?>">
            <label class="col-md-3 control-label">
              <?php echo $this->text('Value'); ?>
            </label>
            <div class="col-md-9">
              <input maxlength="32" name="price_rule[value]" class="form-control" value="<?php echo isset($price_rule['value']) ? $this->escape($price_rule['value']) : ''; ?>">
              <div class="help-block">
                <?php if (isset($this->errors['value'])) { ?>
                <?php echo $this->errors['value']; ?>
                <?php } ?>
                <div class="text-muted">
                  <?php echo $this->text('A numeric value to be added to the original price when the rule is applied. To substract use negative numbers'); ?>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-3 control-label"><?php echo $this->text('Currency'); ?></label>
            <div class="col-md-9">
              <select name="price_rule[currency]" class="form-control">
                <?php foreach ($currencies as $code => $currency) { ?>
                <option value="<?php echo $this->escape($code); ?>"<?php echo (isset($price_rule['currency']) && $price_rule['currency'] == $code) ? ' selected' : ''; ?>>
                <?php echo $this->escape($code); ?>
                </option>
                <?php } ?>
              </select>
              <div class="help-block">
                <?php echo $this->text('The price value will be converted according to the selected currency'); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group<?php echo isset($this->errors['weight']) ? ' has-error' : ''; ?>">
            <label class="col-md-3 control-label"><?php echo $this->text('Weight'); ?></label>
            <div class="col-md-9">
              <input maxlength="2" name="price_rule[weight]" class="form-control" value="<?php echo isset($price_rule['weight']) ? $this->escape($price_rule['weight']) : '0'; ?>">
              <div class="help-block">
              <?php if (isset($this->errors['weight'])) { ?>
              <?php echo $this->errors['weight']; ?>
              <?php } ?>
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
          <div class="form-group<?php echo isset($this->errors['data']['conditions']) ? ' has-error' : ''; ?>">
            <label class="col-md-3 control-label"><?php echo $this->text('Conditions'); ?></label>
            <div class="col-md-9">
              <textarea name="price_rule[data][conditions]" rows="4" class="form-control" placeholder="<?php echo $this->text('User is logged in: user_id > 0'); ?>"><?php echo!empty($price_rule['data']['conditions']) ? $this->escape($price_rule['data']['conditions']) : ''; ?></textarea>
              <div class="help-block">
                <?php if (isset($this->errors['data']['conditions'])) { ?>
                <?php echo $this->errors['data']['conditions']; ?>
                <?php } ?>
                <div class="text-muted">
                  <?php echo $this->text('What conditions must be met to apply the price rule. One condition per line. See the legend. Conditions are checked from the top to bottom'); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="row">
            <div class="col-md-3">
              <?php if (isset($price_rule['price_rule_id']) && $this->access('price_rule_delete')) { ?>
              <button class="btn btn-danger delete" name="delete" value="1">
                <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
              </button>
              <?php } ?>
            </div>
            <div class="col-md-9">
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
    </div>
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Operators'); ?></div>
        <div class="panel-body">
          <table class="table table-striped table-condensed">
            <thead>
              <tr>
                <td><?php echo $this->text('Operator'); ?></td>
                <td><?php echo $this->text('Description'); ?></td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>=</td>
                <td><?php echo $this->text('Equal (is in list)'); ?></td>
              </tr>
              <tr>
                <td>!=</td>
                <td><?php echo $this->text('Not equal (is not in list)'); ?></td>
              </tr>
              <tr>
                <td>></td>
                <td><?php echo $this->text('Greater than'); ?></td>
              </tr>
              <tr>
                <td><</td>
                <td><?php echo $this->text('Less than'); ?></td>
              </tr>
              <tr>
                <td>>=</td>
                <td><?php echo $this->text('Greater than or equal to'); ?></td>
              </tr>
              <tr>
                <td><=</td>
                <td><?php echo $this->text('Less than or equal to'); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Conditions'); ?></div>
        <div class="panel-body">
          <table class="table table-striped table-condensed">
            <thead>
              <tr>
                <td><?php echo $this->text('Condition ID'); ?></td>
                <td><?php echo $this->text('Description'); ?></td>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($conditions as $id => $info) { ?>
              <tr>
                <td><?php echo $id; ?></td>
                <td>
                <?php if (!empty($info['description'])) { ?>
                <?php echo $this->escape($info['description']); ?>
                <?php } ?>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</form>