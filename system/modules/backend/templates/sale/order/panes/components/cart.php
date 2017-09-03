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
<?php if (empty($order['cart'])) { ?>
<tr><td colspan="2"><?php echo $this->text('No products in the cart'); ?></td></tr>
<?php } else { ?>
<tr class="active order-component-title">
  <td colspan="2"><?php echo $this->text('Cart'); ?></td>
</tr>
<?php foreach ($order['cart'] as $sku => $item) { ?>
<tr>
  <td>
    <?php if(empty($item['product_status'])) { ?>
    <?php echo $this->e($item['title']); ?> <span class="text-danger">(<?php echo $this->text('unavailable'); ?>)</span>
    <?php } else { ?>
    <a href="<?php echo $this->url("product/{$item['product_id']}"); ?>">
      <?php echo $this->e($item['title']); ?>
    </a>
    <?php } ?>
    X <?php echo $this->e($item['quantity']); ?>
  </td>
  <td>
    <?php echo $this->e($item['price_formatted']); ?>
  </td>
</tr>
<?php } ?>
<?php } ?>
