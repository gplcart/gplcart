<div class="row">
  <div class="col-md-6">
    <div class="btn-group">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?php echo $this->text('With selected'); ?> <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <?php if ($this->access('store_edit')) {
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
} ?>
        <?php if ($this->access('store_delete')) {
    ?>
        <li>
          <a data-action="delete" href="#">
            <?php echo $this->text('Delete');
    ?>
          </a>
        </li>
        <?php 
} ?>
      </ul>
    </div>
  </div>
  <?php if ($this->access('store_add')) {
    ?>
  <div class="col-md-6 text-right">
    <div class="btn-toolbar">
      <a href="<?php echo $this->url('admin/settings/store/add');
    ?>" class="btn btn-success add">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add');
    ?>
      </a>
    </div>
  </div>
  <?php 
} ?>
</div>
<div class="row">
  <div class="col-md-12">
    <table class="table table-responsive stores margin-top-20">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"></th>
          <th><?php echo $this->text('ID'); ?></th>
          <th class="middle">
            <a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_scheme; ?>"><?php echo $this->text('Scheme'); ?> <i class="fa fa-sort"></i></a>
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
            <select class="form-control" name="scheme">
              <option value="any">
              <?php echo $this->text('Any'); ?>
              </option>
              <option value="http"<?php echo ($filter_scheme === 'http') ? ' selected' : ''; ?>>http://</option>
              <option value="https"<?php echo ($filter_scheme === 'https') ? ' selected' : ''; ?>>https://</option>
            </select>
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
        <?php if ($filtering && !$stores) {
    ?>
        <tr><td class="middle" colspan="8"><?php echo $this->text('No results');
    ?></td></tr>
        <?php 
} ?>
        <?php foreach ($stores as $store_id => $store) {
    ?>
        <tr>
          <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $store_id;
    ?>"></td>
          <td class="middle"><?php echo $store_id;
    ?></td>
          <td class="middle"><?php echo $this->escape($store['name']);
    ?></td>
          <th class="middle"><?php echo $this->escape($store['scheme']);
    ?></th>
          <td class="middle"><?php echo $this->escape($store['domain']);
    ?></td>
          <td class="middle">/<?php echo $this->escape($store['basepath']);
    ?></td>
          <td class="middle">
            <?php if (empty($store['status'])) {
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
          <td class="middle">
          <?php if ($this->access('store_edit')) {
    ?>
          <a href="<?php echo $this->url("admin/settings/store/$store_id");
    ?>" title="<?php echo $this->text('Settings');
    ?>" class="btn btn-default">
            <i class="fa fa-edit"></i>
          </a>
          <?php 
}
    ?>
          </td>
        </tr>
        <?php 
} ?>
      </tbody>
    </table>
  </div>
</div>
<div class="row">
  <div class="col-md-12"><?php echo $pager; ?></div>
</div>