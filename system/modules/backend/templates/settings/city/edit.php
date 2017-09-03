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
        <label class="btn btn-default<?php echo empty($city['status']) ? '' : ' active'; ?>">
          <input name="city[status]" type="radio" autocomplete="off" value="1"<?php echo empty($city['status']) ? '' : ' checked'; ?>>
          <?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-default<?php echo empty($city['status']) ? ' active' : ''; ?>">
          <input name="city[status]" type="radio" autocomplete="off" value="0"<?php echo empty($city['status']) ? ' checked' : ''; ?>>
          <?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <div class="help-block">
        <?php echo $this->text('Disabled cities will not be displayed to customers'); ?>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('name', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
    <div class="col-md-4">
      <input maxlength="255" name="city[name]" class="form-control" value="<?php echo isset($city['name']) ? $this->e($city['name']) : ''; ?>" autofocus>
      <div class="help-block">
        <?php echo $this->error('name'); ?>
        <div class="text-muted"><?php echo $this->text('Required. Native name of the city'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('zone_id', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Zone'); ?></label>
    <div class="col-md-4">
      <select name="city[zone_id]" class="form-control">
        <option value="0"><?php echo $this->text('None'); ?></option>
        <?php foreach ($zones as $zone) { ?>
        <?php if (isset($city['zone_id']) && $city['zone_id'] == $zone['zone_id']) { ?>
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
  <div class="form-group">
    <div class="col-md-10 col-md-offset-2">
      <div class="btn-toolbar">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Are you sure? It cannot be undone!'));">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a class="btn btn-default" href="<?php echo $this->url("admin/settings/cities/{$country['code']}/{$state['state_id']}"); ?>">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('city_edit') || $this->access('city_add')) { ?>
        <button class="btn btn-default save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>