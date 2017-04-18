<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<span class="media suggestion small" data-url="<?php echo $this->url('product/' . $product['product_id']); ?>">
  <span class="media-left">
    <img class="media-object" src="<?php echo $this->e($product['thumb']); ?>">
  </span>
  <span class="media-body">
    <span class="media-heading title">
      <?php echo $this->e($product['title']); ?>
    </span>
    <span class="price">
      <?php echo $this->e($product['price_formatted']); ?>
    </span>
  </span>
</span>