<form method="post" id="export-csv" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-1 control-label">
        <?php echo $this->text('Store'); ?>
        </label>
        <div class="col-md-4">
          <select class="form-control" name="settings[options][store_id]">
            <?php foreach ($stores as $store_id => $store_name) { ?>
            <option value="<?php echo $store_id; ?>"><?php echo $this->escape($store_name); ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-11 col-md-offset-1">
          <button class="btn btn-default" name="export" value="1"><?php echo $this->text('Export'); ?></button>
        </div>
      </div>
    </div>
  </div>
</form>
<?php if (!empty($job)) { ?>
<?php echo $job; ?>
<?php } ?>
