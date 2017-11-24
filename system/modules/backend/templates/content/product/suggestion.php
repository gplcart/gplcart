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
<div class="media autocomplete-suggestion">
  <div class="media-left">
    <img class="media-object" src="<?php echo $this->e($item['thumb']); ?>">
  </div>
  <div class="media-body small">
    <div class="title">
      <?php echo $this->e($item['title']); ?>
    </div>
    <div class="id">
      <?php echo $this->text('ID'); ?>: <?php echo $this->e($item['product_id']); ?>
    </div>
    <div class="price">
      <?php echo $this->text('Price'); ?>: <?php echo $this->e($item['price_formatted']); ?>
    </div>
    <div class="sku">
      <?php echo $this->text('SKU'); ?>: <?php echo $this->e($item['sku']); ?>
    </div>
    <div class="status">
      <?php echo $this->text('Status'); ?>: <?php echo empty($item['status']) ? $this->text('Disabled') : $this->text('Enabled'); ?>
    </div>
  </div>
</div>
