<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($products) && count($products) > 1) { ?>
<?php if (!empty($field_labels)) { ?>
<div class="row">
  <div class="col-md-12">
    <label>
      <input type="checkbox" id="compare-difference"> <?php echo $this->text('Show only difference'); ?>
    </label>
  </div>
</div>
<?php } ?>
<div class="row">
  <div class="col-md-12">
    <table class="table compare-products">
      <tr>
        <td></td>
        <?php foreach ($products as $product_id => $product) { ?>
        <td><div class="row products"><?php echo $product['rendered']; ?></div></td>
        <?php } ?>
      </tr>
      <?php if (!empty($field_labels['attribute'])) { ?>
      <?php foreach ($field_labels['attribute'] as $field_id => $field_title) { ?>
      <tr class="togglable">
        <th class="active" scope="row"><?php echo $this->e($field_title); ?></th>
        <?php foreach ($products as $product_id => $product) { ?>
        <?php if (empty($product['field_value_labels']['attribute'][$field_id])) { ?>
        <td class="value"></td>
        <?php } else { ?>
        <td class="value"><?php echo $this->e(implode(', ', $product['field_value_labels']['attribute'][$field_id])); ?></td>
        <?php } ?>
        <?php } ?>
      </tr>
      <?php } ?>
      <?php } ?>
      <?php if (!empty($field_labels['option'])) { ?>
      <?php foreach ($field_labels['option'] as $field_id => $field_title) { ?>
      <tr class="togglable">
        <th class="active" scope="row"><?php echo $this->e($field_title); ?></th>
        <?php foreach ($products as $product_id => $product) { ?>
        <?php if (empty($product['field_value_labels']['option'][$field_id])) { ?>
        <td class="value"></td>
        <?php } else { ?>
        <td class="value"><?php echo $this->e(implode(', ', $product['field_value_labels']['option'][$field_id])); ?></td>
        <?php } ?>
        <?php } ?>
      </tr>
      <?php } ?>
      <?php } ?>
    </table>
  </div>
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12"><?php echo $this->text('Nothing to compare'); ?></div>
</div>
<?php } ?>