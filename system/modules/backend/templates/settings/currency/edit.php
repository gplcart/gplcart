<form method="post" id="edit-currency" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6 col-md-offset-6 text-right">
      <div class="btn-toolbar">
        <?php if (isset($currency['code']) && $this->access('currency_delete') && ($default_currency != $currency['code'])) { ?>
        <button class="btn btn-danger delete-currency" name="delete" value="1">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/settings/currency'); ?>" class="btn btn-default"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('currency_edit') || $this->access('currency_add')) { ?>
        <button class="btn btn-primary save" name="save" value="1"><i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?></button>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="row margin-top-20">
    <div class="col-md-12">
      <div class="form-group">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Default currency is the base currency of the store'); ?>">
          <?php echo $this->text('Default'); ?>
          </span>
        </label>
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
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Only enabled currencies are visible for users'); ?>">
            <?php echo $this->text('Status'); ?>
          </span>
        </label>
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
        </div>
      </div>
      <div class="form-group<?php echo isset($form_errors['convertion_rate']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('An exchange rate against default (base) currency. Only numeric values'); ?>">
            <?php echo $this->text('Convertion rate'); ?>
          </span>
        </label>
        <div class="col-md-1">
          <input type="number" step="any" min="0" name="currency[convertion_rate]" class="form-control" value="<?php echo isset($currency['convertion_rate']) ? $this->escape($currency['convertion_rate']) : 1; ?>">
          <?php if (isset($form_errors['convertion_rate'])) { ?>
            <div class="help-block"><?php echo $form_errors['convertion_rate']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="required form-group<?php echo isset($form_errors['name']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Official currency name for customers'); ?>">
            <?php echo $this->text('Name'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input maxlength="32" name="currency[name]" class="form-control" value="<?php echo (isset($currency['name'])) ? $this->escape($currency['name']) : ''; ?>">
          <?php if (isset($form_errors['name'])) { ?>
            <div class="help-block"><?php echo $form_errors['name']; ?></div>
          <?php } ?>
        </div>
      </div>
      <?php if (empty($currency['code'])) { ?>
      <div class="required form-group<?php echo isset($form_errors['code']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('3-letter ISO 4217 code , i.e USD for US dollar'); ?>">
            <?php echo $this->text('Code'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input maxlength="3" pattern="[a-zA-Z]{3}" name="currency[code]" class="form-control" value="<?php echo (isset($currency['code'])) ? $this->escape($currency['code']) : ''; ?>">
          <?php if (isset($form_errors['code'])) { ?>
              <div class="help-block"><?php echo $form_errors['code']; ?></div>
          <?php } ?>
        </div>
      </div>
      <?php } else { ?>
        <input type="hidden" name="currency[code]" value="<?php echo $currency['code']; ?>">
      <?php } ?>
      <div class="required form-group<?php echo isset($form_errors['numeric_code']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('ISO 4217 numeric code'); ?>">
            <?php echo $this->text('Numeric code'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input maxlength="3" type="number" pattern="[0-9]{3}" name="currency[numeric_code]" class="form-control" value="<?php echo (isset($currency['numeric_code'])) ? $this->escape($currency['numeric_code']) : ''; ?>">
          <?php if (isset($form_errors['numeric_code'])) { ?>
            <div class="help-block"><?php echo $form_errors['numeric_code']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="required form-group<?php echo isset($form_errors['symbol']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Currency sign, i.e $ for US dollar'); ?>">
            <?php echo $this->text('Symbol'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input maxlength="32" name="currency[symbol]" class="form-control" value="<?php echo (isset($currency['symbol'])) ? $this->escape($currency['symbol']) : ''; ?>">
          <?php if (isset($form_errors['symbol'])) { ?>
          <div class="help-block"><?php echo $form_errors['symbol']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="required form-group<?php echo isset($form_errors['major_unit']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Highest valued currency unit, i.e Dollar for US dollar'); ?>">
          <?php echo $this->text('Major unit'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input maxlength="32" name="currency[major_unit]" class="form-control" value="<?php echo (isset($currency['major_unit'])) ? $this->escape($currency['major_unit']) : ''; ?>">
          <?php if (isset($form_errors['major_unit'])) { ?>
          <div class="help-block"><?php echo $form_errors['major_unit']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="required form-group<?php echo isset($form_errors['minor_unit']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Usually has a value that is 1/100 of the major unit, i.e cents'); ?>">
          <?php echo $this->text('Minor unit'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input maxlength="32" name="currency[minor_unit]" class="form-control" value="<?php echo (isset($currency['minor_unit'])) ? $this->escape($currency['minor_unit']) : ''; ?>">
          <?php if (isset($form_errors['minor_unit'])) { ?>
          <div class="help-block"><?php echo $form_errors['minor_unit']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo isset($form_errors['symbol_placement']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Position of currency sign, either before or after value'); ?>">
          <?php echo $this->text('Symbol placement'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <select class="form-control" name="currency[symbol_placement]">
            <option value=""><?php echo $this->text('None'); ?></option>
            <option value="before"<?php echo (isset($currency['symbol_placement']) && $currency['symbol_placement'] == 'before') ? ' selected' : ''; ?>><?php echo $this->text('Before'); ?></option>
            <option value="after"<?php echo (isset($currency['symbol_placement']) && $currency['symbol_placement'] == 'after') ? ' selected' : ''; ?>><?php echo $this->text('After'); ?></option>
          </select>
          <?php if (isset($form_errors['symbol_placement'])) { ?>
          <div class="help-block"><?php echo $form_errors['symbol_placement']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo isset($form_errors['code_placement']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Position of currency code, either after or before value'); ?>">
          <?php echo $this->text('Code placement'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <select class="form-control" name="currency[code_placement]">
            <option value=""><?php echo $this->text('None'); ?></option>
            <option value="before"<?php echo (isset($currency['code_placement']) && $currency['code_placement'] == 'before') ? ' selected' : ''; ?>><?php echo $this->text('Before'); ?></option>
            <option value="after"<?php echo (isset($currency['code_placement']) && $currency['code_placement'] == 'after') ? ' selected' : ''; ?>><?php echo $this->text('After'); ?></option>
          </select>
          <?php if (isset($form_errors['code_placement'])) { ?>
          <div class="help-block"><?php echo $form_errors['code_placement']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-2 text-right">
          <a data-toggle="collapse" href="#more"><?php echo $this->text('More'); ?> <span class="caret"></span></a>
        </div>
      </div>
      <div class="collapse more<?php echo empty($form_errors) ? '' : ' in'; ?>" id="more">
        <div class="form-group<?php echo isset($form_errors['decimals']) ? ' has-error' : ''; ?>">
          <label class="col-md-2 control-label">
            <span class="hint" title="<?php echo $this->text('Number of decimal points'); ?>">
            <?php echo $this->text('Decimals'); ?>
            </span>
          </label>
          <div class="col-md-1">
            <input maxlength="1" pattern="[0-9]{1}" type="number" min="0" step="1" name="currency[decimals]" class="form-control" value="<?php echo (isset($currency['decimals'])) ? $this->escape($currency['decimals']) : 2; ?>">
            <?php if (isset($form_errors['decimals'])) { ?>
            <div class="help-block"><?php echo $form_errors['decimals']; ?></div>
            <?php } ?>
          </div>
        </div>
        <div class="form-group<?php echo isset($form_errors['rounding_step']) ? ' has-error' : ''; ?>">
          <label class="col-md-2 control-label">
            <span class="hint" title="<?php echo $this->text('Rounding a specific step for more granular control of final value'); ?>">
            <?php echo $this->text('Rounding step'); ?>
            </span>
          </label>
          <div class="col-md-1">
            <input maxlength="1" pattern="[0-9]{1}" type="number" min="0" step="1" name="currency[rounding_step]" class="form-control" value="<?php echo (isset($currency['rounding_step'])) ? $this->escape($currency['rounding_step']) : 0; ?>">
            <?php if (isset($form_errors['rounding_step'])) { ?>
            <div class="help-block"><?php echo $form_errors['rounding_step']; ?></div>
            <?php } ?>
          </div>
        </div>
        <div class="form-group">
          <label class="col-md-2 control-label">
            <span class="hint" title="<?php echo $this->text('Character used to separate thousands, e.g comma'); ?>">
            <?php echo $this->text('Thousands separator'); ?>
            </span>
          </label>
          <div class="col-md-1">
            <input maxlength="1" name="currency[thousands_separator]" class="form-control" value="<?php echo (isset($currency['thousands_separator'])) ? $this->escape($currency['thousands_separator']) : ','; ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="col-md-2 control-label">
            <span class="hint" title="<?php echo $this->text('Character used to separate decimals, e.g period'); ?>">
            <?php echo $this->text('Decimal separator'); ?>
            </span>
          </label>
          <div class="col-md-1">
            <input maxlength="1" name="currency[decimal_separator]" class="form-control" value="<?php echo (isset($currency['decimal_separator'])) ? $this->escape($currency['decimal_separator']) : '.'; ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="col-md-2 control-label">
            <span class="hint" title="<?php echo $this->text('Character between price value and currency sign'); ?>">
            <?php echo $this->text('Symbol spacer'); ?>
            </span>
          </label>
          <div class="col-md-1">
            <input maxlength="2" name="currency[symbol_spacer]" class="form-control" value="<?php echo (isset($currency['symbol_spacer'])) ? $this->escape($currency['symbol_spacer']) : ' '; ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="col-md-2 control-label">
            <span class="hint" title="<?php echo $this->text('Character between currency code and price value'); ?>">
            <?php echo $this->text('Code spacer'); ?>
            </span>
          </label>
          <div class="col-md-1">
            <input maxlength="2" name="currency[code_spacer]" class="form-control" value="<?php echo (isset($currency['code_spacer'])) ? $this->escape($currency['code_spacer']) : ' '; ?>">
          </div>
        </div>
      </div>
    </div>
  </div>
</form>