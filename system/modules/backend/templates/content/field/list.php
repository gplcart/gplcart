<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($fields) || $filtering) { ?>
<div class="panel panel-default">
  <div class="panel-heading clearfix">
  <div class="btn-group pull-left">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
       <span class="caret"></span>
    </button>
    <?php $access_options = false; ?>
    <?php if ($this->access('field_delete')) { ?>
    <?php $access_options = true; ?>
    <ul class="dropdown-menu">
      <li>
        <a data-action="delete" data-action-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>" href="#">
          <?php echo $this->text('Delete'); ?>
        </a>
      </li>
    </ul>
    <?php } ?>
  </div>
  <?php if ($this->access('field_add')) { ?>
  <div class="btn-toolbar pull-right">
    <a class="btn btn-default add" href="<?php echo $this->url('admin/content/field/add'); ?>">
      <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
    </a>
  </div>
  <?php } ?>
  </div>
  <div class="panel-body table-responsive">
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
        <tr class="filters active">
          <th></th>
          <th></th>
          <th class="middle">
            <input class="form-control" name="title" value="<?php echo $filter_title; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th class="middle">
            <select class="form-control" name="type">
              <option value="any"><?php echo $this->text('Any'); ?></option>
              <option value="option"<?php echo ($filter_type == 'option') ? ' selected' : ''; ?>>
              <?php echo $this->text('Option'); ?>
              </option>
              <option value="attribute"<?php echo ($filter_type == 'attribute') ? ' selected' : ''; ?>>
              <?php echo $this->text('Attribute'); ?>
              </option>
            </select>
          </th>
          <th class="middle">
            <select class="form-control" name="widget">
              <option value="any"><?php echo $this->text('Any'); ?></option>
              <?php foreach ($widget_types as $type => $name) { ?>
              <option value="<?php echo $type; ?>"<?php echo ($filter_widget == $type) ? ' selected' : ''; ?>>
              <?php echo $this->escape($name); ?>
              </option>
              <?php } ?>
            </select>
          </th>
          <th class="middle">
            <button type="button" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
              <i class="fa fa-refresh"></i>
            </button>
            <button type="button" class="btn btn-default filter" title="<?php echo $this->text('Filter'); ?>">
              <i class="fa fa-search"></i>
            </button>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php if ($filtering && empty($fields)) { ?>
        <tr><td class="middle" colspan="6"><?php echo $this->text('No results'); ?></td></tr>
        <?php } ?>
        <?php foreach ($fields as $field) { ?>
        <tr>
          <td class="middle">
            <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $field['field_id']; ?>"<?php echo $access_options ? '' : ' disabled'; ?>>
          </td>
          <td class="middle"><?php echo $field['field_id']; ?></td>
          <td class="middle"><?php echo $this->escape($field['title']); ?></td>
          <td class="middle"><?php echo ($field['type'] == 'attribute') ? $this->text('Attribute') : $this->text('Option'); ?></td>
          <td class="middle"><?php echo isset($widget_types[$field['widget']]) ? $widget_types[$field['widget']] : $this->text('Unknown'); ?></td>
          <td class="middle">
            <ul class="list-inline">
              <?php if ($this->access('field_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/field/edit/{$field['field_id']}"); ?>">
                  <?php echo mb_strtolower($this->text('Edit')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('field_value')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/field/value/{$field['field_id']}"); ?>">
                  <?php echo mb_strtolower($this->text('Values')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('field_value_add')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/field/value/{$field['field_id']}/add"); ?>">
                  <?php echo mb_strtolower($this->text('Add value')); ?>
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
  <?php if (!empty($pager)) { ?>
  <div class="panel-footer"><?php echo $pager; ?></div>
  <?php } ?>
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no fields yet'); ?>
    <?php if ($this->access('field_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/field/add'); ?>"><?php echo $this->text('Add'); ?></a>
    <?php } ?>
  </div>
</div>
<?php } ?>