<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if (!empty($fields)) { ?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-group required<?php echo $this->error('values', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Field'); ?></label>
    <div class="col-md-4">
      <select data-live-search="true" class="form-control selectpicker" name="field[values][]" multiple>
        <?php foreach ($fields as $field_id => $field_title) { ?>
        <option value="<?php echo $field_id; ?>"><?php echo $this->e($field_title); ?></option>
        <?php } ?>
      </select>
      <div class="help-block">
        <?php echo $this->error('values'); ?>
        <div class="text-muted">
          <?php echo $this->text('Required. Assign one or more fields to the product class'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-4 col-md-offset-2">
      <div class="btn-toolbar">
        <a class="btn btn-default" href="<?php echo $this->url("admin/content/product-class/field/{$product_class['product_class_id']}"); ?>">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <button class="btn btn-default" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
      </div>
    </div>
  </div>
</form>
<?php } else { ?>
<?php echo $this->text('No fields to add to %name', array('%name' => $product_class['title'])); ?>&nbsp;
<?php if ($this->access('field_add')) { ?>
<?php echo $this->text('You can add one or more fields <a href="@href">here</a>', array('@href' => $this->url('admin/content/field/add'))); ?>
<?php } ?>
<?php } ?>