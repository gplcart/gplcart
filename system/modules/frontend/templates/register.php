<div class="row">
  <form method="post" id="register" class="register col-md-6<?php echo isset($form_errors) ? ' form-errors' : ''; ?>">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <?php if ($this->access('user_add')) { ?> 
    <div class="form-group">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo empty($user['status']) ? '' : ' active'; ?>">
          <input name="user[status]" type="radio" autocomplete="off" value="1"<?php echo empty($user['status']) ? '' : ' checked'; ?>>
          <?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-default<?php echo empty($user['status']) ? ' active' : ''; ?>">
          <input name="user[status]" type="radio" autocomplete="off" value="0"<?php echo empty($user['status']) ? ' checked' : ''; ?>>
          <?php echo $this->text('Disabled'); ?>
        </label>
      </div>
    </div>
    <?php if (!empty($roles)) { ?>
    <div class="form-group">
      <label><?php echo $this->text('Role'); ?></label>
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
    <?php } ?>
    <?php if (isset($stores) && count($stores) > 1) { ?>
    <div class="form-group">
      <label><?php echo $this->text('Store'); ?></label>
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
    <?php } ?>
    <?php } ?>
    <div class="form-group<?php echo isset($form_errors['email']) ? ' has-error' : ''; ?>">
      <label><?php echo $this->text('E-mail'); ?></label>
      <input type="email" class="form-control" maxlength="255" name="user[email]" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>" autofocus required>
      <?php if (isset($form_errors['email'])) { ?>
      <div class="help-block"><?php echo $form_errors['email']; ?></div>
      <?php } ?>
    </div>
    <div class="form-group<?php echo isset($form_errors['password']) ? ' has-error' : ''; ?>">
      <label><?php echo $this->text('Password'); ?></label>
      <input class="form-control" type="password" pattern=".{<?php echo $min_password_length; ?>,<?php echo $max_password_length; ?>}" maxlength="<?php echo $max_password_length; ?>" name="user[password]" placeholder="<?php echo $this->text('Minimum @num characters', array('@num' => $min_password_length)); ?>" required>
      <?php if (isset($form_errors['password'])) { ?> 
      <div class="help-block"><?php echo $form_errors['password']; ?></div>
      <?php } ?>
    </div>
    <div class="form-group<?php echo isset($form_errors['name']) ? ' has-error' : ''; ?>">
      <label><?php echo $this->text('Name'); ?></label>
      <input class="form-control" maxlength="255" name="user[name]" value="<?php echo isset($user['name']) ? $user['name'] : ''; ?>">
      <?php if (isset($form_errors['name'])) { ?>
      <div class="help-block"><?php echo $form_errors['name']; ?></div>
      <?php } ?>
    </div>
    <div class="form-group">
      <?php if ($this->access('user_add')) { ?>
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo empty($user['status']) ? '' : ' active'; ?>">
          <input name="user[notify]" type="checkbox" autocomplete="off" value="1"<?php echo empty($user['status']) ? '' : ' checked'; ?>>
          <?php echo $this->text('Notify'); ?>
        </label>
      </div>
      <button class="btn btn-primary" name="register" value="1">
      <?php echo $this->text('Register'); ?>
      </button>
      <?php } else { ?>
      <button class="btn btn-primary btn-block" name="register" value="1">
      <?php echo $this->text('Register'); ?>
      </button>       
      <?php } ?>
    </div>
    <?php if (!$this->access('user_add')) { ?>
    <div class="form-group">
      <a href="<?php echo $this->url('login'); ?>"><?php echo $this->text('Login'); ?></a>
    </div>
    <?php } ?> 
    <input name="url" style="position:absolute;top:-999px;" value="">
  </form>
</div>