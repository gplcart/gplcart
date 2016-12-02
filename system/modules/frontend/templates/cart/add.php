<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * 
 * To see available variables: <?php print_r(get_defined_vars()); ?>
 * To see the current controller object: <?php print_r($this); ?>
 * To call a controller method: <?php $this->exampleMethod(); ?>
 */
?>
<form method="post" class="add-to-cart form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <input type="hidden" name="product[product_id]" value="<?php echo $product['product_id']; ?>">
  <?php if (!empty($product['field']['option'])) { ?>
  <table class="table-condensed">
    <?php foreach ($product['field']['option'] as $field_id => $field_values) { ?>
    <?php if (isset($field_data['option'][$field_id])) { ?>
    <tr>
      <td class="middle">
        <?php echo $this->escape($field_data['option'][$field_id]['title']); ?>
      </td>
      <td>
        <div class="option-field-wrapper" id="option-field-<?php echo $field_id; ?>">
          <?php if ($field_data['option'][$field_id]['widget'] == 'image') { ?>
          <?php foreach ($field_values as $field_value_id) { ?>
          <?php if (!empty($field_data['option'][$field_id]['values'][$field_value_id]) && isset($field_data['option'][$field_id]['values'][$field_value_id]['thumb'])) { ?>
          <?php $thumb = $field_data['option'][$field_id]['values'][$field_value_id]['thumb']; ?>
          <label class="option-wrapper image" data-field-value-id="<?php echo $field_value_id; ?>" title="<?php echo $this->escape($field_data['option'][$field_id]['values'][$field_value_id]['title']); ?>">
            <input class="option" data-field-id="<?php echo $field_id; ?>" data-field-value-id="<?php echo $field_value_id; ?>" type="radio" name="product[options][<?php echo $field_id; ?>]" value="<?php echo $field_value_id; ?>">
            <img src="<?php echo $thumb; ?>">
          </label>
          <?php } ?>
          <?php } ?>
          <?php } else if ($field_data['option'][$field_id]['widget'] == 'color') { ?>
          <?php foreach ($field_values as $field_value_id) { ?>
          <?php if (!empty($field_data['option'][$field_id]['values'][$field_value_id])) { ?>
          <label class="option-wrapper color" data-field-value-id="<?php echo $field_value_id; ?>" title="<?php echo $this->escape($field_data['option'][$field_id]['values'][$field_value_id]['title']); ?>">
            <input class="option" data-field-id="<?php echo $field_id; ?>" data-field-value-id="<?php echo $field_value_id; ?>" type="radio" name="product[options][<?php echo $field_id; ?>]" value="<?php echo $field_value_id; ?>">
            <span style="background-color:<?php echo $this->escape($field_data['option'][$field_id]['values'][$field_value_id]['color']); ?>;"></span>
          </label>
          <?php } ?>
          <?php } ?>
          <?php } else if ($field_data['option'][$field_id]['widget'] == 'radio') { ?>
          <?php foreach ($field_values as $field_value_id) { ?>
          <?php if (!empty($field_data['option'][$field_id]['values'][$field_value_id])) { ?>
          <label class="option-wrapper radio" data-field-value-id="<?php echo $field_value_id; ?>" title="<?php echo $this->escape($field_data['option'][$field_id]['values'][$field_value_id]['title']); ?>">
            <?php echo $this->escape($field_data['option'][$field_id]['values'][$field_value_id]['title']); ?>
            <input class="option" data-field-id="<?php echo $field_id; ?>" data-field-value-id="<?php echo $field_value_id; ?>" id="option-<?php echo $field_value_id; ?>" type="radio" name="product[options][<?php echo $field_id; ?>]" value="<?php echo $field_value_id; ?>">
          </label>
          <?php } ?>
          <?php } ?>
          <?php } else if ($field_data['option'][$field_id]['widget'] == 'select') { ?>
          <select class="form-control" name="product[options][<?php echo $field_id; ?>]">
            <?php foreach ($field_values as $field_value_id) { ?>
            <?php if (!empty($field_data['option'][$field_id]['values'][$field_value_id])) { ?>
            <option data-field-id="<?php echo $field_id; ?>" data-field-value-id="<?php echo $field_value_id; ?>" value="<?php echo $field_value_id; ?>">
            <?php echo $this->escape($field_data['option'][$field_id]['values'][$field_value_id]['title']); ?>
            </option>
            <?php } ?>
            <?php } ?>
          </select>
          <?php } ?>
        </div>
      </td>
    </tr>
    <?php } ?>
    <?php } ?>
  </table>
  <?php } ?>
  <button name="add_to_cart" value="1" data-ajax="true" class="btn btn-success add-to-cart"<?php echo $cart_access ? '' : ' disabled'; ?>>
    <?php echo $this->text('Add to cart'); ?>
  </button>
  <div id="combination-message" style="display:none;"></div>
</form>