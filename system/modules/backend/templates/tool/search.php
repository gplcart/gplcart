<form method="post" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Index'); ?>
        </label>
        <div class="col-md-10">
          <?php foreach ($handlers as $key => $handler) { ?>
          <button class="btn btn-default" name="index" value="<?php echo $key; ?>">
          <?php echo $this->escape($handler['name']); ?>
          </button>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</form>
<?php if ($job) { ?>
<?php echo $job; ?>
<?php } ?>