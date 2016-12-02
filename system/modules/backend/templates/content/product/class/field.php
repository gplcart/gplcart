<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($fields)) { ?>
<form method="post" id="add-field" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="panel panel-default">
    <div class="panel-body">  
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Field'); ?></label>
        <div class="col-md-4">
          <select data-live-search="true" class="form-control selectpicker" name="fields[]" multiple required>
            <?php foreach ($fields as $field_id => $field_title) { ?>
            <option value="<?php echo $field_id; ?>"><?php echo $this->escape($field_title); ?></option>
            <?php } ?>
          </select>
          <div class="text-muted">
            <?php echo $this->text('Required. Select one or more fields to the product class'); ?>
          </div>
        </div>
      </div>  
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-4 col-md-offset-2">
          <div class="btn-toolbar">
            <a class="btn btn-default" href="<?php echo $this->url("admin/content/product-class/field/{$product_class['product_class_id']}"); ?>">
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
<?php echo $this->text('No fields to add to %class.', array('%class' => $product_class['title'])); ?>
<?php if($this->access('field_add')) { ?>
<?php echo $this->text('You can add one or more fields <a href="@href">here</a>', array('@href' => $this->url('admin/content/field/add'))); ?>
<?php } ?>
<?php } ?>