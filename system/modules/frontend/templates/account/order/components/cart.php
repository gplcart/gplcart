<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($order['cart'])) { ?>
<?php foreach ($order['cart'] as $sku => $item) { ?>
<tr>
  <td>
    <?php if (empty($item['product_status'])) { ?>
    <?php echo $this->e($item['title']); ?> <span class="text-danger">(<?php echo $this->text('unavailable'); ?>)</span>
    <?php } else { ?>
    <a href="<?php echo $this->url("product/{$item['product_id']}"); ?>"><?php echo $this->e($item['title']); ?></a>
    <?php } ?>
    X <?php echo $this->e($item['quantity']); ?>
  </td>
  <td><?php echo $this->e($item['price_formatted']); ?></td>
</tr>
<?php } ?>
<?php } else { ?>
<tr><td colspan="2"><?php echo $this->text('No products in the cart'); ?></td></tr>
<?php } ?>