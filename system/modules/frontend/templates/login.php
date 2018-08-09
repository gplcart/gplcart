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
<div class="row">
  <div class="col-md-12">
    <form method="post" id="login" class="login">
      <input type="hidden" name="token" value="<?php echo $_token; ?>">
      <div class="form-group<?php echo $this->error('email', ' has-error'); ?>">
        <label class="col-form-label col-md-2"><?php echo $this->text('E-mail'); ?></label>
        <div class="col-md-4">
          <input class="form-control" name="user[email]" value="<?php echo isset($user['email']) ? $this->e($user['email']) : ''; ?>" autofocus>
          <div class="form-text"><?php echo $this->error('email'); ?></div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('password', ' has-error'); ?>">
        <label class="col-form-label col-md-2"><?php echo $this->text('Password'); ?></label>
        <div class="col-md-4">
          <input class="form-control" type="password" name="user[password]">
          <div class="form-text"><?php echo $this->error('password'); ?></div>
        </div>
      </div>
      <?php if(!empty($_captcha)) { ?>
      <?php echo $_captcha; ?>
      <?php } ?>
      <div class="form-group">
        <div class="col-md-10 offset-md-2">
          <button class="btn" name="login" value="1"><?php echo $this->text('Log in'); ?></button>
          <?php if(!empty($oauth_buttons)) { ?>
          <?php echo $oauth_buttons; ?>
          <?php } ?>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-10 offset-md-2">
          <ul class="list-inline">
              <?php if(empty($_maintenance)) { ?>
            <li><a href="<?php echo $this->url('register'); ?>"><?php echo $this->text('Register'); ?></a></li>
          <?php } ?>
            <li><a href="<?php echo $this->url('forgot'); ?>"><?php echo $this->text('Forgot password'); ?></a></li>
          </ul>
        </div>
      </div>
    </form>
  </div>
</div>

