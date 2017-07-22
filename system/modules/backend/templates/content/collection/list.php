<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($collections) || $_filtering) { ?>
<form data-filter-empty="true">
  <?php if ($this->access('collection_edit') || $this->access('collection_delete') || $this->access('collection_add')) { ?>
  <div class="btn-toolbar actions">
    <?php $access_actions = false; ?>
    <?php if ($this->access('collection_edit') || $this->access('collection_delete')) { ?>
    <?php $access_actions = true; ?>
    <div class="btn-group">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?php echo $this->text('With selected'); ?> <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <?php if ($this->access('collection_edit')) { ?>
        <li>
          <a data-action="status" data-action-value="1" data-action-confirm="<?php echo $this->text('Are you sure?'); ?>" href="#">
            <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
          </a>
        </li>
        <li>
          <a data-action="status" data-action-value="0" data-action-confirm="<?php echo $this->text('Are you sure?'); ?>" href="#">
            <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
          </a>
        </li>
        <?php } ?>
        <?php if ($this->access('collection_delete')) { ?>
        <li>
          <a data-action="delete" data-action-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>" href="#">
            <?php echo $this->text('Delete'); ?>
          </a>
        </li>
        <?php } ?>
      </ul>
    </div>
    <?php } ?>
    <?php if ($this->access('collection_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/collection/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table collections">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
          <th><a href="<?php echo $sort_collection_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_title; ?>"><?php echo $this->text('Title'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_type; ?>"><?php echo $this->text('Type'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_store_id; ?>"><?php echo $this->text('Store'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
        <tr class="filters active">
          <th></th>
          <th></th>
          <th>
            <input class="form-control" name="title" value="<?php echo $filter_title; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <select class="form-control" name="type">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach ($handlers as $handler_id => $handler) { ?>
              <?php if ($filter_type === $handler_id) { ?>
              <option value="<?php echo $this->e($handler_id); ?>" selected><?php echo $this->e($handler['title']); ?></option>
              <?php } else { ?>
              <option value="<?php echo $this->e($handler_id); ?>"><?php echo $this->e($handler['title']); ?></option>
              <?php } ?>
              <?php } ?>
            </select>
          </th>
          <th>
            <select class="form-control" name="store_id">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach ($_stores as $store_id => $store) { ?>
              <option value="<?php echo $store_id; ?>"<?php echo $filter_store_id == $store_id ? ' selected' : ''; ?>><?php echo $this->e($store['name']); ?></option>
              <?php } ?>
            </select>
          </th>
          <th>
            <select class="form-control" name="status">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <option value="1"<?php echo $filter_status === '1' ? ' selected' : ''; ?>>
                <?php echo $this->text('Enabled'); ?>
              </option>
              <option value="0"<?php echo $filter_status === '0' ? ' selected' : ''; ?>>
                <?php echo $this->text('Disabled'); ?>
              </option>
            </select>
          </th>
          <th>
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
        <?php if (empty($collections) && $_filtering) { ?>
        <tr>
          <td colspan="7">
              <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } else { ?>
        <?php foreach ($collections as $id => $collection) { ?>
        <tr>
          <td class="middle">
            <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>>
          </td>
          <td class="middle"><?php echo $this->e($id); ?></td>
          <td class="middle"><?php echo $this->e($collection['title']); ?></td>
          <td class="middle">
            <?php if (isset($handlers[$collection['type']]['title'])) { ?>
            <?php echo $this->e($handlers[$collection['type']]['title']); ?>
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (isset($_stores[$collection['store_id']])) { ?>
            <?php echo $this->e($_stores[$collection['store_id']]['name']); ?>
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (empty($collection['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <ul class="list-inline">
              <?php if ($this->access('collection_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/collection/edit/$id"); ?>">
                  <?php echo $this->lower($this->text('Edit')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('collection_item')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/collection-item/$id"); ?>">
                  <?php echo $this->lower($this->text('Items')); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
          </td>
        </tr>
        <?php } ?>
        <?php } ?>
      </tbody>
    </table>
  </div>
  <?php if (!empty($_pager)) { ?>
  <?php echo $_pager; ?>
  <?php } ?>
</form>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('There are no items yet'); ?>
    <?php if ($this->access('collection_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/collection/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>