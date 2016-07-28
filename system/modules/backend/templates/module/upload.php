<form method="post" enctype="multipart/form-data" id="upload-module" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="form-group<?php echo isset($this->errors['file']) ? ' has-error' : ''; ?>">
    <div class="col-md-4">
      <input type="file" accept=".zip" name="file" class="form-control" required>
      <div class="help-block">
       <?php echo $this->text('Select a zip file containing module files'); ?>
        <?php if (isset($this->errors['file'])) { ?>
          <p><?php echo $this->errors['file']; ?></p>
        <?php } ?>
      </div>
    </div>
  </div>
  <button class="btn btn-primary" name="install" value="1"><?php echo $this->text('Install'); ?></button>
</form>