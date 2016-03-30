<?php if (empty($products)) { ?>
<tr><td colspan="2"><?php echo $this->text('No products in the cart'); ?></td></tr>
<?php } else { ?>
<tr class="active"><td colspan="2"><?php echo $this->text('Cart'); ?></td></tr>
<?php foreach ($products as $product) { ?>
<tr>
  <td>
    <?php if (isset($product['product_id'])) { ?>
    <a href="<?php echo $this->url("product/{$product['product_id']}"); ?>">
      <?php echo $this->escape($product['title']); ?>
    </a>
    X <?php echo $this->escape($product['cart']['quantity']); ?>
    <?php } else { ?>
    <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
    <?php } ?>
  </td>
  <td>
    <?php echo $this->escape($product['cart']['price_formatted']); ?>
  </td>
</tr>
<?php } ?>
<?php } ?>
