<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
  <div class="panel-body">
    <form method="post" class="forgot form-horizontal">
      <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
      <?php if (empty($forgetful_user)) { ?>
      <div class="form-group<?php echo $this->error('email', ' has-error'); ?>">
        <label class="control-label col-md-2"><?php echo $this->text('E-mail'); ?></label>
        <div class="col-md-4">
          <input class="form-control" name="user[email]" value="<?php echo isset($user['email']) ? $this->e($user['email']) : ''; ?>" autofocus>
          <div class="help-block"><?php echo $this->error('email'); ?></div>
        </div>
      </div>
      <?php } else { ?>
      <div class="form-group<?php echo $this->error('password', ' has-error'); ?>">
        <label class="control-label col-md-2"><?php echo $this->text('New password'); ?></label>
        <div class="col-md-4">
          <input class="form-control" type="password" name="user[password]" autocomplete="new-password" autofocus>
          <div class="help-block"><?php echo $this->error('password'); ?></div>
        </div>
      </div>
      <?php } ?>
      <?php echo $_captcha; ?>
      <div class="form-group">
        <div class="col-md-2 col-md-offset-2">
          <button class="btn btn-default" name="reset" value="1">
          <?php if (empty($forgetful_user)) { ?>
          <?php echo $this->text('Reset password'); ?>
          <?php } else { ?>
          <?php echo $this->text('Change password'); ?>
          <?php } ?>
          </button>
        </div>
        <div class="col-md-2 text-right">
          <ul class="list-inline">
            <li><a href="<?php echo $this->url('login'); ?>"><?php echo $this->text('Login'); ?></a></li>
            <li><a href="<?php echo $this->url('register'); ?>"><?php echo $this->text('Register'); ?></a></li>
          </ul>
        </div>
      </div>
    </form>
  </div>
</div>