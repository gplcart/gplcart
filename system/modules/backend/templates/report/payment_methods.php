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
<?php if (!empty($methods) || $_filtering) { ?>
<div class="table-responsive">
  <table class="table payment-methods">
    <thead>
      <tr>
        <th><a href="<?php echo $sort_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_title; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_module; ?>"><?php echo $this->text('Module'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i></a></th>
        <th></th>
      </tr>
      <tr class="filters active hidden-no-js">
        <th><input class="form-control" name="id" value="<?php echo $filter_id; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th><input class="form-control" name="title" value="<?php echo $filter_title; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th><input class="form-control" name="title" value="<?php echo $filter_module; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
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
      <?php if ($_filtering && empty($methods)) { ?>
      <tr>
        <td colspan="5">
          <?php echo $this->text('No results'); ?>
          <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
        </td>
      </tr>
      <?php } ?>
      <?php foreach ($methods as $id => $method) { ?>
      <tr>
        <td><?php echo $this->e($id); ?></td>
        <td><?php echo $this->e($method['title']); ?></td>
        <td><?php echo empty($method['module']) ? $this->text('Unknown') : $this->e($method['module']); ?></td>
        <td><?php echo empty($method['status']) ? $this->text('Disabled') : $this->text('Enabled'); ?></td>
        <td><?php echo empty($method['description']) ? '' : $this->e($method['description']); ?></td>
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
<?php } ?>