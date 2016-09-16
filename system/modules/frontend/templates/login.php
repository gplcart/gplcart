<div class="panel panel-default">
  <div class="panel-body">
    <form method="post" id="login" class="login col-md-6">
      <input type="hidden" name="token" value="<?php echo $token; ?>">
      <?php echo $honeypot; ?>
      <div class="form-group<?php echo isset($this->errors['email']) ? ' has-error' : ''; ?>">
        <label><?php echo $this->text('E-mail'); ?></label>
        <input type="email" class="form-control" name="user[email]" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>" autofocus>
        <?php if (isset($this->errors['email'])) { ?>
        <div class="help-block"><?php echo $this->errors['email']; ?></div>
        <?php } ?>
      </div>
      <div class="form-group<?php echo isset($this->errors['password']) ? ' has-error' : ''; ?>">
        <label><?php echo $this->text('Password'); ?></label>
        <input class="form-control" type="password" name="user[password]">
        <?php if (isset($this->errors['password'])) { ?>
        <div class="help-block"><?php echo $this->errors['password']; ?></div>
        <?php } ?>
      </div>
      <div class="form-group">
        <button class="btn btn-primary btn-block" name="login" value="1"><?php echo $this->text('Log in'); ?></button>
      </div>
      <div class="form-group">
        <a href="<?php echo $this->url('register'); ?>"><?php echo $this->text('Register account'); ?></a> |
        <a href="<?php echo $this->url('forgot'); ?>"><?php echo $this->text('Forgot password'); ?></a>
      </div>
    </form>
  </div>
</div>