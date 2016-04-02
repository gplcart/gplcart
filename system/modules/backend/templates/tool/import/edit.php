<?php if ($job) {
    ?>
<?php echo $job;
    ?>
<?php 
} else {
    ?>
<form method="post" enctype="multipart/form-data" id="import-csv" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $this->token;
    ?>">
  <?php if (!empty($operation['description'])) {
    ?>
  <div class="form-group">
    <div class="col-md-12"><?php echo $this->xss($operation['description']);
    ?></div>
  </div>
  <?php 
}
    ?>
  <div class="form-group<?php echo isset($form_errors['file']) ? ' has-error' : '';
    ?>">
    <label class="col-md-1 control-label"><?php echo $this->text('CSV file');
    ?></label>
    <div class="col-md-4">
      <input type="file" class="form-control" name="file" accept=".csv" required>
      <?php if (isset($form_errors['file'])) {
    ?>
      <div class="help-block"><?php echo $form_errors['file'];
    ?></div>
      <?php 
}
    ?>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-1 control-label"><?php echo $this->text('Limit');
    ?></label>
    <div class="col-md-1">
      <input type="number" name="limit" class="form-control" value="<?php echo $limit;
    ?>">
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-4 col-md-offset-1">
      <div class="checkbox">
        <label>
          <input name="unique" type="checkbox" autocomplete="off" value="1" checked> <?php echo $this->text('Check uniqueness');
    ?>
        </label>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-4 col-md-offset-1">
    <button class="btn btn-primary import" name="import" value="1">
      <i class="fa fa-upload"></i> <?php echo $this->text('Import');
    ?>
    </button>
    <?php if (!empty($operation['csv']['template'])) {
    ?>
      <span class="btn-group"><?php echo $this->text('<a href="!url">Download template</a>', array(
          '!url' => $this->url(false, array('download_template' => 1))));
    ?></span>
    <?php 
}
    ?>
    </div>
  </div>
</form>
<?php 
} ?>