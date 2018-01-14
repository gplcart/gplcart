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
<?php if (!empty($currencies) || $_filtering) { ?>
<?php if ($this->access('currency_add')) { ?>
<div class="btn-toolbar actions">
  <a href="<?php echo $this->url('admin/settings/currency/add'); ?>" class="btn btn-default">
    <?php echo $this->text('Add'); ?>
  </a>
</div>
<?php } ?>
<div class="table-responsive">
  <table class="table currencies">
    <thead>
      <tr>
        <th><a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_code; ?>"><?php echo $this->text('Code'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_symbol; ?>"><?php echo $this->text('Symbol'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_conversion_rate; ?>"><?php echo $this->text('Conversion rate'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Enabled'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_default; ?>"><?php echo $this->text('Default'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_in_database; ?>"><?php echo $this->text('In database'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_modified; ?>"><?php echo $this->text('Updated'); ?> <i class="fa fa-sort"></i></a></th>
        <th></th>
      </tr>
      <tr class="filters active hidden-no-js">
        <th><input class="form-control" name="name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th><input class="form-control" name="code" value="<?php echo $filter_code; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th><input class="form-control" name="symbol" value="<?php echo $filter_symbol; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th><input class="form-control" name="conversion_rate" value="<?php echo $filter_conversion_rate; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th>
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
        <th>
          <select class="form-control" name="default">
            <option value=""><?php echo $this->text('Any'); ?></option>
            <option value="1"<?php echo $filter_default === '1' ? ' selected' : ''; ?>>
              <?php echo $this->text('Yes'); ?>
            </option>
            <option value="0"<?php echo $filter_default === '0' ? ' selected' : ''; ?>>
              <?php echo $this->text('No'); ?>
            </option>
          </select>
        </th>
        <th>
          <select class="form-control" name="in_database">
            <option value=""><?php echo $this->text('Any'); ?></option>
            <option value="1"<?php echo $filter_in_database === '1' ? ' selected' : ''; ?>>
              <?php echo $this->text('Yes'); ?>
            </option>
            <option value="0"<?php echo $filter_in_database === '0' ? ' selected' : ''; ?>>
              <?php echo $this->text('No'); ?>
            </option>
          </select>
        </th>
        <th></th>
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
      <?php if ($_filtering && empty($currencies)) { ?>
      <tr>
        <td colspan="9">
          <?php echo $this->text('No results'); ?>
          <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
        </td>
      </tr>
      <?php } ?>
      <?php foreach ($currencies as $code => $currency) { ?>
      <tr>
        <td class="middle"><?php echo $this->text($currency['name']); ?></td>
        <td class="middle"><?php echo $this->e($code); ?></td>
        <td class="middle"><?php echo $this->e($currency['symbol']); ?></td>
        <td class="middle"><?php echo $this->e($currency['conversion_rate']); ?></td>
        <td class="middle">
          <?php if (empty($currency['status'])) { ?>
          <i class="fa fa-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if ($default_currency == $code) { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-square-o"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if (empty($currency['in_database'])) { ?>
          <i class="fa fa-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } ?>
        </td>
        <td class="middle"><?php echo empty($currency['modified']) ? '--' : $this->date($currency['modified']); ?></td>
        <td class="middle">
         <?php if ($this->access('currency_edit')) { ?>
          <a href="<?php echo $this->url("admin/settings/currency/edit/$code"); ?>" title="" class="edit">
            <?php echo $this->lower($this->text('Edit')); ?>
          </a>
          <?php } ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
<?php if(!empty($_pager)) { ?>
<?php echo $_pager; ?>
<?php } ?>
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>
<?php if ($this->access('currency_add')) { ?>
<a class="btn btn-default add" href="<?php echo $this->url('admin/settings/currency/add'); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>