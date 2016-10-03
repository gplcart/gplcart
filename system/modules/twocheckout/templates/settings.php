<form method="post" id="edit-module-settings" class="form-horizontal twocheckout" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('API access'); ?></div>
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Account ID'); ?>
        </label>
        <div class="col-md-3">
          <input name="settings[account]" class="form-control" value="<?php echo $this->escape($settings['account']); ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Secret'); ?>
        </label>
        <div class="col-md-3">
          <input name="settings[secret]" class="form-control" value="<?php echo $this->escape($settings['secret']); ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Demo mode'); ?>
        </label>
        <div class="col-md-1">
          <select class="form-control" name="settings[demo]">
            <option value="1"<?php echo empty($settings['demo']) ? '' : ' selected'; ?>><?php echo $this->text('Yes'); ?></option>
            <option value="0"<?php echo empty($settings['demo']) ? ' selected' : ''; ?>><?php echo $this->text('No'); ?></option>
          </select>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <a href="<?php echo $this->url('admin/module/list'); ?>" class="btn btn-default cancel">
            <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
          </a>
        </div>
        <div class="col-md-10">
          <button class="btn btn-default save" name="save" value="1">
            <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>