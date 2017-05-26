<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-body">
  <div class="panel-body">
    <form method="post" class="register form-horizontal">
      <input type="hidden" name="token" value="<?php echo $_token; ?>">
      <div class="form-group<?php echo $this->error('name', ' has-error'); ?>">
        <label class="control-label col-md-2"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-4">
          <input class="form-control" maxlength="255" name="user[name]" value="<?php echo isset($user['name']) ? $this->e($user['name']) : ''; ?>">
          <div class="help-block"><?php echo $this->error('name'); ?></div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('email', ' has-error'); ?>">
        <label class="control-label col-md-2"><?php echo $this->text('E-mail'); ?></label>
        <div class="col-md-4">
          <input type="email" class="form-control" name="user[email]" value="<?php echo isset($user['email']) ? $this->e($user['email']) : ''; ?>" autofocus>
          <div class="help-block"><?php echo $this->error('email'); ?></div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('password', ' has-error'); ?>">
        <label class="control-label col-md-2"><?php echo $this->text('Password'); ?></label>
        <div class="col-md-4">
          <input class="form-control" type="password" name="user[password]" placeholder="<?php echo $this->text('@min - @max characters', array('@min' => $password_limit['min'], '@max' => $password_limit['max'])); ?>">
          <div class="help-block">
            <?php echo $this->error('password'); ?>
          </div>
        </div>
      </div>
      <?php echo $_captcha; ?>
      <div class="form-group">
        <div class="col-md-1 col-md-offset-2">
          <button class="btn btn-default" name="register" value="1"><?php echo $this->text('Register'); ?></button>
        </div>
        <div class="col-md-3 text-right">
          <ul class="list-inline">
            <li><a href="<?php echo $this->url('login'); ?>"><?php echo $this->text('Login'); ?></a></li>
            <li><a href="<?php echo $this->url('forgot'); ?>"><?php echo $this->text('Forgot password'); ?></a></li>
          </ul>
        </div>
      </div>
    </form>
  </div>
</div>