<?php if ($job) { ?>
<?php echo $job; ?>
<?php } else { ?>
<form method="post" id="export-csv" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="form-group">
    <label class="col-md-1 control-label"><?php echo $this->text('Limit'); ?></label>
    <div class="col-md-1">
      <input type="number" name="export_limit" class="form-control" value="<?php echo $limit; ?>">
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-1 control-label">
      <?php echo $this->text('Store'); ?>
    </label>
    <div class="col-md-4">
      <select class="form-control" name="store_id">
        <?php foreach ($stores as $store_id => $store_name) { ?>
        <option value="<?php echo $store_id; ?>"><?php echo $this->escape($store_name); ?></option>
        <?php } ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-11 col-md-offset-1">
      <button class="btn btn-primary" name="export" value="1">
        <i class="fa fa-download"></i> <?php echo $this->text('Export'); ?>
      </button>
    </div>
  </div>
</form>
<?php } ?>
