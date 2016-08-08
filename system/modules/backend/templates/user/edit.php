<form method="post" id="edit-account" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group required<?php echo isset($this->errors['name']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-4">
          <input name="user[name]" maxlength="255" class="form-control" value="<?php echo isset($user['name']) ? $this->escape($user['name']) : ''; ?>">
          <?php if (isset($this->errors['name'])) { ?>
          <div class="help-block"><?php echo $this->errors['name']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group required<?php echo isset($this->errors['email']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('E-mail'); ?></label>
        <div class="col-md-4">
          <input name="user[email]" class="form-control" value="<?php echo isset($user['email']) ? $this->escape($user['email']) : ''; ?>">
          <?php if (isset($this->errors['email'])) { ?>
          <div class="help-block"><?php echo $this->errors['email']; ?></div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <?php if(!$is_superadmin) { ?>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo!empty($user['status']) ? ' active' : ''; ?>">
              <input name="user[status]" type="radio" autocomplete="off" value="1"<?php echo!empty($user['status']) ? ' checked' : ''; ?>>
              <?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($user['status']) ? ' active' : ''; ?>">
              <input name="user[status]" type="radio" autocomplete="off" value="0"<?php echo empty($user['status']) ? ' checked' : ''; ?>>
              <?php echo $this->text('Disabled'); ?>
            </label>
          </div>
        </div>
      </div>
      <?php } ?>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Role'); ?></label>
        <div class="col-md-4">
          <select name="user[role_id]" class="form-control">
            <option value=""><?php echo $this->text('None'); ?></option>
            <?php if (!empty($roles)) { ?>
            <?php foreach ($roles as $role_id => $role) { ?>
            <?php if (isset($user['role_id']) && $user['role_id'] == $role_id) { ?>
            <option value="<?php echo $role_id; ?>" selected><?php echo $this->escape($role['name']); ?></option>
            <?php } else { ?>
            <option value="<?php echo $role_id; ?>"><?php echo $this->escape($role['name']); ?></option>
            <?php } ?>
            <?php } ?>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Store'); ?></label>
        <div class="col-md-4">
          <select name="user[store_id]" class="form-control">
            <?php foreach ($stores as $store_id => $store_name) { ?>
            <?php if (isset($user['store_id']) && $user['store_id'] == $store_id) { ?>
            <option value="<?php echo $store_id; ?>" selected><?php echo $this->escape($store_name); ?></option>
            <?php } else { ?>
            <option value="<?php echo $store_id; ?>"><?php echo $this->escape($store_name); ?></option>
            <?php } ?>
            <?php } ?>
          </select>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group<?php echo isset($this->errors['password']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Password'); ?></label>
        <div class="col-md-4"> 
          <div class="input-group">
            <input name="user[password]" class="form-control" value="<?php echo isset($user['password']) ? $this->escape($user['password']) : ''; ?>">
            <span class="input-group-btn">
              <button class="btn btn-default" type="button" onclick="$(this).closest('.input-group').find('input').val(GplCart.randomString());">
                <i class="fa fa-magic"></i>
              </button>
            </span>
          </div>
            <?php if (isset($this->errors['password'])) { ?>
            <div class="help-block"><?php echo $this->errors['password']; ?></div>
            <?php } ?>
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
            <i class="fa fa-floppy-o"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-4">
          <div class="btn-toolbar">
            <a class="btn btn-default cancel" href="<?php echo $this->url('admin/user'); ?>">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>    
            <button class="btn btn-default save" name="save" value="1">
              <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>