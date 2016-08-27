<div class="row">
  <form method="post" id="register" class="register col-md-6">
    <?php echo $honeypot; ?>
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <div class="form-group<?php echo isset($this->errors['name']) ? ' has-error' : ''; ?>">
      <label><?php echo $this->text('Name'); ?></label>
      <input class="form-control" maxlength="255" name="user[name]" value="<?php echo isset($user['name']) ? $user['name'] : ''; ?>">
      <?php if (isset($this->errors['name'])) { ?>
      <div class="help-block"><?php echo $this->errors['name']; ?></div>
      <?php } ?>
    </div>
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
      <div class="help-block">
      <?php if(!empty($password_limit['min'])) { ?>
      <?php echo $this->text('Minimum length: %min characters', array('%min' => $password_limit['min'])); ?>
      <?php } ?>
      <?php if(!empty($password_limit['max'])) { ?>
      <?php echo $this->text('Maximum length: %max characters', array('%max' => $password_limit['max'])); ?>
      <?php } ?>
      <?php if (isset($this->errors['password'])) { ?> 
        <p><?php echo $this->errors['password']; ?></p>
      <?php } ?>
      </div>
    </div>
    <div class="form-group">
      <button class="btn btn-primary btn-block" name="register" value="1">
      <?php echo $this->text('Register'); ?>
      </button>       
    </div>
    <div class="form-group">
      <a href="<?php echo $this->url('login'); ?>"><?php echo $this->text('Login'); ?></a>
    </div>
  </form>
</div>