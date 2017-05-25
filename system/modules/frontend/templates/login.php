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
    <form method="post" id="login" class="login form-horizontal">
      <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
      <div class="form-group<?php echo $this->error('email', ' has-error'); ?>">
        <label class="control-label col-md-2"><?php echo $this->text('E-mail'); ?></label>
        <div class="col-md-4">
          <input class="form-control" name="user[email]" value="<?php echo isset($user['email']) ? $this->e($user['email']) : ''; ?>" autofocus>
          <div class="help-block"><?php echo $this->error('email'); ?></div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('password', ' has-error'); ?>">
        <label class="control-label col-md-2"><?php echo $this->text('Password'); ?></label>
        <div class="col-md-4">
          <input class="form-control" type="password" name="user[password]">
          <div class="help-block"><?php echo $this->error('password'); ?></div>
        </div>
      </div>
      <?php echo $_captcha; ?>
      <div class="form-group">
        <div class="col-md-10 col-md-offset-2">
          <button class="btn btn-default" name="login" value="1"><?php echo $this->text('Log in'); ?></button>
          <?php if(!empty($oauth_buttons)) { ?>
          <?php foreach($oauth_buttons as $oauth_button) { ?>
          &nbsp;&nbsp;<?php echo $oauth_button['rendered']; ?>
          <?php } ?>
          <?php } ?>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-10 col-md-offset-2">
          <ul class="list-inline">
            <li><a href="<?php echo $this->url('register'); ?>"><?php echo $this->text('Register'); ?></a></li>
            <li><a href="<?php echo $this->url('forgot'); ?>"><?php echo $this->text('Forgot password'); ?></a></li>
          </ul>
        </div>
      </div>
    </form>
  </div>
</div>

