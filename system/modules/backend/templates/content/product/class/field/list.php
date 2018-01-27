<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if (!empty($fields)) { ?>
<?php $draggable = count($fields) > 1 ;?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="btn-toolbar actions">
    <button class="btn btn-default save" name="save" value="1" onclick="return confirm('<?php echo $this->text('Are you sure?'); ?>');">
      <?php echo $this->text('Save'); ?>
    </button>
    <a class="btn btn-default add" href="<?php echo $this->url("admin/content/product-class/field/{$product_class['product_class_id']}/add"); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
  </div>
  <div class="table-responsive">
    <table class="table fields" data-sortable-input-weight="true">
      <thead>
        <tr>
          <th>
            <a href="<?php echo $sort_product_class_field_id; ?>">
              <?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_field_id; ?>">
              <?php echo $this->text('Field ID'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_title; ?>">
              <?php echo $this->text('Title'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_type; ?>">
              <?php echo $this->text('Type'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_required; ?>">
              <?php echo $this->text('Required'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_multiple; ?>">
              <?php echo $this->text('Multiple'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_weight; ?>">
              <?php echo $this->text('Weight'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th><?php echo $this->text('Remove'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($fields as $id => $field) { ?>
        <tr>
          <td class="middle">
            <?php echo $this->e($id); ?>
          </td>
          <td class="middle">
            <?php echo $this->e($field['field_id']); ?>
          </td>
          <td class="middle">
            <?php echo $this->e($field['title']); ?>
            <input type="hidden" name="fields[<?php echo $id; ?>][weight]" value="<?php echo $field['weight']; ?>">
          </td>
          <td class="middle">
            <?php echo empty($field_types[$field['type']]) ? $this->text('Unknown') : $this->e($field_types[$field['type']]); ?>
          </td>
          <td class="middle">
            <input type="checkbox" name="fields[<?php echo $id; ?>][required]" value="1"<?php echo $field['required'] ? ' checked' : ''; ?>>
          </td>
          <td class="middle">
            <input type="checkbox" name="fields[<?php echo $id; ?>][multiple]" value="1"<?php echo $field['multiple'] ? ' checked' : ''; ?>>
          </td>
          <td class="middle">
            <?php if($draggable) { ?>
            <i class="fa fa-arrows handle"></i>
            <?php } ?>
            <span class="weight"><?php echo $this->e($field['weight']); ?></span>
          </td>
          <td class="middle">
            <input type="checkbox" name="fields[<?php echo $id; ?>][remove]" value="1">
          </td>
          <td class="middle">
            <ul class="list-inline">
              <?php if ($this->access('field_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/field/edit/{$field['field_id']}", array('target' => $this->path())); ?>">
                  <?php echo $this->lower($this->text('Edit')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('field_value')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/field/value/{$field['field_id']}"); ?>">
                  <?php echo $this->lower($this->text('Values')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('field_value_add')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/field/value/{$field['field_id']}/add", array('target' => $this->path())); ?>">
                  <?php echo $this->lower($this->text('Add value')); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</form>
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>&nbsp;
<a class="btn btn-default" href="<?php echo $this->url("admin/content/product-class/field/{$product_class['product_class_id']}/add"); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>