<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div>
  <div id="attribute-form">
    <?php if (!empty($fields['attribute'])) { ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title">
        <?php echo $this->text('Attributes'); ?>
        </h4>
      </div>
      <div class="panel-body table-responsive">
        <table class="table table-condensed attribute">
          <tbody>
            <?php foreach ($fields['attribute'] as $field_id => $attribute) { ?>
            <tr>
              <td class="middle">
                <?php if (!empty($attribute['required'])) { ?>
                <span class="text-danger">*</span>
                <?php } ?>
                <?php echo $this->e($attribute['title']); ?>
              </td>
              <td>
                <div class="<?php echo $this->error("attribute.$field_id", 'has-error'); ?>">
                  <select title="<?php echo $this->text('- select -'); ?>" data-live-search="true" class="form-control selectpicker" name="product[field][attribute][<?php echo $field_id; ?>][]"<?php echo $attribute['multiple'] ? ' multiple' : ''; ?>>
                    <?php if (empty($attribute['multiple'])) { ?>
                    <option value="" selected disabled><?php echo $this->text('- select -'); ?></option>
                    <?php } ?>
                    <?php foreach ($attribute['values'] as $value) { ?>
                    <?php if (isset($product['field']['attribute'][$field_id]) && in_array($value['field_value_id'], $product['field']['attribute'][$field_id])) { ?>
                    <option value="<?php echo $value['field_value_id']; ?>" selected><?php echo $this->e($value['title']); ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $value['field_value_id']; ?>"><?php echo $this->e($value['title']); ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select>
                  <div class="help-block">
                    <?php echo $this->error("attribute.$field_id"); ?>
                  </div>
                </div>
              </td>
              <td>
                <?php if ($this->access('field_add')) { ?>
                <a target="_blank" href="<?php echo $this->url("admin/content/field/value/$field_id"); ?>" class="btn btn-default"><i class="fa fa-plus"></i></a>
                <?php } ?>
                <a href="#" class="btn btn-default refresh-fields" data-field-type="attribute"><i class="fa fa-refresh"></i></a>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php } ?>
  </div>
</div>