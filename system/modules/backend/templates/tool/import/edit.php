<?php if ($job) { ?>
<?php echo $job; ?>
<?php } else { ?>
<form method="post" enctype="multipart/form-data" id="import-csv" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <?php if (!empty($operation['description'])) { ?>
      <div class="form-group">
        <div class="col-md-12"><?php echo $this->xss($operation['description']); ?></div>
      </div>
      <?php } ?>
      <div class="form-group<?php echo isset($this->errors['file']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('CSV file'); ?></label>
        <div class="col-md-4">
          <input type="file" class="form-control" name="file" accept=".csv" required>
          <?php if (isset($this->errors['file'])) { ?>
          <div class="help-block"><?php echo $this->errors['file']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Limit'); ?></label>
        <div class="col-md-1">
          <input type="number" name="limit" class="form-control" value="<?php echo $limit; ?>">
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2">
          <div class="checkbox">
            <label>
              <input name="unique" type="checkbox" autocomplete="off" value="1" checked> <?php echo $this->text('Check uniqueness'); ?>
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <?php if (!empty($operation['csv']['template'])) { ?>
          <a class="btn btn-default" href="<?php echo $this->url(false, array('download_template' => 1)); ?>">
            <i class="fa fa-download"></i> <?php echo $this->text('Download template'); ?>
          </a>
          <?php } ?>   
        </div>
        <div class="col-md-10">
          <a class="btn btn-default cancel" href="<?php echo $this->url('admin/tool/import'); ?>">
            <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
          </a>
          <button class="btn btn-default import" name="import" value="1">
            <i class="fa fa-upload"></i> <?php echo $this->text('Import'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>
<?php } ?>

