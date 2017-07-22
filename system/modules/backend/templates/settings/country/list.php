<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($countries) || $_filtering) { ?>
<form data-filter-empty="true">
  <?php if ($this->access('country_edit') || $this->access('country_delete') || $this->access('country_add')) { ?>
  <div class="btn-toolbar actions">
    <?php $access_options = false; ?>
    <?php if ($this->access('country_edit') || $this->access('country_delete')) { ?>
    <?php $access_options = true; ?>
    <div class="btn-group">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?php echo $this->text('With selected'); ?> <span class="caret"></span>
      </button>
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
    </div>
    <?php } ?>
    <?php if ($this->access('country_add')) { ?>
    <a class="btn btn-default add" href="<?php echo $this->url('admin/settings/country/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="table-responsive">
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
              <option value=""><?php echo $this->text('Any'); ?></option>
              <option value="1"<?php echo $filter_status === '1' ? ' selected' : ''; ?>>
              <?php echo $this->text('Enabled'); ?>
              </option>
              <option value="0"<?php echo $filter_status === '0' ? ' selected' : ''; ?>>
              <?php echo $this->text('Disabled'); ?>
              </option>
            </select>
          </th>
          <th class="middle">
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
        <?php if ($_filtering && empty($countries)) { ?>
        <tr>
          <td class="middle" colspan="6">
            <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($countries as $code => $country) { ?>
        <tr>
          <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $code; ?>"<?php echo $access_options ? '' : ' disabled'; ?>></td>
          <td class="middle"><?php echo $this->e($country['name']); ?></td>
          <td class="middle"><?php echo $this->e($country['native_name']); ?></td>
          <td class="middle"><?php echo $this->e($code); ?></td>
          <td class="middle">
            <?php if (empty($country['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <ul class="list-inline">
              <?php if ($this->access('country_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/settings/country/edit/$code"); ?>">
                  <?php echo $this->lower($this->text('Edit')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('state')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/settings/states/$code"); ?>">
                  <?php echo $this->lower($this->text('States')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('country_format')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/settings/country/format/$code"); ?>">
                  <?php echo $this->lower($this->text('Format')); ?>
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
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('There are no items yet'); ?>
    <?php if ($this->access('country_add')) { ?>
    <a class="btn btn-default add" href="<?php echo $this->url('admin/settings/country/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>