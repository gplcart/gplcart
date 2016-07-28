<form method="post" id="edit-country" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6 col-md-offset-6 text-right">
      <div class="btn-toolbar">
        <?php if (isset($country['code']) && $this->access('country_delete') && empty($country['default'])) { ?>
        <button class="btn btn-danger delete" name="delete" value="1">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/settings/country'); ?>" class="btn btn-default"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('country_edit') || $this->access('country_add')) { ?>
        <button class="btn btn-primary save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="row margin-top-20">
    <div class="col-md-12">
      <div class="form-group">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Primary store country that cannot be deleted or disabled'); ?>">
            <?php echo $this->text('Default'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($country['default']) ? '' : ' active'; ?>">
              <input name="country[default]" type="radio" autocomplete="off" value="1"<?php echo empty($country['default']) ? '' : ' checked'; ?>>
              <?php echo $this->text('Yes'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($country['default']) ? ' active' : ''; ?>">
              <input name="country[default]" type="radio" autocomplete="off" value="0"<?php echo empty($country['default']) ? ' checked' : ''; ?>>
              <?php echo $this->text('No'); ?>
            </label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Disabled countries will not be displayed to customers'); ?>">
            <?php echo $this->text('Status'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($country['status']) ? '' : ' active'; ?>">
              <input name="country[status]" type="radio" autocomplete="off" value="1"<?php echo empty($country['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($country['status']) ? ' active' : ''; ?>">
              <input name="country[status]" type="radio" autocomplete="off" value="0"<?php echo empty($country['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['code']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Short alphabetic geographical code according to ISO 3166-2 standard'); ?>">
          <?php echo $this->text('Code'); ?>
          </span>
        </label>
        <div class="col-md-2">
          <input maxlength="2" name="country[code]" class="form-control" value="<?php echo isset($country['code']) ? $this->escape($country['code']) : ''; ?>">
          <?php if (isset($this->errors['code'])) { ?>
          <div class="help-block"><?php echo $this->errors['code']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['name']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('International name of the country in english according to ISO 3166-2 standard'); ?>">
          <?php echo $this->text('Name'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input maxlength="255" name="country[name]" class="form-control" value="<?php echo isset($country['name']) ? $this->escape($country['name']) : ''; ?>">
          <?php if (isset($this->errors['name'])) { ?>
          <div class="help-block"><?php echo $this->errors['name']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['native_name']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Local name of the country'); ?>">
          <?php echo $this->text('Native name'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input maxlength="255" name="country[native_name]" class="form-control" value="<?php echo isset($country['native_name']) ? $this->escape($country['native_name']) : ''; ?>">
          <?php if (isset($this->errors['native_name'])) { ?>
          <div class="help-block"><?php echo $this->errors['native_name']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['weight']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Items are displayed to users in ascending order by weight'); ?>">
          <?php echo $this->text('Weight'); ?>
          </span>
        </label>
        <div class="col-md-2">
          <input maxlength="2" name="country[weight]" class="form-control" value="<?php echo isset($country['weight']) ? $this->escape($country['weight']) : 0; ?>">
          <?php if (isset($this->errors['weight'])) { ?>
          <div class="help-block"><?php echo $this->errors['weight']; ?></div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</form>