<div class="panel panel-default">
  <div class="panel-body">
    <form method="post" id="forgot" class="forgot form-horizontal">
      <?php echo $honeypot; ?>
      <input type="hidden" name="token" value="<?php echo $token; ?>">
      <?php if (empty($forgetful_user)) { ?>
      <div class="form-group<?php echo $this->error('email', ' has-error'); ?>">
        <label class="control-label col-md-2"><?php echo $this->text('E-mail'); ?></label>
        <div class="col-md-4">
          <input class="form-control" name="user[email]" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>" autofocus>
          <div class="help-block"><?php echo $this->error('email'); ?></div>
        </div>
      </div>
      <?php } else { ?>
      <div class="form-group">
        <?php echo $this->text('Hello %name. Please type your new password', array('%name' => $forgetful_user['name'])); ?>
      </div>
      <div class="form-group<?php echo $this->error('password', ' has-error'); ?>">
        <label class="control-label col-md-2"><?php echo $this->text('Password'); ?></label>
        <div class="col-md-4">
          <input class="form-control" type="password" name="user[password]" autocomplete="new-password" autofocus>
          <div class="help-block"><?php echo $this->error('password'); ?></div>
        </div>
      </div>
      <?php } ?>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2 text-right">
          <button class="btn btn-default" name="reset" value="1">
            <?php if ($forgetful_user) { ?>
            <?php echo $this->text('Change password'); ?>
            <?php } else { ?>
            <?php echo $this->text('Reset password'); ?>
            <?php } ?>
          </button>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2">
          <ul class="list-inline">
            <li><a href="<?php echo $this->url('login'); ?>"><?php echo $this->text('Login'); ?></a></li>
            <li><a href="<?php echo $this->url('register'); ?>"><?php echo $this->text('Register account'); ?></a></li>
          </ul>
        </div>
      </div>
    </form>
  </div>
</div>