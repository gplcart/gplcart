<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" id="edit-city" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
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
          <input maxlength="255" name="city[name]" class="form-control" value="<?php echo isset($city['name']) ? $this->escape($city['name']) : ''; ?>" autofocus>
          <div class="help-block">
            <?php echo $this->error('name'); ?>
            <div class="text-muted"><?php echo $this->text('Required. Native name of the city'); ?></div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('zone_id', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Zone'); ?>
        </label>
        <div class="col-md-4">
          <select name="city[zone_id]" class="form-control">
            <option value="0"><?php echo $this->text('None'); ?></option>
            <?php foreach ($zones as $zone) { ?>
            <?php if (isset($city['zone_id']) && $city['zone_id'] == $zone['zone_id']) { ?>
            <option value="<?php echo $zone['zone_id']; ?>" selected><?php echo $this->escape($zone['title']); ?></option>
            <?php } else { ?>
            <option value="<?php echo $zone['zone_id']; ?>"><?php echo $this->escape($zone['title']); ?></option>
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
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <?php if ($can_delete) { ?>
          <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Delete? It cannot be undone!'));">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-4">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url("admin/settings/cities/{$country['code']}/{$state['state_id']}"); ?>" class="btn btn-default">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('city_edit') || $this->access('city_add')) { ?>
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