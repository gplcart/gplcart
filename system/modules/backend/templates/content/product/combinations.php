<?php if($combinations) { ?>
<?php foreach($combinations as $combination_id => $combination) { ?>
<tr class="product-options-<?php echo $product['product_id']; ?> active" data-combination-id="<?php echo $combination_id; ?>">
  <td></td>
  <td class="middle">
    <?php foreach($fields['option'] as $field_id => $option) { ?>
    <?php foreach($option['values'] as $value) { ?>
    <?php if(!empty($combination['fields']) && in_array($value['field_value_id'], $combination['fields'])) { ?>
    <input type="hidden" name="product[combination][<?php echo $combination_id; ?>][fields][<?php echo $field_id; ?>]" value="<?php echo $value['field_value_id']; ?>">
    <?php echo $this->escape($value['title']); ?><br>
    <?php } ?>
    <?php } ?>
    <?php } ?>
  </td>
  <td class="middle">
    <?php echo $this->escape($combination['sku']); ?>
    <input type="hidden" name="product[update_combinations]" value="1">
    <input type="hidden" name="product[price]" value="<?php echo $product['price']; ?>">
    <input type="hidden" name="product[currency]" value="<?php echo $product['currency']; ?>">
    <input type="hidden" name="product[combination][<?php echo $combination_id; ?>][currency]" value="<?php echo $product['currency']; ?>">
  </td>
  <td class="middle">
    <div class="<?php echo isset($this->errors['combination'][$combination_id]['price']) ? 'has-error' : ''; ?>">
      <input class="form-control" name="product[combination][<?php echo $combination_id; ?>][price]" value="<?php echo $combination['price']; ?>"<?php echo $this->access('product_edit') ? '' : ' disabled'; ?>>
      <?php if(isset($this->errors['combination'][$combination_id]['price'])) { ?>
      <div class="help-block"><?php echo $this->errors['combination'][$combination_id]['price']; ?></div>
      <?php } ?>
    </div>
  </td>
  <td class="middle"><?php echo $this->escape($product['currency']); ?></td>
  <td class="middle">
    <div class="<?php echo isset($this->errors['combination'][$combination_id]['stock']) ? 'has-error' : ''; ?>">
      <input class="form-control" name="product[combination][<?php echo $combination_id; ?>][stock]" value="<?php echo $combination['stock']; ?>"<?php echo $this->access('product_edit') ? '' : ' disabled'; ?>>
      <?php if(isset($this->errors['combination'][$combination_id]['stock'])) { ?>
      <div class="help-block"><?php echo $this->errors['combination'][$combination_id]['stock']; ?></div>
      <?php } ?>
    </div>
  </td>
  <td></td>
  <td></td>
  <td></td>
  <td>
    <?php if($this->access('product_edit')) { ?>
    <button type="button" href="#" class="btn btn-default save-row disabled"><i class="fa fa-floppy-o"></i></button>
    <?php } ?>
    <button type="button" href="#" class="btn btn-default cancel-row"><i class="fa fa-reply"></i></button>
  </td>
</tr>
<?php } ?>
<?php } ?>