<div class="row">
  <form method="post" id="forgot" class="forgot col-md-6">
    <input name="url" class="collapse" value="">
    <input style="position:absolute;top:-9999px;">
    <input type="password" style="position:absolute;top:-9999px;">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <?php if ($recoverable_user) { ?>
    <div class="form-group">
    <?php echo $this->text('Hello %name. Please type your new password in the field below', array('%name' => $recoverable_user['name'])); ?>
    </div>
    <div class="form-group<?php echo isset($form_errors['password']) ? ' has-error' : ''; ?>">
      <label><?php echo $this->text('Password'); ?></label>
      <input class="form-control" type="password" pattern=".{<?php echo $min_password_length; ?>,<?php echo $max_password_length; ?>}" maxlength="<?php echo $max_password_length; ?>" name="user[password]" placeholder="<?php echo $this->text('Minimum @num characters', array('@num' => $min_password_length)); ?>" autofocus>
      <?php if (isset($form_errors['password'])) { ?>
      <div class="help-block"><?php echo $form_errors['password']; ?></div>
      <?php } ?>
    </div>
    <?php } else { ?>
    <div class="form-group<?php echo isset($form_errors['email']) ? ' has-error' : ''; ?>">
      <label><?php echo $this->text('E-mail'); ?></label>
      <input class="form-control" maxlength="255" name="user[email]" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>" autofocus>
      <?php if (isset($form_errors['email'])) { ?>
      <div class="help-block"><?php echo $form_errors['email']; ?></div>
      <?php } ?>
    </div>
    <?php } ?>
    <div class="form-group">
      <button class="btn btn-primary btn-block" name="reset" value="1">
        <?php if ($recoverable_user) { ?>
        <?php echo $this->text('Change password'); ?>
        <?php } else { ?>
        <?php echo $this->text('Reset password'); ?>
        <?php } ?>
      </button>
    </div>
  </form>
</div>