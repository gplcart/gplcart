<?php if ($job) { ?>
<?php echo $job; ?>
<?php } else { ?>
<?php if ($handlers) { ?>
<form method="post" id="search" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
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
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Limit'); ?></label>
    <div class="col-md-1">
      <input type="number" min="1" step="1" name="limit" class="form-control" value="50">
    </div>
  </div>
</form>
<?php } else { ?>
<div class="row">
  <div class="col-md-12"><?php echo $this->text('Missing handler'); ?></div>
</div>
<?php } ?>
<?php } ?>