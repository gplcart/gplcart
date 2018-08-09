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
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-group row">
    <div class="col-md-4">
      <div class="btn-group btn-group-toggle" data-toggle="buttons">
        <label class="btn btn-outline-secondary<?php echo empty($product_class['status']) ? '' : ' active'; ?>">
          <input name="product_class[status]" type="radio" autocomplete="off" value="1"<?php echo empty($product_class['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-outline-secondary<?php echo empty($product_class['status']) ? ' active' : ''; ?>">
          <input name="product_class[status]" type="radio" autocomplete="off" value="0"<?php echo empty($product_class['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <div class="form-text">
        <div class="description">
            <?php echo $this->text('Disabled product classes will not be available to customers and administrators while editing products'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group row required<?php echo $this->error('title', ' has-error'); ?>">
    <div class="col-md-4">
      <label><?php echo $this->text('Title'); ?></label>
      <input name="product_class[title]" maxlength="255" class="form-control" value="<?php echo isset($product_class['title']) ? $this->e($product_class['title']) : ''; ?>">
      <div class="form-text">
        <?php echo $this->error('title'); ?>
        <div class="description">
          <?php echo $this->text('The name of product class for administrators'); ?>
        </div>
      </div>
    </div>
  </div>
      <div class="btn-toolbar">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a class="btn cancel" href="<?php echo $this->url('admin/content/product-class'); ?>"><?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('product_class_edit') || $this->access('product_class_add')) { ?>
        <button class="btn btn-success save" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
</form>