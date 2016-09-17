<div class="panel panel-default">
  <div class="panel-body">
    <form method="post" id="login" class="login form-horizontal">
      <?php echo $honeypot; ?>
      <input type="hidden" name="token" value="<?php echo $token; ?>">
      <div class="form-group<?php echo isset($this->errors['email']) ? ' has-error' : ''; ?>">
        <label class="control-label col-md-2"><?php echo $this->text('E-mail'); ?></label>
        <div class="col-md-4">
          <input class="form-control" name="user[email]" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>" autofocus>
          <?php if (isset($this->errors['email'])) { ?>
          <div class="help-block"><?php echo $this->errors['email']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['password']) ? ' has-error' : ''; ?>">
        <label class="control-label col-md-2"><?php echo $this->text('Password'); ?></label>
        <div class="col-md-4">
          <input class="form-control" type="password" name="user[password]">
          <?php if (isset($this->errors['password'])) { ?>
          <div class="help-block"><?php echo $this->errors['password']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2 text-right">
          <button class="btn btn-default" name="login" value="1"><?php echo $this->text('Log in'); ?></button>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2">
          <ul class="list-inline">
            <li><a href="<?php echo $this->url('register'); ?>"><?php echo $this->text('Register account'); ?></a></li>
            <li><a href="<?php echo $this->url('forgot'); ?>"><?php echo $this->text('Forgot password'); ?></a></li>
          </ul>
        </div>
      </div>
    </form>
  </div>
</div>