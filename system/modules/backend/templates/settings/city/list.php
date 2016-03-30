<?php if ($cities || $filtering) { ?>
<div class="row">
   <div class="col-md-6">
    <div class="btn-group">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?php echo $this->text('With selected'); ?> <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <?php if ($this->access('city_edit')) { ?>
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
        <?php if ($this->access('city_delete')) { ?>
        <li>
          <a data-action="delete" href="#">
            <?php echo $this->text('Delete'); ?>
          </a>
        </li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="col-md-6 text-right">
    <div class="btn-toolbar">
      <a href="<?php echo $this->url("admin/settings/states/{$country['code']}"); ?>" class="btn btn-default cancel">
        <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
      </a>
      <?php if ($this->access('city_add')) { ?>
      <a href="<?php echo $this->url("admin/settings/city/add/{$country['code']}/{$state['state_id']}"); ?>" class="btn btn-success add">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
      <?php if($this->access('import')) { ?>
      <a href="<?php echo $this->url('admin/tool/import/city'); ?>" class="btn btn-primary add">
        <i class="fa fa-upload"></i> <?php echo $this->text('Import'); ?>
      </a>
      <?php } ?>
      <?php } ?>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <table class="table margin-top-20 cities">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"></th>
          <th><a href="<?php echo $sort_city_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
        <tr class="filters active">
          <th></th>
          <th></th>
          <th class="middle">
            <input class="form-control" name="name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th class="middle">
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
          <th class="middle">
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
        <?php if($filtering && !$cities) { ?>
        <tr><td class="middle" colspan="5"><?php echo $this->text('No results'); ?></td></tr>
        <?php } ?>
        <?php foreach ($cities as $city_id => $city) { ?>
        <tr>
          <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $city_id; ?>"></td>
          <td class="middle"><?php echo $city_id; ?></td>
          <td class="middle"><?php echo $this->escape($city['name']); ?></td>
          <td class="middle">
            <?php if ($city['status']) { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if ($this->access('city_edit')) { ?>
            <a title="<?php echo $this->text('Edit'); ?>" href="<?php echo $this->url("admin/settings/city/edit/{$country['code']}/{$state['state_id']}/{$city['city_id']}"); ?>" class="btn btn-default">
              <i class="fa fa-edit"></i>
            </a>
            <?php } ?>
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
    <?php echo $this->text('This state has no cities yet'); ?>
    <?php if ($this->access('city_add')) { ?>
    <a href="<?php echo $this->url("admin/settings/city/add/{$country['code']}/{$state['state_id']}"); ?>">
    <?php echo $this->text('Add'); ?>
    </a>
    <?php if($this->access('import')) {?>
    <a href="<?php echo $this->url('admin/tool/import/city'); ?>"><?php echo $this->text('Import'); ?></a>
    <?php } ?>
    <?php } ?>
  </div>
</div>
<?php } ?>