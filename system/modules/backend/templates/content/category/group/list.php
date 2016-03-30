<?php if ($groups || $filtering) { ?>
<div class="row">
  <div class="col-md-6 col-md-offset-6 text-right">
    <div class="btn-toolbar">
      <?php if ($this->access('category_group_add')) { ?>
      <a class="btn btn-success" href="<?php echo $this->url('admin/content/category/group/add'); ?>">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
      <?php } ?>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <table class="table table-responsive margin-top-20 category-group">
      <thead>
        <tr>
          <th><a href="<?php echo $sort_title; ?>"><?php echo $this->text('Title'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_store_id; ?>"><?php echo $this->text('Store'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_type; ?>"><?php echo $this->text('Type'); ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
        <tr class="filters active">
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
        <?php foreach ($groups as $id => $group) { ?>
        <tr>
          <td class="middle"><?php echo $this->escape($group['title']); ?></td>
          <td class="middle">
          <?php echo isset($stores[$group['store_id']]) ? $this->escape($stores[$group['store_id']]) : $this->text('Unknown'); ?>
          </td>
          <td class="middle"><?php echo $this->text($group['type'], array(), $this->text('None')); ?>
          </td>
          <td class="middle">
            <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                  <?php if ($this->access('category_group_edit')) { ?>
                  <li>
                    <a href="<?php echo $this->url("admin/content/category/group/edit/$id"); ?>">
                      <?php echo $this->text('Edit'); ?>
                    </a>
                  </li>
                  <?php } ?>
                  <?php if ($this->access('category')) { ?>
                  <li>
                    <a href="<?php echo $this->url("admin/content/category/$id"); ?>">
                      <?php echo $this->text('Categories'); ?>
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
    <?php echo $this->text('You have no category groups yet'); ?>
    <?php if ($this->access('category_group_add')) { ?>
    <a href="<?php echo $this->url('admin/content/category/group/add'); ?>">
    <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>