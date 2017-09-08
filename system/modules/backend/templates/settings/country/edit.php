<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
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
        <?php echo $this->text('Disabled countries will not be available to frontend users'); ?>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('code', ' has-error'); ?>">
    <label class="col-md-2 control-label">
      <?php echo $this->text('Code'); ?>
    </label>
    <div class="col-md-4">
      <?php if(empty($code)) { ?>
      <input maxlength="2" name="country[code]" class="form-control" value="<?php echo isset($country['code']) ? $this->e($country['code']) : ''; ?>">
      <?php } else if(isset($country['code'])) { ?>
      <span class="form-control"><?php echo $this->e($country['code']); ?></span>
      <?php } ?>
      <div class="help-block">
        <?php echo $this->error('code'); ?>
        <div class="text-muted">
          <?php echo $this->text('ISO 3166-2 code, e.g US'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('name', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
    <div class="col-md-4">
      <input maxlength="255" name="country[name]" class="form-control" value="<?php echo isset($country['name']) ? $this->e($country['name']) : ''; ?>">
      <div class="help-block">
        <?php echo $this->error('name'); ?>
        <div class="text-muted">
        <?php echo $this->text('International english name of the country according to ISO 3166-2 standard'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('native_name', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Native name'); ?></label>
    <div class="col-md-4">
      <input maxlength="255" name="country[native_name]" class="form-control" value="<?php echo isset($country['native_name']) ? $this->e($country['native_name']) : ''; ?>">
      <div class="help-block">
        <?php echo $this->error('native_name'); ?>
        <div class="text-muted">
          <?php echo $this->text('Local name of the country, e.g 中国'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('zone_id', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Zone'); ?></label>
    <div class="col-md-4">
      <select name="country[zone_id]" class="form-control">
        <option value="0"><?php echo $this->text('None'); ?></option>
        <?php foreach ($zones as $zone) { ?>
        <?php if (isset($country['zone_id']) && $country['zone_id'] == $zone['zone_id']) { ?>
        <option value="<?php echo $zone['zone_id']; ?>" selected><?php echo $this->e($zone['title']); ?></option>
        <?php } else { ?>
        <option value="<?php echo $zone['zone_id']; ?>"><?php echo $this->e($zone['title']); ?></option>
        <?php } ?>
        <?php } ?>
      </select>
      <div class="help-block">
        <?php echo $this->error('zone_id'); ?>
        <div class="text-muted">
          <?php echo $this->text('Zones are geographic regions that you ship goods to. Each zone provides shipping rates that apply to customers whose addresses are within that zone.'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('weight', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Weight'); ?></label>
    <div class="col-md-4">
      <input maxlength="2" name="country[weight]" class="form-control" value="<?php echo isset($country['weight']) ? $this->e($country['weight']) : 0; ?>">
      <div class="help-block">
        <?php echo $this->error('weight'); ?>
        <div class="text-muted">
          <?php echo $this->text('Countries are sorted in lists by the weight value. Lower value means higher position'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-10 col-md-offset-2">
      <div class="btn-toolbar">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Are you sure? It cannot be undone!'));">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/settings/country'); ?>" class="btn btn-default"><?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('country_edit') || $this->access('country_add')) { ?>
        <button class="btn btn-default save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>