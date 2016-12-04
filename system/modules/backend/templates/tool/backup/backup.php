<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" class="form-horizontal add-backup">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group required<?php echo $this->error('name', ' has-error'); ?>">
        <label class="col-md-1 control-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-4">
          <input class="form-control" name="backup[name]" value="<?php echo isset($backup['name']) ? $backup['name'] : $this->text('Backup from @date', array('@date' => $this->date(null, false))); ?>">
          <div class="help-block">
            <?php echo $this->error('name'); ?>
            <div class="text-muted">
              <?php echo $this->text('A short descriptive name of the backup for administrators'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-1 control-label"><?php echo $this->text('Type'); ?></label>
        <div class="col-md-4">
          <select class="form-control" name="backup[type]">
            <?php foreach ($handlers as $handler_id => $handler) { ?>
            <option value="<?php echo $handler_id; ?>"><?php echo $this->escape($handler['name']); ?></option>
            <?php } ?>
          </select>
          <div class="help-block"><?php echo $this->text('Select a type of backup'); ?></div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-1 control-label"><?php echo $this->text('Module'); ?></label>
        <div class="col-md-4">
          <select class="form-control" name="backup[module_id]">
            <?php foreach ($modules as $module_id => $module) { ?>
            <option value="<?php echo $module_id; ?>"><?php echo $this->escape($module['name']); ?></option>
            <?php } ?>
          </select>
          <div class="help-block"><?php echo $this->text('Select a module to be saved. It\'s only applies to "Module" backup type'); ?></div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-11 col-md-offset-1">
          <button class="btn btn-default" name="save" value="1"><?php echo $this->text('Backup'); ?></button>
        </div>
      </div>
    </div>
  </div>
</form>
