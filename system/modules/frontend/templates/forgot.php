<div class="row">
  <form method="post" id="forgot" class="forgot col-md-6">
    <input name="url" class="collapse" value="">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <?php if (empty($recoverable_user)) { ?>
    <div class="form-group<?php echo $this->error('email', ' has-error'); ?>">
      <label><?php echo $this->text('E-mail'); ?></label>
      <input class="form-control" maxlength="255" name="user[email]" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>" autofocus>
      <?php if ($this->error('email', true)) { ?>
      <div class="help-block"><?php echo $this->error('email'); ?></div>
      <?php } ?>
    </div>
    <?php } else { ?>
    <div class="form-group">
    <?php echo $this->text('Hello %name. Please type your new password in the field below', array('%name' => $recoverable_user['name'])); ?>
    </div>
    <div class="form-group<?php echo $this->error('password', ' has-error'); ?>">
      <label><?php echo $this->text('Password'); ?></label>
      <input class="form-control" type="password" autocomplete="new-password" pattern=".{<?php echo $min_password_length; ?>,<?php echo $max_password_length; ?>}" maxlength="<?php echo $max_password_length; ?>" name="user[password]" placeholder="<?php echo $this->text('Minimum @num characters', array('@num' => $min_password_length)); ?>" autofocus>
      <?php if ($this->error('password', true)) { ?>
      <div class="help-block"><?php echo $this->error('password'); ?></div>
      <?php } ?>
    </div>
    <?php } ?>
    <div class="form-group">
      <button class="btn btn-primary btn-block" name="reset" value="1">
        <?php if (empty($recoverable_user)) { ?>
        <?php echo $this->text('Reset password'); ?>
        <?php } else { ?>
        <?php echo $this->text('Change password'); ?>
        <?php } ?>
      </button>
    </div>
  </form>
</div>