<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * 
 * To see available variables: <?php print_r(get_defined_vars()); ?>
 * To see the current controller object: <?php print_r($this); ?>
 * To call a controller method: <?php $this->exampleMethod(); ?>
 */
?>
<div class="panel panel-default">
  <div class="panel-body">
    <div class="row">
      <div class="col-md-3">
        <div class="list-group">
          <a href="<?php echo $this->url("account/{$user['user_id']}"); ?>" class="list-group-item">
            <h4 class="list-group-item-heading h5"><b><?php echo $this->truncate($this->escape($user['name']), 20); ?></b></h4>
            <p class="list-group-item-text"><?php echo $this->escape($user['email']); ?></p>
          </a>
          <a href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>" class="list-group-item">
            <h4 class="list-group-item-heading h5"><?php echo $this->text('Addresses'); ?></h4>
            <p class="list-group-item-text"><?php echo $this->text('View and manage addressbook'); ?></p>
          </a>
          <a class="list-group-item active disabled">
            <h4 class="list-group-item-heading h5"><?php echo $this->text('Settings'); ?></h4>
            <p class="list-group-item-text"><?php echo $this->text('Edit account details'); ?></p>
          </a>
        </div>
        <a class="btn btn-default" href="<?php echo $this->url('logout'); ?>">
          <span class="fa fa-sign-out"></span> <?php echo $this->text('Log out'); ?>
        </a>
      </div>
      <div class="col-md-9">
        <form method="post" id="edit-account" class="margin-top-20 form-horizontal<?php echo $this->error(null, ' form-errors'); ?>">
          <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
          <div class="form-group required<?php echo $this->error('email', ' has-error'); ?>">
            <label class="col-md-2 control-label"><?php echo $this->text('E-mail'); ?></label>
            <div class="col-md-6">
              <input name="user[email]" class="form-control" value="<?php echo isset($user['email']) ? $this->escape($user['email']) : ''; ?>">
              <div class="help-block"><?php echo $this->error('email'); ?></div>
            </div>
          </div>
          <div class="form-group required<?php echo $this->error('name', ' has-error'); ?>">
            <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
            <div class="col-md-6">
              <input name="user[name]" maxlength="255" class="form-control" value="<?php echo isset($user['name']) ? $this->escape($user['name']) : ''; ?>">
              <div class="help-block"><?php echo $this->error('name'); ?></div>
            </div>
          </div>
          <div class="form-group<?php echo $this->error('password', ' has-error'); ?>">
            <label class="col-md-2 control-label"><?php echo $this->text('New password'); ?></label>
            <div class="col-md-6">
              <input type="password" name="user[password]" autocomplete="new-password" class="form-control">
              <div class="help-block"><?php echo $this->error('password'); ?></div>
            </div>
          </div>
          <div class="form-group<?php echo $this->error('password_old', ' has-error'); ?>">
            <label class="col-md-2 control-label"><?php echo $this->text('Old password'); ?></label>
            <div class="col-md-6">
              <input type="password" name="user[password_old]" autocomplete="new-password" class="form-control">
              <div class="help-block"><?php echo $this->error('password_old'); ?></div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-6 col-md-offset-2">
              <button class="btn btn-default save" name="save" value="1">
                <?php echo $this->text('Save'); ?>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>