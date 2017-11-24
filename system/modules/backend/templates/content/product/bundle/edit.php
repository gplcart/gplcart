<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php echo $product_picker; ?>
  <div class="btn-toolbar">
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/product'); ?>"><?php echo $this->text('Cancel'); ?></a>
    <?php if ($this->access('product_bundle_add') || $this->access('product_bundle_edit')) { ?>
    <button class="btn btn-default save" name="save" value="1">
      <?php echo $this->text('Save'); ?>
    </button>
    <?php } ?>
  </div>
</form>