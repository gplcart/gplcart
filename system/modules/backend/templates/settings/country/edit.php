<form method="post" id="edit-country" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group required<?php echo isset($this->errors['code']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Code'); ?>
        </label>
        <div class="col-md-4">
          <input maxlength="2" name="country[code]" class="form-control" value="<?php echo isset($country['code']) ? $this->escape($country['code']) : ''; ?>">
          <div class="help-block">
            <?php if (isset($this->errors['code'])) { ?>
              <?php echo $this->errors['code']; ?>
            <?php } ?>
            <div class="text-muted">
              <?php echo $this->text('Required. A code according to ISO 3166-2 standard, e.g US'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo isset($this->errors['name']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-4">
          <input maxlength="255" name="country[name]" class="form-control" value="<?php echo isset($country['name']) ? $this->escape($country['name']) : ''; ?>">
          <div class="help-block">
           <?php if (isset($this->errors['name'])) { ?>
           <?php echo $this->errors['name']; ?>
           <?php } ?>
           <div class="text-muted">
            <?php echo $this->text('Required. An international english name of the country according to ISO 3166-2 standard'); ?>
           </div>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo isset($this->errors['native_name']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Native name'); ?></label>
        <div class="col-md-4">
          <input maxlength="255" name="country[native_name]" class="form-control" value="<?php echo isset($country['native_name']) ? $this->escape($country['native_name']) : ''; ?>">
          <div class="help-block">
            <?php if (isset($this->errors['native_name'])) { ?>
            <?php echo $this->errors['native_name']; ?>
            <?php } ?>
           <div class="text-muted">
            <?php echo $this->text('Required. A local name of the country, e.g 中国'); ?>
           </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Zone'); ?>
        </label>
        <div class="col-md-4">
          <select name="country[zone_id]" class="form-control">
            <option value=""><?php echo $this->text('None'); ?></option>
            <?php foreach ($zones as $zone) { ?>
            <?php if (isset($country['zone_id']) && $country['zone_id'] == $zone['zone_id']) { ?>
            <option value="<?php echo $zone['zone_id']; ?>" selected><?php echo $this->escape($zone['title']); ?></option>
            <?php } else { ?>
            <option value="<?php echo $zone['zone_id']; ?>"><?php echo $this->escape($zone['title']); ?></option>
            <?php } ?>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->text('Zones are geographic regions that you ship goods to. Each zone provides shipping rates that apply to customers whose addresses are within that zone.'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Default'); ?></label>
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
          <div class="help-block">
            <?php echo $this->text('Use this country by default in addresses'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($country['status']) ? '' : ' active'; ?>">
              <input name="country[status]" type="radio" autocomplete="off" value="1"<?php echo empty($country['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($country['status']) ? ' active' : ''; ?>">
              <input name="country[status]" type="radio" autocomplete="off" value="0"<?php echo empty($country['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
            <?php echo $this->text('Disabled countries will not be available for frontend users'); ?>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['weight']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Weight'); ?></label>
        <div class="col-md-4">
          <input maxlength="2" name="country[weight]" class="form-control" value="<?php echo isset($country['weight']) ? $this->escape($country['weight']) : 0; ?>">
          <div class="help-block">
            <?php if (isset($this->errors['weight'])) { ?>
            <?php echo $this->errors['weight']; ?>
            <?php } ?>
            <div class="text-muted">
              <?php echo $this->text('Countries are sorted in lists by the weight value. Lower value means higher position'); ?>
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
          <?php if ($can_delete) { ?>
          <button class="btn btn-danger delete" name="delete" value="1">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-4">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url('admin/settings/country'); ?>" class="btn btn-default"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
            <?php if ($this->access('country_edit') || $this->access('country_add')) { ?>
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