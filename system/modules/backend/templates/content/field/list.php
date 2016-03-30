<?php if ($fields || $filtering) { ?>
<?php if ($this->access('field_add')) { ?>
<div class="row">
  <div class="col-md-6 col-md-offset-6 text-right">
    <div class="btn-toolbar">
      <a class="btn btn-success add" href="<?php echo $this->url('admin/content/field/add'); ?>">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
    </div>
  </div>
</div>
<?php } ?>
<div class="row margin-top-20">
  <div class="col-md-12">
    <table class="table fields">
      <thead>
        <tr>
          <th><a href="<?php echo $sort_title; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_type; ?>"><?php echo $this->text('Type'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_widget; ?>"><?php echo $this->text('Widget'); ?> <i class="fa fa-sort"></i></a></th>
          <th><?php echo $this->text('Action'); ?></th>
        </tr>
        <tr class="filters active">
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
              <?php foreach($widget_types as $type => $name) { ?>
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
      <?php if($filtering && !$fields) { ?>
      <tr><td class="middle" colspan="4"><?php echo $this->text('No results'); ?></td></tr>
      <?php } ?>
      <?php foreach ($fields as $field) { ?>
      <tr>
        <td class="middle"><?php echo $this->escape($field['title']); ?></td>
        <td class="middle"><?php echo ($field['type'] == 'attribute') ? $this->text('Attribute') : $this->text('Option'); ?></td>
        <td class="middle"><?php echo isset($widget_types[$field['widget']]) ? $widget_types[$field['widget']] : $this->text('Unknown'); ?></td>
        <td class="middle">
          <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-bars"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
              <?php if ($this->access('field_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/field/edit/{$field['field_id']}"); ?>">
                <?php echo $this->text('Edit'); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('field_value')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/field/value/{$field['field_id']}"); ?>">
                <?php echo $this->text('Values'); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
          </div>
        </td>
      </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<div class="row">
  <div class="col-md-12"><?php echo $pager; ?></div>
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no fields yet'); ?>
    <?php if ($this->access('field_add')) { ?>
    <a href="<?php echo $this->url('admin/content/field/add'); ?>"><?php echo $this->text('Add'); ?></a>
    <?php } ?>
  </div>
</div>
<?php } ?>