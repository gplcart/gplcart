<form method="post" enctype="multipart/form-data" id="upload-module" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('file', ' has-error'); ?>">
        <div class="col-md-4">
          <input type="file" accept=".zip" name="file" class="form-control">
          <div class="help-block">
            <?php echo $this->error('file'); ?>
            <div class="text-muted"><?php echo $this->text('Select a zip file containing module files'); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <button class="btn btn-default" name="install" value="1"><?php echo $this->text('Install'); ?></button>
    </div>
  </div>
</form>