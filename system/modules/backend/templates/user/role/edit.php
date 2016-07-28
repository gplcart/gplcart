<form method="post" id="edit-role" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6 col-md-offset-6 text-right">
      <?php if (isset($role['role_id']) && $this->access('user_role_delete')) { ?>
      <button class="btn btn-danger delete" name="delete" value="1">
        <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
      </button>
      <?php } ?>
      <a href="<?php echo $this->url('admin/user/role'); ?>" class="btn btn-default cancel">
        <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
      </a>
      <?php if ($this->access('user_role_edit') || $this->access('user_role_add')) { ?>
      <button class="btn btn-primary save" name="save" value="1">
        <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
      </button>
      <?php } ?>
    </div>
  </div>
  <div class="form-group margin-top-20 required<?php echo isset($this->errors['name']) ? ' has-error' : ''; ?>">
    <label class="col-md-1 control-label">
      <span class="hint" title="<?php echo $this->text('Give a descriptive name to the role. For example: Boss, Manager etc'); ?>">
      <?php echo $this->text('Name'); ?>
      </span>
    </label>
    <div class="col-md-4">
      <input maxlength="255" name="role[name]" class="form-control" value="<?php echo isset($role['name']) ? $this->escape($role['name']) : ''; ?>">
      <?php if (isset($this->errors['name'])) { ?>
      <div class="help-block"><?php echo $this->errors['name']; ?></div>
      <?php } ?>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-1 control-label">
      <?php echo $this->text('Status'); ?>
    </label>
    <div class="col-md-4">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo empty($role['status']) ? '' : ' active'; ?>">
          <input name="role[status]" type="radio" autocomplete="off" value="1"<?php echo empty($role['status']) ? '' : ' checked'; ?>>
          <?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-default<?php echo empty($role['status']) ? ' active' : ''; ?>">
          <input name="role[status]" type="radio" autocomplete="off" value="0"<?php echo empty($role['status']) ? ' checked' : ''; ?>>
          <?php echo $this->text('Disabled'); ?>
        </label>
      </div>
    </div>
  </div>
  <div class="form-group margin-top-20">
    <div class="col-md-4">
      <div class="checkbox">
        <label>
          <input type="checkbox" id="select-all" autocomplete="off"><?php echo $this->text('Select/unselect all'); ?>
        </label>
      </div>
    </div>
  </div>
  <div class="form-group">
    <?php foreach ($permissions as $permission_group) { ?>
    <div class="col-md-3">
      <?php foreach ($permission_group as $id => $name) { ?>
      <div class="checkbox">
        <label>
          <input type="checkbox" class="select-all" name="role[permissions][]" value="<?php echo $id; ?>"<?php echo(isset($role['permissions']) && in_array($id, $role['permissions'])) ? ' checked' : ''; ?>>
          <span class="hint" title="<?php echo $this->text('Access key: @s', array('@s' => $id)); ?>">
            <?php echo $this->escape($name); ?>
          </span>
        </label>
      </div>
      <?php } ?>
    </div>
    <?php } ?>
  </div>
</form>