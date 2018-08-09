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
<?php if (!empty($routes) || $_filtering) { ?>
<div class="table-responsive">
  <table class="table routes">
    <thead>
      <tr>
        <th><a href="<?php echo $sort_pattern; ?>"><?php echo $this->text('Pattern'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_access; ?>"><?php echo $this->text('Access'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_internal; ?>"><?php echo $this->text('Internal'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i></a></th>
      </tr>
      <tr class="filters active hidden-no-js">
        <th>
          <input class="form-control" name="pattern" value="<?php echo $filter_pattern; ?>" placeholder="<?php echo $this->text('Any'); ?>">
        </th>
        <th>
          <select class="form-control" name="access">
            <option value=""><?php echo $this->text('Any'); ?></option>
            <?php foreach($permissions as $permission_id => $permission_name) { ?>
            <option value="<?php echo $permission_id; ?>"<?php echo $permission_id === $filter_access ? ' selected' : ''; ?>><?php echo $this->text($permission_name); ?></option>
            <?php } ?>
          </select>
        </th>
        <th>
          <select class="form-control" name="internal">
            <option value=""><?php echo $this->text('Any'); ?></option>
            <option value="1"<?php echo $filter_internal === '1' ? ' selected' : ''; ?>>
              <?php echo $this->text('Yes'); ?>
            </option>
            <option value="0"<?php echo $filter_internal === '0' ? ' selected' : ''; ?>>
              <?php echo $this->text('No'); ?>
            </option>
          </select>
        </th>
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
          <a href="<?php echo $this->url($_path); ?>" class="btn clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
            <i class="fa fa-sync"></i>
          </a>
          <button class="btn filter" title="<?php echo $this->text('Filter'); ?>">
            <i class="fa fa-search"></i>
          </button>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if ($_filtering && empty($routes)) { ?>
      <tr>
        <td colspan="5">
          <?php echo $this->text('No results'); ?>
          <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
        </td>
      </tr>
      <?php } ?>
      <?php foreach ($routes as $pattern => $route) { ?>
      <tr>
        <td><?php echo $this->e($pattern); ?></td>
        <td><?php echo $this->e($route['access_name']); ?></td>
        <td class="middle">
          <?php if (empty($route['internal'])) { ?>
          <i class="fa fa-square"></i>
          <?php } else { ?>
          <i class="fa fa-check-square"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if (empty($route['status'])) { ?>
          <i class="fa fa-square"></i>
          <?php } else { ?>
          <i class="fa fa-check-square"></i>
          <?php } ?>
        </td>
        <td></td>
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