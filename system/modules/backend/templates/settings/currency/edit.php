<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" id="edit-currency" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('convertion_rate', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Convertion rate'); ?></label>
        <div class="col-md-4">
          <input name="currency[convertion_rate]" class="form-control" value="<?php echo isset($currency['convertion_rate']) ? $this->escape($currency['convertion_rate']) : 1; ?>">
          <div class="help-block">
            <?php echo $this->error('convertion_rate'); ?>
            <div class="text-muted">
              <?php echo $this->text('An exchange rate against default (base) currency. Only numeric positive values'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="required form-group<?php echo $this->error('name', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Name'); ?>
        </label>
        <div class="col-md-4">
          <input name="currency[name]" class="form-control" value="<?php echo (isset($currency['name'])) ? $this->escape($currency['name']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('name'); ?>
            <div class="text-muted">
              <?php echo $this->text('Required. An official currency name in english'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="required form-group<?php echo $this->error('code', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Code'); ?></label>
        <div class="col-md-4">
          <input name="currency[code]" class="form-control" value="<?php echo (isset($currency['code'])) ? $this->escape($currency['code']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('code'); ?>
            <div class="text-muted">
              <?php echo $this->text('Required. A three-letter upper-case code according to ISO 4217 standard, e.g USD'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="required form-group<?php echo $this->error('symbol', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Symbol'); ?></label>
        <div class="col-md-4">
          <input name="currency[symbol]" class="form-control" value="<?php echo (isset($currency['symbol'])) ? $this->escape($currency['symbol']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('symbol'); ?>
            <div class="text-muted">
              <?php echo $this->text('Required. A sign of the currency, e.g $'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="required form-group<?php echo $this->error('numeric_code', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Numeric code'); ?></label>
        <div class="col-md-4">
          <input name="currency[numeric_code]" class="form-control" value="<?php echo (isset($currency['numeric_code'])) ? $this->escape($currency['numeric_code']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('numeric_code'); ?>
            <div class="text-muted"><?php echo $this->text('Required. A numeric code according to ISO 4217 standard'); ?></div>
          </div>
        </div>
      </div>
      <div class="required form-group<?php echo $this->error('major_unit', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Major unit'); ?></label>
        <div class="col-md-4">
          <input name="currency[major_unit]" class="form-control" value="<?php echo (isset($currency['major_unit'])) ? $this->escape($currency['major_unit']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('major_unit'); ?>
            <div class="text-muted">
              <?php echo $this->text('Required. A name of the highest valued currency unit, e.g Dollar'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="required form-group<?php echo $this->error('minor_unit', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Minor unit'); ?></label>
        <div class="col-md-4">
          <input name="currency[minor_unit]" class="form-control" value="<?php echo (isset($currency['minor_unit'])) ? $this->escape($currency['minor_unit']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('minor_unit'); ?>
            <div class="text-muted">
              <?php echo $this->text('Required. A name of the lowest valued currency unit. Usually it\'s 1/100 of the major unit, e.g Cent'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Default'); ?></label>
        <div class="col-md-6">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo(isset($currency['code']) && $default_currency == $currency['code']) ? ' active' : ''; ?>">
              <input name="currency[default]" type="radio" autocomplete="off" value="1"<?php echo(isset($currency['code']) && $default_currency == $currency['code']) ? ' checked' : ''; ?>>
              <?php echo $this->text('Yes'); ?>
            </label>
            <label class="btn btn-default<?php echo(isset($currency['code']) && $default_currency == $currency['code']) ? '' : ' active'; ?>">
              <input name="currency[default]" type="radio" autocomplete="off" value="0"<?php echo(isset($currency['code']) && $default_currency == $currency['code']) ? '' : ' checked'; ?>>
              <?php echo $this->text('No'); ?>
            </label>
          </div>
          <div class="help-block">
            <?php echo $this->text('Default currency is the base currency of the store'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
        <div class="col-md-6">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($currency['status']) ? '' : ' active'; ?>">
              <input name="currency[status]" type="radio" autocomplete="off" value="1"<?php echo empty($currency['status']) ? '' : ' checked'; ?>>
              <?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($currency['status']) ? ' active' : ''; ?>">
              <input name="currency[status]" type="radio" autocomplete="off" value="0"<?php echo empty($currency['status']) ? ' checked' : ''; ?>>
              <?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
            <?php echo $this->text('Only enabled currencies are visible to frontend users'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('symbol_placement', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Symbol placement'); ?></label>
        <div class="col-md-4">
          <select class="form-control" name="currency[symbol_placement]">
            <option value="before"<?php echo (isset($currency['symbol_placement']) && $currency['symbol_placement'] == 'before') ? ' selected' : ''; ?>><?php echo $this->text('Before'); ?></option>
            <option value="after"<?php echo (isset($currency['symbol_placement']) && $currency['symbol_placement'] == 'after') ? ' selected' : ''; ?>><?php echo $this->text('After'); ?></option>
          </select>
          <div class="help-block">
            <?php echo $this->error('symbol_placement'); ?>
            <div class="text-muted">
            <?php echo $this->text('A position of the currency sign, either before or after price value'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('code_placement', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Code placement'); ?></label>
        <div class="col-md-4">
          <select class="form-control" name="currency[code_placement]">
            <option value="after"<?php echo (isset($currency['code_placement']) && $currency['code_placement'] == 'after') ? ' selected' : ''; ?>><?php echo $this->text('After'); ?></option>
            <option value="before"<?php echo (isset($currency['code_placement']) && $currency['code_placement'] == 'before') ? ' selected' : ''; ?>><?php echo $this->text('Before'); ?></option>
          </select>
          <div class="help-block">
            <?php echo $this->error('code_placement'); ?>
            <div class="text-muted">
              <?php echo $this->text('A position of the currency code, either before or after price value'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('decimals', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Decimals'); ?></label>
        <div class="col-md-4">
          <input name="currency[decimals]" class="form-control" value="<?php echo (isset($currency['decimals'])) ? $this->escape($currency['decimals']) : 2; ?>">
          <div class="help-block">
            <?php echo $this->error('decimals'); ?>
            <div class="text-muted"><?php echo $this->text('A number of decimal points, usually 2'); ?></div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('rounding_step', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Rounding step'); ?></label>
        <div class="col-md-4">
          <input name="currency[rounding_step]" class="form-control" value="<?php echo (isset($currency['rounding_step'])) ? $this->escape($currency['rounding_step']) : 0; ?>">
          <div class="help-block">
            <?php echo $this->error('rounding_step'); ?>
            <div class="text-muted">
              <?php echo $this->text('A numeric value for more granular control over rounding to the final value. Enter 0 if unsure'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Thousands separator'); ?></label>
        <div class="col-md-4">
          <input name="currency[thousands_separator]" class="form-control" value="<?php echo (isset($currency['thousands_separator'])) ? $this->escape($currency['thousands_separator']) : ','; ?>">
          <div class="help-block">
            <?php echo $this->text('A character used to separate thousands, e.g comma'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Decimal separator'); ?></label>
        <div class="col-md-4">
          <input name="currency[decimal_separator]" class="form-control" value="<?php echo (isset($currency['decimal_separator'])) ? $this->escape($currency['decimal_separator']) : '.'; ?>">
          <div class="help-block">
            <?php echo $this->text('A character used to separate decimals, e.g period'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Symbol spacer'); ?></label>
        <div class="col-md-4">
          <input name="currency[symbol_spacer]" class="form-control" value="<?php echo (isset($currency['symbol_spacer'])) ? $this->escape($currency['symbol_spacer']) : ' '; ?>">
          <div class="help-block">
            <?php echo $this->text('A character used to separate currency sign and price value, e.g whitespace'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Code spacer'); ?></label>
        <div class="col-md-4">
          <input name="currency[code_spacer]" class="form-control" value="<?php echo (isset($currency['code_spacer'])) ? $this->escape($currency['code_spacer']) : ' '; ?>">
          <div class="help-block">
            <?php echo $this->text('A character used to separate currency code and price value, e.g whitespace'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <?php if ($can_delete) { ?>
          <button class="btn btn-danger delete-currency" name="delete" value="1" onclick="return confirm('Delete? It cannot be undone!');">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-4">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url('admin/settings/currency'); ?>" class="btn btn-default"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
            <?php if ($this->access('currency_edit') || $this->access('currency_add')) { ?>
            <button class="btn btn-default save" name="save" value="1"><i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?></button>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>