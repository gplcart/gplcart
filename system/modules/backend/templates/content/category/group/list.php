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
<?php if (!empty($groups) || $_filtering) { ?>
<form data-filter-empty="true">
  <?php if ($this->access('category_group_add')) { ?>
  <div class="btn-toolbar actions">
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/category-group/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table category-group">
      <thead>
        <tr>
          <th><a href="<?php echo $sort_category_group_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_title; ?>"><?php echo $this->text('Title'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_store_id; ?>"><?php echo $this->text('Store'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_type; ?>"><?php echo $this->text('Type'); ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
        <tr class="filters active">
          <th></th>
          <th>
            <input class="form-control" name="title" value="<?php echo $filter_title; ?>" placeholder="<?php echo $this->text('Any'); ?>">
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
            <select class="form-control" name="type">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <option value="catalog"<?php echo $filter_type === 'catalog' ? ' selected' : ''; ?>><?php echo $this->text('Catalog'); ?></option>
              <option value="brand"<?php echo $filter_type === 'brand' ? ' selected' : ''; ?>><?php echo $this->text('Brand'); ?></option>
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
        <?php if (empty($groups) && $_filtering) { ?>
        <tr>
          <td colspan="5">
            <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } else { ?>
        <?php foreach ($groups as $id => $group) { ?>
        <tr>
          <td class="middle"><?php echo $id; ?></td>
          <td class="middle"><?php echo $this->e($group['title']); ?></td>
          <td class="middle">
            <?php echo isset($_stores[$group['store_id']]) ? $this->e($_stores[$group['store_id']]['name']) : $this->text('Unknown'); ?>
          </td>
          <td class="middle"><?php echo $this->text($group['type']); ?>
          </td>
          <td class="middle">
            <ul class="list-inline">
              <?php if ($this->access('category_group_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/category-group/edit/$id"); ?>">
                  <?php echo $this->lower($this->text('Edit')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('category')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/category/$id"); ?>">
                  <?php echo $this->lower($this->text('Categories')); ?>
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
<?php echo $this->text('There are no items yet'); ?>&nbsp;
<?php if ($this->access('category_group_add')) { ?>
<a class="btn btn-default" href="<?php echo $this->url('admin/content/category-group/add'); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>