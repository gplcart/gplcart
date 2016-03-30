<form method="post" id="edit-product-class" onsubmit="return confirm();" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6 col-md-offset-6 text-right">
      <div class="btn-toolbar">
        <?php if (isset($product_class['product_class_id']) && $this->access('product_class_delete')) { ?>
        <button class="btn btn-danger delete" name="delete" value="1">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a class="btn btn-default" href="<?php echo $this->url('admin/content/product/class'); ?>"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('product_class_edit') || $this->access('product_class_add')) { ?>
        <button class="btn btn-primary" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="row margin-top-20">
    <div class="col-md-12">
      <div class="form-group">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Disabled product classes will not be available for editors'); ?>">
          <?php echo $this->text('Status'); ?>
          </span>
        </label>
        <div class="col-md-6">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo!empty($product_class['status']) ? ' active' : ''; ?>">
              <input name="product_class[status]" type="radio" autocomplete="off" value="1"<?php echo!empty($product_class['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($product_class['status']) ? ' active' : ''; ?>">
              <input name="product_class[status]" type="radio" autocomplete="off" value="0"<?php echo empty($product_class['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo isset($form_errors['title']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Name of the product class for editors'); ?>">
          <?php echo $this->text('Name'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input name="product_class[title]" maxlength="255" class="form-control" value="<?php echo isset($product_class['title']) ? $this->escape($product_class['title']) : ''; ?>" required>
          <?php if (isset($form_errors['title'])) { ?>
          <div class="help-block"><?php echo $form_errors['title']; ?></div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</form>