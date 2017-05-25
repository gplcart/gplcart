<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($pages) || $_filtering) { ?>
<form method="post" id="pages" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <div class="btn-group pull-left">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
           <span class="caret"></span>
        </button>
        <?php $access_actions = false; ?>
        <?php if ($this->access('page_edit') || $this->access('page_delete')) { ?>
        <?php $access_actions = true; ?>
        <ul class="dropdown-menu">
          <?php if ($this->access('page_edit')) { ?>
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
          <?php if ($this->access('page_delete')) { ?>
          <li>
            <a data-action="delete" data-action-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>" href="#">
              <?php echo $this->text('Delete'); ?>
            </a>
          </li>
          <?php } ?>
        </ul>
        <?php } ?>
      </div>
      <div class="btn-toolbar pull-right">
        <?php if ($this->access('page_add')) { ?>
        <a class="btn btn-default" href="<?php echo $this->url('admin/content/page/add'); ?>">
          <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
        </a>
        <?php } ?>
      </div>
    </div>
    <div class="panel-body table-responsive">
      <table class="table table-condensed pages-list">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all" value="1"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
            <th>
              <a href="<?php echo $sort_page_id; ?>">
                <?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_title; ?>">
                <?php echo $this->text('Title'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_store_id; ?>">
                <?php echo $this->text('Store'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_email; ?>">
                <?php echo $this->text('Author'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_status; ?>">
                <?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_created; ?>">
                <?php echo $this->text('Created'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th></th>
          </tr>
          <tr class="filters active">
            <th></th>
            <th><?php echo $this->text('ID'); ?></th>
            <th>
              <input class="form-control" name="title" value="<?php echo $filter_title; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th>
              <select class="form-control" name="store_id">
                <option value="any"><?php echo $this->text('Any'); ?></option>
                <?php foreach ($_stores as $store_id => $store) { ?>
                <option value="<?php echo $store_id; ?>"<?php echo $filter_store_id == $store_id ? ' selected' : ''; ?>>
                <?php echo $this->escape($store['name']); ?>
                </option>
                <?php } ?>
              </select>
            </th>
            <th>
              <input class="form-control" value="<?php echo $filter_email; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th>
              <select class="form-control" name="status">
                <option value="any">
                <?php echo $this->text('Any'); ?>
                </option>
                <option value="1"<?php echo $filter_status === '1' ? ' selected' : ''; ?>>
                <?php echo $this->text('Enabled'); ?>
                </option>
                <option value="0"<?php echo $filter_status === '0' ? ' selected' : ''; ?>>
                <?php echo $this->text('Disabled'); ?>
                </option>
              </select>
            </th>
            <th></th>
            <th>
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
          <?php if ($_filtering && empty($pages)) { ?>
          <tr>
            <td colspan="8">
              <?php echo $this->text('No results'); ?>
              <a href="#" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
            </td>
          </tr>
          <?php } ?>
          <?php foreach ($pages as $id => $page) { ?>
          <tr>
            <td class="middle">
              <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>>
            </td>
            <td class="middle"><?php echo $this->escape($id); ?></td>
            <td class="middle"><?php echo $this->truncate($this->escape($page['title']), 30); ?></td>
            <td class="middle">
              <?php if (isset($_stores[$page['store_id']])) { ?>
              <?php echo $this->escape($_stores[$page['store_id']]['name']); ?>
              <?php } else { ?>
              <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
              <?php } ?>
            </td>
            <td class="middle">
                <?php echo $this->escape($page['email']); ?>
            </td>
            <td class="middle">
              <?php if (empty($page['status'])) { ?>
              <i class="fa fa-square-o"></i>
              <?php } else { ?>
              <i class="fa fa-check-square-o"></i>
              <?php } ?>
            </td>
            <td class="middle"><?php echo $this->date($page['created']); ?></td>
            <td class="middle">
              <ul class="list-inline">
                <li>
                  <a href="<?php echo $this->escape($page['url']); ?>">
                    <?php echo $this->lower($this->text('View')); ?>
                  </a>
                </li>
                <?php if ($this->access('page_edit')) { ?>
                <li>
                  <a href="<?php echo $this->url("admin/content/page/edit/$id"); ?>">
                  <?php echo $this->lower($this->text('Edit')); ?>
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
    <div class="panel-footer text-right"><?php echo $_pager; ?></div>
    <?php } ?>
  </div>
</form>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no pages yet'); ?>
    <?php if ($this->access('page_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/page/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>