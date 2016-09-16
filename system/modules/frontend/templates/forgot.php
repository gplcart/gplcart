<div class="panel panel-default">
  <div class="panel-body">
    <form method="post" id="forgot" class="forgot col-md-6">
      <?php echo $honeypot; ?>
      <input type="hidden" name="token" value="<?php echo $token; ?>">
      <?php if (empty($forgetful_user)) { ?>
      <div class="form-group<?php echo isset($this->errors['email']) ? ' has-error' : ''; ?>">
        <label><?php echo $this->text('E-mail'); ?></label>
        <input class="form-control" name="user[email]" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>" autofocus>
        <?php if (isset($this->errors['email'])) { ?>
        <div class="help-block"><?php echo $this->errors['email']; ?></div>
        <?php } ?>
      </div>
      <?php } else { ?>
      <div class="form-group">
        <?php echo $this->text('Hello %name. Please type your new password', array('%name' => $forgetful_user['name'])); ?>
      </div>
      <div class="form-group<?php echo isset($this->errors['password']) ? ' has-error' : ''; ?>">
        <label><?php echo $this->text('Password'); ?></label>
        <input class="form-control" type="password" name="user[password]" autocomplete="new-password" autofocus>
        <?php if (isset($this->errors['password'])) { ?>
        <div class="help-block"><?php echo $this->errors['password']; ?></div>
        <?php } ?>
      </div>
      <?php } ?>
      <div class="form-group">
        <button class="btn btn-default" name="reset" value="1">
          <?php if ($forgetful_user) { ?>
          <?php echo $this->text('Change password'); ?>
          <?php } else { ?>
          <?php echo $this->text('Reset password'); ?>
          <?php } ?>
        </button>
      </div>
    </form>
  </div>
</div>