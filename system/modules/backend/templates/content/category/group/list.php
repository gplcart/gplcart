<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($groups) || $filtering) { ?>
<div class="panel panel-default">
  <div class="panel-heading clearfix">
    <?php if ($this->access('category_group_add')) { ?>
    <div class="btn-toolbar pull-right">
      <a class="btn btn-default" href="<?php echo $this->url('admin/content/category-group/add'); ?>">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
    </div>
    <?php } ?>
  </div>
  <div class="panel-body table-responsive">
    <table class="table table-condensed category-group">
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
              <option value=""<?php echo (!$filter_store_id) ? ' selected' : ''; ?>><?php echo $this->text('Any'); ?></option>
              <?php foreach ($stores as $store_id => $store_name) { ?>
              <option value="<?php echo $store_id; ?>"<?php echo ($filter_store_id == $store_id) ? ' selected' : ''; ?>><?php echo $this->escape($store_name); ?></option>
              <?php } ?>
            </select>
          </th>
          <th>
            <select class="form-control" name="type">
              <option value=""<?php echo (!$filter_type) ? ' selected' : ''; ?>><?php echo $this->text('Any'); ?></option>
              <option value="catalog"<?php echo ($filter_type === 'catalog') ? ' selected' : ''; ?>><?php echo $this->text('Catalog'); ?></option>
              <option value="brand"<?php echo ($filter_type === 'brand') ? ' selected' : ''; ?>><?php echo $this->text('Brand'); ?></option>
            </select>
          </th>
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
        <?php if (empty($groups) && $filtering) { ?>
        <tr>
          <td colspan="5">
            <?php echo $this->text('No results'); ?>
            <a class="clear-filter" href="#"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } else { ?>
        <?php foreach ($groups as $id => $group) { ?>
        <tr>
          <td class="middle"><?php echo $id; ?></td>
          <td class="middle"><?php echo $this->escape($group['title']); ?></td>
          <td class="middle">
            <?php echo isset($stores[$group['store_id']]) ? $this->escape($stores[$group['store_id']]) : $this->text('Unknown'); ?>
          </td>
          <td class="middle"><?php echo $this->text($group['type'], array(), $this->text('None')); ?>
          </td>
          <td class="middle">
            <ul class="list-inline">
              <?php if ($this->access('category_group_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/category-group/edit/$id"); ?>">
                  <?php echo mb_strtolower($this->text('Edit')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('category')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/category/$id"); ?>">
                  <?php echo mb_strtolower($this->text('Categories')); ?>
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
  <?php if (!empty($pager)) { ?>
  <div class="panel-footer text-right"><?php echo $pager; ?></div>
  <?php } ?>
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no category groups yet'); ?>
    <?php if ($this->access('category_group_add')) { ?>
    <a href="<?php echo $this->url('admin/content/category-group/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>