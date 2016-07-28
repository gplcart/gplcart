<div class="row">
  <div class="col-md-3">
    <div class="list-group">
      <a href="<?php echo $this->url("account/{$user['user_id']}"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading"><span class="fa fa-user"></span> <?php echo $this->truncate($this->escape($user['name']), 20); ?></h4>
        <p class="list-group-item-text"><?php echo $this->escape($user['email']); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading"><?php echo $this->text('Addresses'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('View and manage addressbook'); ?></p>
      </a>
      <a class="list-group-item active disabled">
        <h4 class="list-group-item-heading"><?php echo $this->text('Settings'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('Edit account details'); ?></p>
      </a>
    </div>
    <a href="<?php echo $this->url('logout'); ?>">
      <span class="fa fa-sign-out"></span> <?php echo $this->text('Log out'); ?>
    </a>
  </div>
  <div class="col-md-9">
    <form method="post" id="edit-account" class="margin-top-20 form-horizontal<?php echo !empty($this->errors) ? ' form-errors' : ''; ?>">
      <input type="hidden" name="token" value="<?php echo $this->token; ?>">
      <?php if ($this->access('user_edit')) { ?>   
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
        <div class="col-md-6">
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
      <?php if (!empty($roles)) { ?>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Role'); ?></label>
        <div class="col-md-6">
          <select name="user[role_id]" class="form-control">
            <option value=""><?php echo $this->text('None'); ?></option>
            <?php foreach ($roles as $role_id => $role) { ?>
            <?php if (isset($user['role_id']) && $user['role_id'] == $role_id) { ?>
            <option value="<?php echo $role_id; ?>" selected><?php echo $this->escape($role['name']); ?></option>
            <?php } else { ?>
            <option value="<?php echo $role_id; ?>"><?php echo $this->escape($role['name']); ?></option>
            <?php } ?>
            <?php } ?>
          </select>
        </div>
      </div>
      <?php } ?>
      <?php if (isset($stores) && count($stores) > 1) { ?>
          <div class="form-group">
            <label class="col-md-2 control-label"><?php echo $this->text('Store'); ?></label>
            <div class="col-md-6">
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
      <?php } ?>
      <?php } ?>
      <div class="form-group required<?php echo isset($this->errors['email']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('E-mail'); ?></label>
        <div class="col-md-6">
          <input type="email" name="user[email]" class="form-control" value="<?php echo isset($user['email']) ? $this->escape($user['email']) : ''; ?>">
          <?php if (isset($this->errors['email'])) { ?>
          <div class="help-block"><?php echo $this->errors['email']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group required<?php echo isset($this->errors['name']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-6">
          <input name="user[name]" maxlength="255" class="form-control" value="<?php echo isset($user['name']) ? $this->escape($user['name']) : ''; ?>">
          <?php if (isset($this->errors['name'])) { ?>
          <div class="help-block"><?php echo $this->errors['name']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['password']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('New password'); ?></label>
        <div class="col-md-6">
          <input type="password" name="user[password]" autocomplete="off" class="form-control">
          <?php if (isset($this->errors['password'])) { ?>
          <div class="help-block"><?php echo $this->errors['password']; ?></div>
          <?php } ?>
        </div>
      </div>
      <?php if (!$this->access('user_edit')) { ?>
      <div class="form-group<?php echo isset($this->errors['password_old']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Old password'); ?></label>
        <div class="col-md-6">
          <input type="password" name="user[password_old]" autocomplete="off" class="form-control">
          <?php if (isset($this->errors['password_old'])) { ?>
          <div class="help-block"><?php echo $this->errors['password_old']; ?></div>
          <?php } ?>
        </div>
      </div>
      <?php } ?>
      <div class="form-group">
        <div class="col-md-6 col-md-offset-2 text-right">
          <button class="btn btn-primary save" name="save" value="1">
            <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
