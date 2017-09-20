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
<?php if (!empty($pages) || $_filtering) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php if ($this->access('page_edit') || $this->access('page_delete') || $this->access('page_add')) { ?>
  <div class="form-inline actions">
    <?php $access_actions = false; ?>
    <?php if ($this->access('page_edit') || $this->access('page_delete')) { ?>
    <?php $access_actions = true; ?>
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="Gplcart.action(this);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <?php if ($this->access('page_edit')) { ?>
        <option value="status|1" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
        </option>
        <option value="status|0" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
        </option>
        <?php } ?>
        <?php if ($this->access('page_delete')) { ?>
        <option value="delete" data-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>">
          <?php echo $this->text('Delete'); ?>
        </option>
        <?php } ?>
      </select>
      <span class="input-group-btn hidden-js">
        <button class="btn btn-default" name="action[submit]" value="1"><?php echo $this->text('OK'); ?></button>
      </span>
    </div>
    <?php } ?>
    <?php if ($this->access('page_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/page/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table pages">
      <thead>
        <tr>
          <th><input type="checkbox" onchange="Gplcart.selectAll(this);"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
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
        <tr class="filters active hidden-no-js">
          <th></th>
          <th><?php echo $this->text('ID'); ?></th>
          <th>
            <input class="form-control" name="title" value="<?php echo $filter_title; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <select class="form-control" name="store_id">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach ($_stores as $store_id => $store) { ?>
              <option value="<?php echo $store_id; ?>"<?php echo $filter_store_id == $store_id ? ' selected' : ''; ?>>
                <?php echo $this->e($store['name']); ?>
              </option>
              <?php } ?>
            </select>
          </th>
          <th>
            <input class="form-control" value="<?php echo $filter_email; ?>" placeholder="<?php echo $this->text('Any'); ?>">
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
          <th></th>
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
        <?php if ($_filtering && empty($pages)) { ?>
        <tr>
          <td colspan="8">
            <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($pages as $id => $page) { ?>
        <tr>
          <td class="middle">
            <input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>>
          </td>
          <td class="middle"><?php echo $this->e($id); ?></td>
          <td class="middle"><?php echo $this->truncate($this->e($page['title']), 30); ?></td>
          <td class="middle">
            <?php if (isset($_stores[$page['store_id']])) { ?>
            <?php echo $this->e($_stores[$page['store_id']]['name']); ?>
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
            <?php } ?>
          </td>
          <td class="middle">
            <?php echo $this->e($page['email']); ?>
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
                <a href="<?php echo $this->e($page['url']); ?>">
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
  <?php echo $_pager; ?>
  <?php } ?>
</form>
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>&nbsp;
<?php if ($this->access('page_add')) { ?>
<a class="btn btn-default" href="<?php echo $this->url('admin/content/page/add'); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>