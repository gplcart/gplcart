<form method="post" id="edit-product-class" onsubmit="return confirm();" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">      
      <div class="form-group">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Disabled product classes will not be available for editors'); ?>">
            <?php echo $this->text('Status'); ?>
          </span>
        </label>
        <div class="col-md-4">
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
      <div class="form-group required<?php echo isset($this->errors['title']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Name of the product class for editors'); ?>">
            <?php echo $this->text('Name'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input name="product_class[title]" maxlength="255" class="form-control" value="<?php echo isset($product_class['title']) ? $this->escape($product_class['title']) : ''; ?>">
          <?php if (isset($this->errors['title'])) { ?>
          <div class="help-block"><?php echo $this->errors['title']; ?></div>
          <?php } ?>
        </div>
      </div>  
    </div> 
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
        <?php if (isset($product_class['product_class_id']) && $this->access('product_class_delete')) { ?>
        <button class="btn btn-danger delete" name="delete" value="1">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        </div>
        <div class="col-md-4">
      <div class="btn-toolbar">
        <a class="btn btn-default" href="<?php echo $this->url('admin/content/product-class'); ?>"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('product_class_edit') || $this->access('product_class_add')) { ?>
        <button class="btn btn-default" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
        </div>
      </div>
    </div>
  </div>
</form>