<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="row edit-account">
  <div class="col-md-3">
    <div class="list-group">
      <a href="<?php echo $this->url("account/{$user['user_id']}"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading h5"><b><?php echo $this->e($this->truncate($user['name'], 20)); ?></b></h4>
        <p class="list-group-item-text"><?php echo $this->e($user['email']); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading h5"><?php echo $this->text('Addresses'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('View and manage addressbook'); ?></p>
      </a>
      <?php if ($this->user('user_id') == $user['user_id'] || $this->access('user_edit')) { ?>
      <a class="list-group-item active disabled" href="<?php echo $this->url("account/{$user['user_id']}/edit"); ?>">
        <h4 class="list-group-item-heading h5"><?php echo $this->text('Settings'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('Edit account details'); ?></p>
      </a>
      <?php } ?>
    </div>
    <?php if ($this->user('user_id') == $user['user_id']) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('logout'); ?>">
      <span class="fa fa-sign-out"></span> <?php echo $this->text('Log out'); ?>
    </a>
    <?php } ?>
  </div>
  <div class="col-md-9">
    <form method="post" id="edit-account" class="form-horizontal">
      <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
      <div class="form-group required<?php echo $this->error('email', ' has-error'); ?>">
        <label class="col-md-3 control-label"><?php echo $this->text('E-mail'); ?></label>
        <div class="col-md-6">
          <input name="user[email]" class="form-control" value="<?php echo isset($user['email']) ? $this->e($user['email']) : ''; ?>">
          <div class="help-block"><?php echo $this->error('email'); ?></div>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('name', ' has-error'); ?>">
        <label class="col-md-3 control-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-6">
          <input name="user[name]" maxlength="255" class="form-control" value="<?php echo isset($user['name']) ? $this->e($user['name']) : ''; ?>">
          <div class="help-block"><?php echo $this->error('name'); ?></div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('password', ' has-error'); ?>">
        <label class="col-md-3 control-label"><?php echo $this->text('New password'); ?></label>
        <div class="col-md-6">
          <input type="password" name="user[password]" autocomplete="new-password" class="form-control">
          <div class="help-block"><?php echo $this->error('password'); ?></div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('password_old', ' has-error'); ?>">
        <label class="col-md-3 control-label"><?php echo $this->text('Existing password'); ?></label>
        <div class="col-md-6">
          <input type="password" name="user[password_old]" autocomplete="new-password" class="form-control">
          <div class="help-block"><?php echo $this->error('password_old'); ?></div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-6 col-md-offset-3">
          <button class="btn btn-default save" name="save" value="1">
            <?php echo $this->text('Save'); ?>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>