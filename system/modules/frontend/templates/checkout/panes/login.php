<div class="form-group">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-body">
        <?php if ($this->error('login', true)) { ?>
        <div class="alert alert-danger alert-dismissible clearfix">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <?php echo $this->error('login'); ?>
        </div>
        <?php } ?>
        <div class="form-inline clearfix">
          <div class="form-group col-md-4<?php echo $this->error('email', ' has-error'); ?>">
            <label class="col-md-3 control-label"><?php echo $this->text('E-mail'); ?></label>
            <div class="col-md-9">
              <input maxlength="255" class="form-control" name="email" value="<?php echo isset($email) ? $email : ''; ?>" autofocus>
              <?php if ($this->error('email', true)) { ?>
                  <div class="help-block"><?php echo $this->error('email'); ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group col-md-4">
            <label class="col-md-3 control-label"><?php echo $this->text('Password'); ?></label>
            <div class="col-md-9">
              <input type="password" maxlength="32" class="form-control" name="password" value="">
            </div>
          </div>
          <div class="form-group col-md-4">
            <div class="col-md-6">
              <button class="btn btn-primary" name="login" value="1"><?php echo $this->text('Log in'); ?></button>
            </div>
            <div class="col-md-6">
              <button class="btn btn-default" name="checkout_anonymous" value="1">
                <i class="fa fa-user-secret"></i> <?php echo $this->text('Anonymous checkout'); ?>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>