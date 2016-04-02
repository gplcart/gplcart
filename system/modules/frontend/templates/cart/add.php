<form method="post" class="add-cart form-horizontal" id="add-to-cart">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <?php if (!empty($product['field']['option'])) {
    ?>
  <table class="table-condensed">
    <?php foreach ($product['field']['option'] as $field_id => $field_values) {
    ?>
    <?php if (isset($field_data['option'][$field_id])) {
    ?>
    <tr>
      <td class="middle">
        <?php echo $this->escape($field_data['option'][$field_id]['title']);
    ?>
      </td>
      <td>
        <div class="option-field-wrapper" id="option-field-<?php echo $field_id;
    ?>">
          <?php if ($field_data['option'][$field_id]['widget'] == 'image') {
    ?>
          <?php foreach ($field_values as $field_value_id) {
    ?>
            <?php if (!empty($field_data['option'][$field_id]['values'][$field_value_id]) && isset($field_data['option'][$field_id]['values'][$field_value_id]['thumb'])) {
    ?>
            <?php $thumb = $field_data['option'][$field_id]['values'][$field_value_id]['thumb'];
    ?>
            <label class="option-wrapper image" data-field-value-id="<?php echo $field_value_id;
    ?>" title="<?php echo $this->escape($field_data['option'][$field_id]['values'][$field_value_id]['title']);
    ?>">
            <input class="option" data-field-id="<?php echo $field_id;
    ?>" data-field-value-id="<?php echo $field_value_id;
    ?>" type="radio" name="product[options][<?php echo $field_id;
    ?>]" value="<?php echo $field_value_id;
    ?>">
            <img src="<?php echo $thumb;
    ?>">
            </label>
            <?php 
}
    ?>
          <?php 
}
    ?>
          <?php 
} elseif ($field_data['option'][$field_id]['widget'] == 'color') {
    ?>
          <?php foreach ($field_values as $field_value_id) {
    ?>
            <?php if (!empty($field_data['option'][$field_id]['values'][$field_value_id])) {
    ?>
            <label class="option-wrapper color" data-field-value-id="<?php echo $field_value_id;
    ?>" title="<?php echo $this->escape($field_data['option'][$field_id]['values'][$field_value_id]['title']);
    ?>">
            <input class="option" data-field-id="<?php echo $field_id;
    ?>" data-field-value-id="<?php echo $field_value_id;
    ?>" type="radio" name="product[options][<?php echo $field_id;
    ?>]" value="<?php echo $field_value_id;
    ?>">
            <span style="background-color:<?php echo $this->escape($field_data['option'][$field_id]['values'][$field_value_id]['color']);
    ?>;"></span>
            </label>
            <?php 
}
    ?>
          <?php 
}
    ?>
          <?php 
} elseif ($field_data['option'][$field_id]['widget'] == 'radio') {
    ?>
          <?php foreach ($field_values as $field_value_id) {
    ?>
            <?php if (!empty($field_data['option'][$field_id]['values'][$field_value_id])) {
    ?>
            <label class="option-wrapper radio" data-field-value-id="<?php echo $field_value_id;
    ?>" title="<?php echo $this->escape($field_data['option'][$field_id]['values'][$field_value_id]['title']);
    ?>">
            <?php echo $this->escape($field_data['option'][$field_id]['values'][$field_value_id]['title']);
    ?>
            <input class="option" data-field-id="<?php echo $field_id;
    ?>" data-field-value-id="<?php echo $field_value_id;
    ?>" id="option-<?php echo $field_value_id;
    ?>" type="radio" name="product[options][<?php echo $field_id;
    ?>]" value="<?php echo $field_value_id;
    ?>">
            </label>
            <?php 
}
    ?>
          <?php 
}
    ?>
          <?php 
} elseif ($field_data['option'][$field_id]['widget'] == 'select') {
    ?>
            <select class="form-control" name="product[options][<?php echo $field_id;
    ?>]">
            <?php foreach ($field_values as $field_value_id) {
    ?>
            <?php if (!empty($field_data['option'][$field_id]['values'][$field_value_id])) {
    ?>
            <option data-field-id="<?php echo $field_id;
    ?>" data-field-value-id="<?php echo $field_value_id;
    ?>" value="<?php echo $field_value_id;
    ?>">
            <?php echo $this->escape($field_data['option'][$field_id]['values'][$field_value_id]['title']);
    ?>
            </option>
            <?php 
}
    ?>
            <?php 
}
    ?>
            </select>
          <?php 
}
    ?>
        </div>
      </td>
    </tr>
    <?php 
}
    ?>
    <?php 
}
    ?>
  </table>
  <?php 
} ?>
  <div class="row">
    <div class="col-md-6">
      <div class="input-group"<?php echo isset($form_errors['quantity']) ? ' has-error' : ''; ?>>
        <input name="product[quantity]" maxlength="2" type="number" min="1" step="1" class="form-control" value="<?php echo isset($product['quantity']) ? $product['quantity'] : 1; ?>">
        <?php if (isset($form_errors['quantity'])) {
    ?>
            <div class="help-block"><?php echo $form_errors['quantity'];
    ?></div>
        <?php 
} ?>
        <span class="input-group-btn">
          <button class="btn btn-success add-to-cart"<?php echo $cart_access ? '' : ' disabled'; ?>><?php echo $this->text('Add to cart'); ?></button>
        </span>
      </div>
    </div>
  </div>
  <input type="hidden" name="product[product_id]" value="<?php echo $product['product_id']; ?>">
</form>