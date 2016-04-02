<?php if ($roles || $filtering) {
    ?>
<div class="row">
  <div class="col-md-6">
    <?php if ($this->access('user_role_edit') || $this->access('user_role_delete')) {
    ?>
    <div class="btn-group">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?php echo $this->text('With selected');
    ?> <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <?php if ($this->access('user_role_edit')) {
    ?>
        <li>
          <a data-action="status" data-action-value="1" href="#">
            <?php echo $this->text('Status');
    ?>: <?php echo $this->text('Enabled');
    ?>
          </a>
        </li>
        <li>
          <a data-action="status" data-action-value="0" href="#">
            <?php echo $this->text('Status');
    ?>: <?php echo $this->text('Disabled');
    ?>
          </a>
        </li>
        <?php 
}
    ?>
        <?php if ($this->access('user_role_delete')) {
    ?>
        <li>
          <a data-action="delete" href="#">
            <?php echo $this->text('Delete');
    ?>
          </a>
        </li>
        <?php 
}
    ?>
      </ul>
    </div>
    <?php 
}
    ?>
  </div>
  <div class="col-md-6 text-right">
    <?php if ($this->access('user_role_add')) {
    ?>  
    <a href="<?php echo $this->url('admin/user/role/add');
    ?>" class="btn btn-success add">
      <i class="fa fa-plus"></i> <?php echo $this->text('Add');
    ?>
    </a>
    <?php 
}
    ?>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <table class="table table-responsive margin-top-20 roles">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"></th>
          <th><a href="<?php echo $sort_role_id;
    ?>"><?php echo $this->text('ID');
    ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_name;
    ?>"><?php echo $this->text('Name');
    ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_status;
    ?>"><?php echo $this->text('Status');
    ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
        <tr class="filters active">
          <th></th>
          <th></th>
          <th>
            <input class="form-control" name="name" maxlength="255" value="<?php echo $filter_name;
    ?>" placeholder="<?php echo $this->text('Any');
    ?>">
          </th>
          <th>
            <select class="form-control" name="status">
              <option value="any"><?php echo $this->text('Any');
    ?></option>
              <option value="1"<?php echo ($filter_status === '1') ? ' selected' : '';
    ?>>
              <?php echo $this->text('Enabled');
    ?>
              </option>
              <option value="0"<?php echo ($filter_status === '0') ? ' selected' : '';
    ?>>
              <?php echo $this->text('Disabled');
    ?>
              </option>
            </select>
          </th>
          <th>
            <button type="button" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter');
    ?>">
              <i class="fa fa-refresh"></i>
            </button>
            <button type="button" class="btn btn-default filter" title="<?php echo $this->text('Filter');
    ?>">
              <i class="fa fa-search"></i>
            </button>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php if ($filtering && !$roles) {
    ?>
        <tr>
          <td colspan="5"><?php echo $this->text('No results');
    ?></td>
        </tr>
        <?php 
}
    ?>
        <?php foreach ($roles as $role_id => $role) {
    ?>
        <tr>
          <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $role_id;
    ?>"></td>
          <td class="middle"><?php echo $role_id;
    ?></td>
          <td class="middle"><?php echo $this->escape($role['name']);
    ?></td>
          <td class="middle">
            <?php if (empty($role['status'])) {
    ?>
            <i class="fa fa-square-o"></i>
            <?php 
} else {
    ?>
            <i class="fa fa-check-square-o"></i>
            <?php 
}
    ?>
          </td>
          <td>
            <?php if ($this->access('user_role_edit')) {
    ?>
            <a title="<?php echo $this->text('Edit');
    ?>" class="btn btn-default" href="<?php echo $this->url("admin/user/role/edit/$role_id");
    ?>">
              <i class="fa fa-edit"></i>
            </a>
            <?php 
}
    ?>
            <?php if (!empty($role['permissions_list'])) {
    ?>
            <a title="<?php echo $this->text('Permissions');
    ?>" class="btn btn-default" href="#" onclick="return false;" data-toggle="collapse" data-target="#permissions-list-<?php echo $role_id;
    ?>">
              <i class="fa fa-key"></i>
            </a>
            <?php 
}
    ?>
          </td>
        </tr>
        <?php if (!empty($role['permissions_list'])) {
    ?>
        <tr class="collapse active" id="permissions-list-<?php echo $role_id;
    ?>">
          <td colspan="5">
            <div class="row">
              <?php foreach ($role['permissions_list'] as $names) {
    ?>
              <div class="col-md-2">
                <ul class="list-unstyled small">
                  <?php foreach ($names as $name) {
    ?>
                  <li><?php echo $this->escape($name);
    ?></li>
                  <?php 
}
    ?>
                </ul>
              </div>
              <?php 
}
    ?>
            </div>
          </td>
        </tr>
        <?php 
}
    ?>
        <?php 
}
    ?>
      </tbody>
    </table>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <?php echo $pager;
    ?>
  </div>
</div>
<?php 
} else {
    ?>
<div class="row margin-top-20">
  <div class="col-md-12">
    <?php echo $this->text('You have no roles yet');
    ?>
    <?php if ($this->access('user_role_add')) {
    ?>
    <a href="<?php echo $this->url('admin/user/role/add');
    ?>">
    <?php echo $this->text('Add');
    ?>
    </a>
    <?php 
}
    ?>
  </div>
</div>
<?php 
} ?>