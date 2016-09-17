<div class="panel panel-body">
  <div class="panel-body">
    <form method="post" id="register" class="register form-horizontal">
      <?php echo $honeypot; ?>
      <input type="hidden" name="token" value="<?php echo $token; ?>">
      <div class="form-group<?php echo isset($this->errors['name']) ? ' has-error' : ''; ?>">
        <label class="control-label col-md-2"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-4">
          <input class="form-control" maxlength="255" name="user[name]" value="<?php echo isset($user['name']) ? $user['name'] : ''; ?>">
          <?php if (isset($this->errors['name'])) { ?>
          <div class="help-block"><?php echo $this->errors['name']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['email']) ? ' has-error' : ''; ?>">
        <label class="control-label col-md-2"><?php echo $this->text('E-mail'); ?></label>
        <div class="col-md-4">
          <input type="email" class="form-control" name="user[email]" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>" autofocus>
          <?php if (isset($this->errors['email'])) { ?>
          <div class="help-block"><?php echo $this->errors['email']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['password']) ? ' has-error' : ''; ?>">
        <label class="control-label col-md-2"><?php echo $this->text('Password'); ?></label>
        <div class="col-md-4">
          <input class="form-control" type="password" name="user[password]">
          <div class="help-block">
            <?php if (!empty($password_limit['min'])) { ?>
            <?php echo $this->text('Minimum length: %min characters', array('%min' => $password_limit['min'])); ?>
            <?php } ?>
            <?php if (!empty($password_limit['max'])) { ?>
            <?php echo $this->text('Maximum length: %max characters', array('%max' => $password_limit['max'])); ?>
            <?php } ?>
            <?php if (isset($this->errors['password'])) { ?> 
            <p><?php echo $this->errors['password']; ?></p>
            <?php } ?>
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