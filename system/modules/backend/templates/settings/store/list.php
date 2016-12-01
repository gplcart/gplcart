<div class="panel panel-default">
  <div class="panel-heading clearfix">
    <div class="btn-group pull-left">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?php echo $this->text('With selected'); ?> <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <?php if ($this->access('store_edit')) { ?>
        <li>
          <a data-action="status" data-action-value="1" href="#">
            <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
          </a>
        </li>
        <li>
          <a data-action="status" data-action-value="0" href="#">
            <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
          </a>
        </li>
        <?php } ?>
        <?php if ($this->access('store_delete')) { ?>
        <li>
          <a data-action="delete" href="#">
            <?php echo $this->text('Delete'); ?>
          </a>
        </li>
        <?php } ?>
      </ul>
    </div>
    <?php if ($this->access('store_add')) { ?>
    <div class="btn-toolbar pull-right">
      <a href="<?php echo $this->url('admin/settings/store/add'); ?>" class="btn btn-default add">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
    </div>
    <?php } ?>
  </div>
  <div class="panel-body table-responsive">
    <table class="table stores">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"></th>
          <th><?php echo $this->text('ID'); ?></th>
          <th class="middle">
            <a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_domain; ?>"><?php echo $this->text('Domain'); ?> <i class="fa fa-sort"></i></a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_basepath; ?>"><?php echo $this->text('Path'); ?> <i class="fa fa-sort"></i></a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_status; ?>"><?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i></a>
          </th>
          <th></th>
        </tr>
        <tr class="filters active">
          <th></th>
          <th></th>
          <th class="middle">
            <input class="form-control" maxlength="255" name="name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th class="middle">
            <input class="form-control" maxlength="255" name="domain" value="<?php echo $filter_domain; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th class="middle">
            <input class="form-control" maxlength="255" name="basepath" value="<?php echo $filter_basepath; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th class="text-center middle">
            <select class="form-control" name="status">
              <option value="any">
              <?php echo $this->text('Any'); ?>
              </option>
              <option value="1"<?php echo ($filter_status === '1') ? ' selected' : ''; ?>>
              <?php echo $this->text('Enabled'); ?>
              </option>
              <option value="0"<?php echo ($filter_status === '0') ? ' selected' : ''; ?>>
              <?php echo $this->text('Disabled'); ?>
              </option>
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
        <?php if ($filtering && empty($stores)) { ?>
        <tr>
          <td class="middle" colspan="8">
            <?php echo $this->text('No results'); ?>
            <a href="#" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($stores as $store_id => $store) { ?>
        <tr>
          <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $store_id; ?>"></td>
          <td class="middle"><?php echo $store_id; ?></td>
          <td class="middle"><?php echo $this->escape($store['name']); ?></td>
          <td class="middle"><?php echo $this->escape($store['domain']); ?></td>
          <td class="middle">/<?php echo $this->escape($store['basepath']); ?></td>
          <td class="middle">
            <?php if (empty($store['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if ($this->access('store_edit')) { ?>
            <a href="<?php echo $this->url("admin/settings/store/$store_id"); ?>">
              <?php echo strtolower($this->text('Edit')); ?>
            </a>
            <?php } ?>
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