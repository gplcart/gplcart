<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>

<form method="post" class="form-horizontal edit-filter">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      

              
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Status'); ?>
        </label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($filter['status']) ? '' : ' active'; ?>">
              <input name="filter[status]" type="radio" autocomplete="off" value="1"<?php echo empty($filter['status']) ? '' : ' checked'; ?>>
              <?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($filter['status']) ? ' active' : ''; ?>">
              <input name="filter[status]" type="radio" autocomplete="off" value="0"<?php echo empty($filter['status']) ? ' checked' : ''; ?>>
              <?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block"><?php echo $this->text('Disabled filters will not process text, default minimal configuration will be used instead'); ?></div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Role'); ?></label>
        <div class="col-md-4">
          <select class="form-control" name="filter[role_id]">
            <option value="0"><?php echo $this->text('Anonymous'); ?></option>
            <?php foreach ($roles as $role_id => $role) { ?>
            <option value="<?php echo $role_id; ?>"<?php echo (isset($filter['role_id']) && $filter['role_id'] == $role_id) ? ' selected' : ''; ?>>
              <?php echo $this->escape($role['name']); ?>
            </option>
            <?php } ?>
          </select>
          <div class="text-muted">
            <?php echo $this->text('Select a role for this filter'); ?>
          </div>
        </div>
      </div>
      

      
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-4 col-md-offset-2">
          <div class="btn-toolbar">
            <a class="btn btn-default" href="<?php echo $this->url('admin/settings/filter'); ?>">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <button class="btn btn-default" name="save" value="1">
              <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div> 
</form>
