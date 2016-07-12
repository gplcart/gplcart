<?php if ($job) { ?>
<?php echo $job; ?>
<?php } else { ?>
<form method="post" id="demo" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="form-group">
    <div class="col-md-10">
      <button class="btn btn-primary" name="install" value="1">
        <?php echo $this->text('Install'); ?>
      </button>
    </div>
  </div>
</form>
<?php } ?>