<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" class="add-to-cart">
  <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
  <input type="hidden" name="product[product_id]" value="<?php echo $this->e($product['product_id']); ?>">
  <?php if (!empty($field_data['option'])) { ?>
  <?php foreach ($field_data['option'] as $field_id => $field) { ?>
  <?php if (!empty($product['field']['option'][$field_id])) { ?>
  <div class="form-group field-widget-<?php echo $this->e($field['widget']); ?> field-id-<?php echo $this->e($field_id); ?>">
    <label class="title"><?php echo $this->e($field['title']); ?></label>
    <?php if ($field['widget'] === 'button') { ?>
    <?php foreach ($product['field']['option'][$field_id] as $field_value_id) { ?>
    <?php if (isset($field['values'][$field_value_id])) { ?>
    <label class="option-combination field-widget-button" title="<?php echo $this->e($field['values'][$field_value_id]['title']); ?>">
      <input class="option" data-field-id="<?php echo $this->e($field_id); ?>" data-field-title="<?php echo $this->e($field['values'][$field_value_id]['title']); ?>" data-field-value-id="<?php echo $this->e($field_value_id); ?>" type="checkbox" name="product[options][<?php echo $this->e($field_id); ?>]" value="<?php echo $this->e($field_value_id); ?>"<?php echo in_array($field_value_id, $product['default_field_values']) ? ' checked' : ''; ?>>
      <?php if (!empty($field['values'][$field_value_id]['thumb'])) { ?>
      <span class="btn btn-default image has-value" title="<?php echo $this->e($field['values'][$field_value_id]['title']); ?>" style="background-image: url(<?php echo $this->e($field['values'][$field_value_id]['thumb']); ?>);"></span>
      <?php } else if (!empty($field['values'][$field_value_id]['color'])) { ?>
      <span class="btn btn-default has-value" title="<?php echo $this->e($field['values'][$field_value_id]['title']); ?>" style="background-color:<?php echo $this->e($field['values'][$field_value_id]['color']); ?>;"></span>
      <?php } else { ?>
      <span class="btn btn-default"><?php echo $this->e($field['values'][$field_value_id]['title']); ?></span>
      <?php } ?>
    </label>
    <?php } ?>
    <?php } ?>
    <?php } else if ($field['widget'] === 'radio') { ?>
    <div class="radio">
      <label class="option-combination field-widget-radio" title="<?php echo $this->text('Any'); ?>">
        <input class="option" data-field-id="<?php echo $this->e($field_id); ?>" name="product[options][<?php echo $this->e($field_id); ?>]" type="radio" value="">
        <?php echo $this->text('Any'); ?>
      </label>
    </div>
    <?php foreach ($product['field']['option'][$field_id] as $field_value_id) { ?>
    <?php if (isset($field['values'][$field_value_id])) { ?>
    <div class="radio">
      <label class="option-combination field-widget-radio" title="<?php echo $this->e($field['values'][$field_value_id]['title']); ?>">
        <input class="option" data-field-id="<?php echo $this->e($field_id); ?>" data-field-title="<?php echo $this->e($field['values'][$field_value_id]['title']); ?>" data-field-value-id="<?php echo $this->e($field_value_id); ?>" id="option-<?php echo $this->e($field_value_id); ?>" type="radio" name="product[options][<?php echo $this->e($field_id); ?>]" value="<?php echo $this->e($field_value_id); ?>"<?php echo in_array($field_value_id, $product['default_field_values']) ? ' checked' : ''; ?>>
        <?php echo $this->e($field['values'][$field_value_id]['title']); ?>
      </label>
    </div>
    <?php } ?>
    <?php } ?>
    <?php } else if ($field['widget'] === 'select') { ?>
    <select class="form-control option-combination" data-field-id="<?php echo $this->e($field_id); ?>" name="product[options][<?php echo $this->e($field_id); ?>]">
      <option data-field-id="<?php echo $this->e($field_id); ?>" value=""><?php echo $this->text('Any'); ?></option>
      <?php foreach ($product['field']['option'][$field_id] as $field_value_id) { ?>
      <?php if (isset($field['values'][$field_value_id])) { ?>
      <option data-field-id="<?php echo $this->e($field_id); ?>" data-field-title="<?php echo $this->e($field['values'][$field_value_id]['title']); ?>" data-field-value-id="<?php echo $this->e($field_value_id); ?>" value="<?php echo $this->e($field_value_id); ?>"<?php echo in_array($field_value_id, $product['default_field_values']) ? ' selected' : ''; ?>>
        <?php echo $this->e($field['values'][$field_value_id]['title']); ?>
      </option>
      <?php } ?>
      <?php } ?>
    </select>
    <?php } ?>
  </div>
  <?php } ?>
  <?php } ?>
  <?php } ?>
  <p class="selected-combination"></p>
  <button name="add_to_cart" value="1" data-ajax="true" class="btn btn-success btn-block add-to-cart"<?php echo $product['selected_combination']['cart_access'] ? '' : ' disabled'; ?>>
    <?php echo $this->text('Add to cart'); ?>
  </button>
  <div class="message"><?php echo $product['selected_combination']['message']; ?></div>
</form>