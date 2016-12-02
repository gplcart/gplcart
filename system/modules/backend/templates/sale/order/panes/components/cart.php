<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (empty($order['cart'])) { ?>
<tr><td colspan="2"><?php echo $this->text('No products in the cart'); ?></td></tr>
<?php } else { ?>
<?php foreach ($order['cart'] as $sku => $item) { ?>
<tr>
  <td>
    <a href="<?php echo $this->url("product/{$item['product_id']}"); ?>">
      <?php echo $this->escape($item['title']); ?>
    </a>
    X <?php echo $this->escape($item['quantity']); ?>
  </td>
  <td>
    <?php echo $this->escape($item['price_formatted']); ?>
  </td>
</tr>
<?php } ?>
<?php } ?>
