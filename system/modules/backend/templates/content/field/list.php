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
<?php if (!empty($fields) || $_filtering) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php if ($this->access('field_delete') || $this->access('field_add')) { ?>
  <div class="form-inline bulk-actions">
    <?php $access_options = false; ?>
    <?php if ($this->access('field_delete')) { ?>
    <?php $access_options = true; ?>
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="GplCart.action(event);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <option value="delete" data-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>">
          <?php echo $this->text('Delete'); ?>
        </option>
      </select>
      <span class="input-group-btn hidden-js">
        <button class="btn btn-default" name="action[submit]" value="1"><?php echo $this->text('OK'); ?></button>
      </span>
    </div>
    <?php } ?>
    <?php if ($this->access('field_add')) { ?>
    <a class="btn btn-default add" href="<?php echo $this->url('admin/content/field/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table fields">
      <thead>
        <tr>
          <th class="middle"><input type="checkbox" id="select-all" value="1"<?php echo $access_options ? '' : ' disabled'; ?>></th>
          <th><a href="<?php echo $sort_field_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_title; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_type; ?>"><?php echo $this->text('Type'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_widget; ?>"><?php echo $this->text('Widget'); ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
        <tr class="filters active hidden-no-js">
          <th></th>
          <th></th>
          <th class="middle">
            <input class="form-control" name="title" value="<?php echo $filter_title; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th class="middle">
            <select class="form-control" name="type">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <option value="option"<?php echo $filter_type === 'option' ? ' selected' : ''; ?>>
                <?php echo $this->text('Option'); ?>
              </option>
              <option value="attribute"<?php echo $filter_type === 'attribute' ? ' selected' : ''; ?>>
                <?php echo $this->text('Attribute'); ?>
              </option>
            </select>
          </th>
          <th class="middle">
            <select class="form-control" name="widget">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach ($widget_types as $type => $name) { ?>
              <option value="<?php echo $type; ?>"<?php echo $filter_widget == $type ? ' selected' : ''; ?>>
                <?php echo $this->e($name); ?>
              </option>
              <?php } ?>
            </select>
          </th>
          <th class="middle">
            <a href="<?php echo $this->url($_path); ?>" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
              <i class="fa fa-refresh"></i>
            </a>
            <button class="btn btn-default filter" title="<?php echo $this->text('Filter'); ?>">
              <i class="fa fa-search"></i>
            </button>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php if ($_filtering && empty($fields)) { ?>
        <tr>
          <td class="middle" colspan="6">
            <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($fields as $field) { ?>
        <tr>
          <td class="middle">
            <input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $field['field_id']; ?>"<?php echo $access_options ? '' : ' disabled'; ?>>
          </td>
          <td class="middle"><?php echo $field['field_id']; ?></td>
          <td class="middle"><?php echo $this->e($field['title']); ?></td>
          <td class="middle"><?php echo $field['type'] == 'attribute' ? $this->text('Attribute') : $this->text('Option'); ?></td>
          <td class="middle"><?php echo isset($widget_types[$field['widget']]) ? $widget_types[$field['widget']] : $this->text('Unknown'); ?></td>
          <td class="middle">
            <ul class="list-inline">
              <?php if ($this->access('field_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/field/edit/{$field['field_id']}"); ?>">
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
                <a href="<?php echo $this->url("admin/content/field/value/{$field['field_id']}/add"); ?>">
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
  <?php if (!empty($_pager)) { ?>
  <?php echo $_pager; ?>
  <?php } ?>
</form>
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>&nbsp;
<?php if ($this->access('field_add')) { ?>
<a class="btn btn-default" href="<?php echo $this->url('admin/content/field/add'); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>