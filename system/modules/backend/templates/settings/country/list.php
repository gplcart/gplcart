<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($countries) || $filtering) { ?>
<div class="panel panel-default">
  <div class="panel-heading clearfix">
    <div class="btn-group pull-left">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
         <span class="caret"></span>
      </button>
      <?php $access_options = false; ?>
      <?php if($this->access('country_edit') || $this->access('country_delete')) { ?>
      <?php $access_options = true; ?>
      <ul class="dropdown-menu">
        <?php if ($this->access('country_edit')) { ?>
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
        <?php if ($this->access('country_delete')) { ?>
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
      <?php if ($this->access('country_add')) { ?>
      <a href="<?php echo $this->url('admin/settings/country/add'); ?>" class="btn btn-default add">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
      <?php } ?>
    </div>
  </div>
  <div class="panel-body table-responsive">
    <table class="table countries">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"<?php echo $access_options ? '' : ' disabled'; ?>></th>
          <th><a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_native_name; ?>"><?php echo $this->text('Native name'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_code; ?>"><?php echo $this->text('Code'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
        <tr class="filters active">
          <th></th>
          <th class="middle">
            <input class="form-control" name="name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th class="middle">
            <input class="form-control" name="native_name" value="<?php echo $filter_native_name; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th class="middle">
            <input class="form-control" name="code" value="<?php echo $filter_code; ?>" placeholder="<?php echo $this->text('Any'); ?>">
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
        <?php if ($filtering && empty($countries)) { ?>
        <tr>
          <td class="middle" colspan="6">
            <?php echo $this->text('No results'); ?>
            <a href="#" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($countries as $code => $country) { ?>
        <tr>
          <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $code; ?>"<?php echo $access_options ? '' : ' disabled'; ?>></td>
          <td class="middle">
            <input type="hidden" name="country[<?php echo $code; ?>][weight]" value="<?php echo $country['weight']; ?>">
            <?php echo $this->escape($country['name']); ?>
            <?php if ($default_country == $code) { ?>
            (<?php echo mb_strtolower($this->text('Default')); ?>)
            <?php } ?>
          </td>
          <td class="middle"><?php echo $this->escape($country['native_name']); ?></td>
          <td class="middle"><?php echo $this->escape($code); ?></td>
          <td class="middle">
            <?php if ($country['status']) { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <ul class="list-inline">
              <?php if ($this->access('country_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/settings/country/edit/$code"); ?>">
                  <?php echo mb_strtolower($this->text('Edit')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('state')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/settings/states/$code"); ?>">
                  <?php echo mb_strtolower($this->text('States')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('country_format')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/settings/country/format/$code"); ?>">
                  <?php echo mb_strtolower($this->text('Format')); ?>
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
  <?php if (!empty($pager)) { ?>
  <div class="panel-footer"><?php echo $pager; ?></div>
  <?php } ?>
</div>
<?php } else { ?>
<div class="row empty">
  <div class="col-md-12">
    <?php echo $this->text('You have no countries yet'); ?>
    <?php if ($this->access('country_add')) { ?>
    <a class="btn btn-default add" href="<?php echo $this->url('admin/settings/country/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>