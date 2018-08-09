<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\frontend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<div class="row edit-account">
  <div class="col-md-3">
    <div class="list-group">
      <a class="list-group-item list-group-item-action" href="<?php echo $this->url("account/{$user['user_id']}"); ?>">
        <h4 class="h5"><b><?php echo $this->e($this->truncate($user['name'], 20)); ?></b></h4>
        <p><?php echo $this->e($user['email']); ?></p>
      </a>
      <a class="list-group-item list-group-item-action" href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>">
        <h4 class="h5"><?php echo $this->text('Addresses'); ?></h4>
        <p><?php echo $this->text('View and manage addressbook'); ?></p>
      </a>
      <?php if ($_uid == $user['user_id'] || $this->access('user_edit')) { ?>
      <a class="list-group-item list-group-item-action active disabled" href="<?php echo $this->url("account/{$user['user_id']}/edit"); ?>">
        <h4 class="h5"><?php echo $this->text('Settings'); ?></h4>
        <p><?php echo $this->text('Edit account details'); ?></p>
      </a>
      <?php } ?>
    </div>
  </div>
  <div class="col-md-9">
    <form method="post" id="edit-account">
      <input type="hidden" name="token" value="<?php echo $_token; ?>">
      <div class="form-group row required<?php echo $this->error('email', ' has-error'); ?>">
        <label class="col-md-3 col-form-label"><?php echo $this->text('E-mail'); ?></label>
        <div class="col-md-6">
          <input name="user[email]" class="form-control" value="<?php echo isset($user['email']) ? $this->e($user['email']) : ''; ?>">
          <div class="form-text"><?php echo $this->error('email'); ?></div>
        </div>
      </div>
      <div class="form-group row required<?php echo $this->error('name', ' has-error'); ?>">
        <label class="col-md-3 col-form-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-6">
          <input name="user[name]" maxlength="255" class="form-control" value="<?php echo isset($user['name']) ? $this->e($user['name']) : ''; ?>">
          <div class="form-text"><?php echo $this->error('name'); ?></div>
        </div>
      </div>
      <div class="form-group row<?php echo $this->error('password', ' has-error'); ?>">
        <label class="col-md-3 col-form-label"><?php echo $this->text('New password'); ?></label>
        <div class="col-md-6">
          <input type="password" name="user[password]" autocomplete="new-password" class="form-control">
          <div class="form-text"><?php echo $this->error('password'); ?></div>
        </div>
      </div>
      <div class="form-group row<?php echo $this->error('password_old', ' has-error'); ?>">
        <label class="col-md-3 col-form-label"><?php echo $this->text('Existing password'); ?></label>
        <div class="col-md-6">
          <input type="password" name="user[password_old]" autocomplete="new-password" class="form-control">
          <div class="form-text"><?php echo $this->error('password_old'); ?></div>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-6 offset-md-3">
          <button class="btn save" name="save" value="1">
            <?php echo $this->text('Save'); ?>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>