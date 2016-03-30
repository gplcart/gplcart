<form method="post" id="reset" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="form-group">
    <div class="col-md-12">
    <?php echo $this->text('By submitting this form you <b>PERMANENTLY DELETE ALL DATA</b> that has been added/modified since the system installed. It cannot be undone. Make sure you have a valid database backup!'); ?>
    </div>
  </div>
  <div class="form-group required<?php echo isset($form_errors['confirmation']) ? ' has-error' : ''; ?>">
    <div class="col-md-2">
      <input name="confirmation" class="form-control" autocomplete="off" placeholder="<?php echo $this->text('Type DELETE ALL'); ?>">
      <?php if (isset($form_errors['confirmation'])) { ?>
      <div class="help-block">
      <?php echo $form_errors['confirmation']; ?>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-10">
      <button class="btn btn-danger" name="reset" value="1">
        <i class="fa fa-exclamation-triangle"></i> <?php echo $this->text('Reset system'); ?>
      </button>
    </div>
  </div>
</form>