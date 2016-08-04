<?php if (!empty($fields)) { ?>
<form method="post" id="add-field" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-8">
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Fields'); ?></div>
        <div class="panel-body">  
          <div class="form-group">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Select one or more fields to be added to the product class'); ?>">
                <?php echo $this->text('Field'); ?>
              </span>
            </label>
            <div class="col-md-10">
              <select data-live-search="true" class="form-control selectpicker" name="fields[]" multiple required>
                <?php foreach ($fields as $field_id => $field_title) { ?>
                <option value="<?php echo $field_id; ?>"><?php echo $this->escape($field_title); ?></option>
                <?php } ?>
              </select>
            </div>
          </div>  
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="btn-toolbar">
            <a class="btn btn-default" href="<?php echo $this->url("admin/content/product/class/field/{$product_class['product_class_id']}"); ?>">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <button class="btn btn-default" name="save" value="1">
              <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
            </button>
          </div>
        </div>
      </div>  
    </div> 
  </div>
</form>
<?php } else { ?>
<?php echo $this->text('Nothing to add to %class', array('%class' => $product_class['title'])); ?>
<?php } ?>