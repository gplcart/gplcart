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
<?php if (count($products) > 1) { ?>
<div class="row">
  <?php if (!empty($attribute_fields) || !empty($option_fields)) { ?>
  <div class="col-md-12 text-right">
    <label>
      <input type="checkbox" id="compare-difference"> <?php echo $this->text('Show only difference'); ?>
    </label>
  </div>
  <?php } ?>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="table-responsive">
      <table class="table compare products">
        <tr>
          <td></td>
          <?php foreach ($products as $product_id => $product) { ?>
          <td><div class="row products"><?php echo $product['rendered']; ?></div></td>
          <?php } ?>
        </tr>
        <?php if (!empty($attribute_fields)) { ?>
        <?php foreach ($attribute_fields as $attribute_field_id => $attribute_field_title) { ?>
        <tr class="togglable">
          <td class="active"><?php echo $this->escape($attribute_field_title); ?></td>
          <?php foreach ($products as $product_id => $product) { ?>
          <?php if (!empty($product['attribute_values'][$attribute_field_id])) { ?>
          <td class="value"><?php echo $this->escape($product['attribute_values'][$attribute_field_id]); ?></td>
          <?php } else { ?>
          <td class="value"></td>
          <?php } ?>
          <?php } ?>
        </tr>
        <?php } ?>
        <?php } ?>
        <?php if (!empty($option_fields)) { ?>
        <?php foreach ($option_fields as $option_field_id => $option_field_title) { ?>
        <tr class="togglable">
        <td class="active"><?php echo $this->escape($option_field_title); ?></td>
          <?php foreach ($products as $product_id => $product) { ?>
          <?php if (!empty($product['option_values'][$option_field_id])) { ?>
          <td class="value"><?php echo $this->escape(implode(',', $product['option_values'][$option_field_id])); ?></td>
          <?php } else { ?>
          <td class="value"></td>
          <?php } ?>
          <?php } ?>
        </tr>
        <?php } ?>
        <?php } ?>
      </table>
    </div>
  </div>
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12"><?php echo $this->text('Nothing to compare'); ?></div>
</div>
<?php } ?>