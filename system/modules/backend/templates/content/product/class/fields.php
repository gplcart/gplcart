<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($fields)) { ?>
<form method="post" id="product-class-fields" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <div class="btn-toolbar pull-right">
        <button class="btn btn-default" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <a class="btn btn-default" href="<?php echo $this->url("admin/content/product-class/field/{$product_class['product_class_id']}/add"); ?>">
          <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
        </a>
      </div>
    </div>
    <div class="panel-body table-responsive">
      <table class="table fields" data-sortable-input-weight="true">
        <thead>
          <tr>
            <th><?php echo $this->text('Name'); ?></th>
            <th><?php echo $this->text('Required'); ?></th>
            <th><?php echo $this->text('Multiple'); ?></th>
            <th><?php echo $this->text('Weight'); ?></th>
            <th><?php echo $this->text('Remove'); ?></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($fields as $field_id => $field) { ?>
          <tr>
            <td class="middle">
              <?php echo $this->escape($field['title']); ?>
              <span class="text-muted">
              <?php if($field['type'] == 'option') { ?>
              (<?php echo $this->text('Option'); ?>)
              <?php } else { ?>
              (<?php echo $this->text('Attribute'); ?>)
              <?php } ?>
              </span>
              <input type="hidden" name="fields[<?php echo $field_id; ?>][weight]" value="<?php echo $field['weight']; ?>">
            </td>
            <td class="middle">
              <input type="checkbox" name="fields[<?php echo $field_id; ?>][required]" value="1"<?php echo $field['required'] ? ' checked' : ''; ?>>
            </td>
            <td class="middle">
              <input type="checkbox" name="fields[<?php echo $field_id; ?>][multiple]" value="1"<?php echo $field['multiple'] ? ' checked' : ''; ?>>
            </td>
            <td class="middle">
              <i class="fa fa-arrows handle"></i> <span class="weight"><?php echo $this->escape($field['weight']); ?></span>
            </td>
            <td class="middle">
              <input type="checkbox" name="fields[<?php echo $field_id; ?>][remove]" value="1">
            </td>
              <td class="middle">
                <?php if($this->access('field_edit')) { ?>
                <a href="<?php echo $this->url("admin/content/field/edit/$field_id"); ?>">
                  <?php echo mb_strtolower($this->text('Edit')); ?>
                </a>
                <?php } ?>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</form>
<?php } else { ?>
<?php echo $this->text('This product class has no fields'); ?>
<a class="btn btn-default" href="<?php echo $this->url("admin/content/product-class/field/{$product_class['product_class_id']}/add"); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>