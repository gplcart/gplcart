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
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
    <div class="col-md-4">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo empty($product_class['status']) ? '' : ' active'; ?>">
          <input name="product_class[status]" type="radio" autocomplete="off" value="1"<?php echo empty($product_class['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-default<?php echo empty($product_class['status']) ? ' active' : ''; ?>">
          <input name="product_class[status]" type="radio" autocomplete="off" value="0"<?php echo empty($product_class['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <div class="text-muted">
        <?php echo $this->text('Disabled product classes will not be available to administrators'); ?>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('title', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Title'); ?></label>
    <div class="col-md-4">
      <input name="product_class[title]" maxlength="255" class="form-control" value="<?php echo isset($product_class['title']) ? $this->e($product_class['title']) : ''; ?>">
      <div class="help-block">
        <?php echo $this->error('title'); ?>
        <div class="text-muted">
          <?php echo $this->text('The name of product class for administrators'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-10 col-md-offset-2">
      <div class="btn-toolbar">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Are you sure? It cannot be undone!'));">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a class="btn btn-default" href="<?php echo $this->url('admin/content/product-class'); ?>"><?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('product_class_edit') || $this->access('product_class_add')) { ?>
        <button class="btn btn-default" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>