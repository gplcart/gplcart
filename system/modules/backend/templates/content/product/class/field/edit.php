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
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-group row required<?php echo $this->error('field_id', ' has-error'); ?>">
    <div class="col-md-4">
      <label><?php echo $this->text('Fields'); ?></label>
      <select class="form-control" name="product_class[field_id][]" multiple>
        <?php foreach ($fields as $field_id => $field_title) { ?>
        <option value="<?php echo $field_id; ?>"><?php echo $this->e($field_title); ?></option>
        <?php } ?>
      </select>
      <div class="form-text">
        <?php echo $this->error('field_id'); ?>
        <div class="description">
          <?php echo $this->text('Assign one or more fields to the product class.'); ?>
          <?php if ($this->access('field_add')) { ?>
          <?php echo $this->text('You can add new fields <a href="@url">here</a>', array('@url' => $this->url('admin/content/field/add', array('target' => $this->path())))); ?>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
      <div class="btn-toolbar">
        <a class="btn cancel" href="<?php echo $this->url("admin/content/product-class/field/{$product_class['product_class_id']}"); ?>">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <button class="btn btn-success save" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
      </div>
</form>
<?php } else { ?>
<?php echo $this->text('No fields to add to %name.', array('%name' => $product_class['title'])); ?>&nbsp;
<?php if ($this->access('field_add')) { ?>
<?php echo $this->text('You can add new fields <a href="@url">here</a>', array('@url' => $this->url('admin/content/field/add', array('target' => $this->path())))); ?>
<?php } ?>
<?php } ?>