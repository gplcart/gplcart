<?php if (!empty($roles) || $filtering) { ?>
<div class="panel panel-default">
  <div class="panel-heading clearfix">
    <?php if ($this->access('user_role_edit') || $this->access('user_role_delete')) { ?>
    <div class="btn-group pull-left">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?php echo $this->text('With selected'); ?> <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <?php if ($this->access('user_role_edit')) { ?>
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
        <?php if ($this->access('user_role_delete')) { ?>
        <li>
          <a data-action="delete" href="#">
            <?php echo $this->text('Delete'); ?>
          </a>
        </li>
        <?php } ?>
      </ul>
    </div>
    <?php } ?>
    <div class="btn-toolbar pull-right">
      <?php if ($this->access('user_role_add')) { ?>
      <a href="<?php echo $this->url('admin/user/role/add'); ?>" class="btn btn-default add">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
      <?php } ?>
    </div>
  </div>
  <div class="panel-body table-responsive">
    <table class="table roles">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"></th>
          <th><a href="<?php echo $sort_role_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
        <tr class="filters active">
          <th></th>
          <th></th>
          <th>
            <input class="form-control" name="name" maxlength="255" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <select class="form-control" name="status">
              <option value="any"><?php echo $this->text('Any'); ?></option>
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
        <?php if ($filtering && empty($roles)) { ?>
        <tr>
          <td colspan="5">
            <?php echo $this->text('No results'); ?>
            <a href="#" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($roles as $role_id => $role) { ?>
        <tr>
          <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $role_id; ?>"></td>
          <td class="middle"><?php echo $role_id; ?></td>
          <td class="middle"><?php echo $this->escape($role['name']); ?></td>
          <td class="middle">
            <?php if (empty($role['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td>
            <?php if ($this->access('user_role_edit')) { ?>
            <a href="<?php echo $this->url("admin/user/role/edit/$role_id"); ?>">
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
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no roles yet'); ?>
    <?php if ($this->access('user_role_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/user/role/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>