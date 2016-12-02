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
<div class="panel panel-body">
  <div class="panel-body">
    <form method="post" id="register" class="register form-horizontal">
      <?php echo $honeypot; ?>
      <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
      <div class="form-group<?php echo $this->error('name', ' has-error'); ?>">
        <label class="control-label col-md-2"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-4">
          <input class="form-control" maxlength="255" name="user[name]" value="<?php echo isset($user['name']) ? $user['name'] : ''; ?>">
          <div class="help-block"><?php echo $this->error('name'); ?></div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('email', ' has-error'); ?>">
        <label class="control-label col-md-2"><?php echo $this->text('E-mail'); ?></label>
        <div class="col-md-4">
          <input type="email" class="form-control" name="user[email]" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>" autofocus>
          <div class="help-block"><?php echo $this->error('email'); ?></div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('password', ' has-error'); ?>">
        <label class="control-label col-md-2"><?php echo $this->text('Password'); ?></label>
        <div class="col-md-4">
          <input class="form-control" type="password" name="user[password]">
          <div class="help-block">
            <?php echo $this->error('password'); ?>
            <div class="text-muted">
              <?php if (!empty($password_limit['min'])) { ?>
              <?php echo $this->text('Minimum length: %min characters', array('%min' => $password_limit['min'])); ?>
              <?php } ?>
              <?php if (!empty($password_limit['max'])) { ?>
              <?php echo $this->text('Maximum length: %max characters', array('%max' => $password_limit['max'])); ?>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2 text-right">
          <button class="btn btn-default" name="register" value="1">
          <?php echo $this->text('Register'); ?>
          </button>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2">
          <ul class="list-inline">
            <li><a href="<?php echo $this->url('login'); ?>"><?php echo $this->text('Login'); ?></a></li>
            <li><a href="<?php echo $this->url('forgot'); ?>"><?php echo $this->text('Forgot password'); ?></a></li>
          </ul>
        </div>
      </div>
    </form>
  </div>
</div>