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
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php if (!$is_superadmin) { ?>
  <div class="form-group">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Status'); ?></label>
    <div class="col-md-4">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn<?php echo empty($user['status']) ? '' : ' active'; ?>">
          <input name="user[status]" type="radio" autocomplete="off" value="1"<?php echo empty($user['status']) ? '' : ' checked'; ?>>
          <?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn<?php echo empty($user['status']) ? ' active' : ''; ?>">
          <input name="user[status]" type="radio" autocomplete="off" value="0"<?php echo empty($user['status']) ? ' checked' : ''; ?>>
          <?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <div class="form-text"><?php echo $this->text('Disabled users are not allowed to login'); ?></div>
    </div>
  </div>
  <?php } ?>
  <div class="form-group required<?php echo $this->error('name', ' has-error'); ?>">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Name'); ?></label>
    <div class="col-md-4">
      <input name="user[name]" maxlength="255" class="form-control" value="<?php echo isset($user['name']) ? $this->e($user['name']) : ''; ?>">
      <div class="form-text">
        <?php echo $this->error('name'); ?>
        <div class="text-muted"><?php echo $this->text('User name, e.g John Smith'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('email', ' has-error'); ?>">
    <label class="col-md-2 col-form-label"><?php echo $this->text('E-mail'); ?></label>
    <div class="col-md-4">
      <input name="user[email]" class="form-control" value="<?php echo isset($user['email']) ? $this->e($user['email']) : ''; ?>">
      <div class="form-text">
        <?php echo $this->error('email'); ?>
        <div class="text-muted"><?php echo $this->text('The e-mail is used both for contacting and authorizing the user'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('password', ' has-error'); ?>">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Password'); ?></label>
    <div class="col-md-4">
      <input name="user[password]" class="form-control" value="<?php echo isset($user['password']) ? $user['password'] : ''; ?>">
      <div class="form-text">
        <?php echo $this->error('password'); ?>
        <div class="text-muted"><?php echo $this->text('The password is used for authorizing the user. It should be @min - @max characters long', array('@min' => $password_limit[0], '@max' => $password_limit[1])); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Role'); ?></label>
    <div class="col-md-4">
      <select name="user[role_id]" class="form-control">
        <option value=""><?php echo $this->text('None'); ?></option>
        <?php if (!empty($roles)) { ?>
        <?php foreach ($roles as $role_id => $role) { ?>
        <?php if (isset($user['role_id']) && $user['role_id'] == $role_id) { ?>
        <option value="<?php echo $role_id; ?>" selected><?php echo $this->e($role['name']); ?></option>
        <?php } else { ?>
        <option value="<?php echo $role_id; ?>"><?php echo $this->e($role['name']); ?></option>
        <?php } ?>
        <?php } ?>
        <?php } ?>
      </select>
      <div class="form-text"><?php echo $this->text('Roles are sets of permissions that control what users can do and see on the site'); ?></div>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Store'); ?></label>
    <div class="col-md-4">
      <select name="user[store_id]" class="form-control">
        <?php foreach ($_stores as $store_id => $store) { ?>
        <?php if (isset($user['store_id']) && $user['store_id'] == $store_id) { ?>
        <option value="<?php echo $store_id; ?>" selected><?php echo $this->e($store['name']); ?></option>
        <?php } else { ?>
        <option value="<?php echo $store_id; ?>"><?php echo $this->e($store['name']); ?></option>
        <?php } ?>
        <?php } ?>
      </select>
      <div class="form-text"><?php echo $this->text('Associate this user with a certain store'); ?></div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-10 offset-md-2">
      <div class="btn-toolbar">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a class="btn cancel" href="<?php echo $this->url('admin/user/list'); ?>">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <button class="btn save" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
      </div>
    </div>
  </div>
</form>