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
<div class="form-group<?php echo $this->error(null, ' has-error'); ?>">
  <div class="col-md-12">
    <div class="product-picker-results">
      <?php if (!empty($products)) { ?>
      <?php foreach ($products as $product) { ?>
      <div class="selected-item">
        <?php if(empty($multiple)) { ?>
        <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $product['sku']; ?>">
        <?php } else { ?>
        <input type="hidden" name="<?php echo $name; ?>[]" value="<?php echo $product['sku']; ?>">
        <?php } ?>
        <?php echo $product['rendered']; ?>
      </div>
      <?php } ?>
      <?php } ?>
    </div>
    <input class="form-control product-picker"
           placeholder="<?php echo $this->text('Start to type product title or SKU'); ?>"
           data-name="<?php echo $name; ?>"
           data-multiple="<?php echo empty($multiple) ? 'false' : 'true'; ?>"
           data-store-id="<?php echo empty($store_id) ? '' : $store_id; ?>">
    <div class="help-block"><?php echo $this->format($this->error()); ?></div>
  </div>
</div>

