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
<?php if (!empty($orders) || $_filtering) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php if ($this->access('order_edit') || $this->access('order_delete') || $this->access('order_add')) { ?>
  <div class="form-inline actions">
    <?php if ($this->access('order_edit') || $this->access('order_delete')) { ?>
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="GplCart.action(this);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <?php if ($this->access('order_edit')) { ?>
        <optgroup label="<?php echo $this->text('Status'); ?>">
          <?php foreach ($statuses as $status_id => $status_name) { ?>
          <option value="status|<?php echo $status_id; ?>" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
            <?php echo $this->e($status_name); ?>
          </option>
          <?php } ?>
        </optgroup>
        <?php } ?>
        <?php if ($this->access('order_delete')) { ?>
        <option value="delete" data-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>">
          <?php echo $this->text('Delete'); ?>
        </option>
        <?php } ?>
      </select>
      <span class="input-group-btn hidden-js">
        <button class="btn btn-default" name="action[submit]" value="1"><?php echo $this->text('OK'); ?></button>
      </span>
    </div>
    <?php } ?>
    <?php if ($this->access('order_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/user/list'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="table-condensed">
    <table class="table orders">
      <thead>
        <tr>
          <th>
            <input type="checkbox" onchange="GplCart.selectAll(this);">
          </th>
          <th>
            <a href="<?php echo $sort_order_id; ?>">
              <?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_user_id; ?>">
              <?php echo $this->text('Customer'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_creator; ?>">
              <?php echo $this->text('Creator'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_store_id; ?>">
              <?php echo $this->text('Store'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_status; ?>">
              <?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_total; ?>">
              <?php echo $this->text('Amount'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_created; ?>">
              <?php echo $this->text('Created'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th></th>
        </tr>
        <tr class="filters active hidden-no-js">
          <th></th>
          <th></th>
          <th>
            <input class="form-control" maxlength="255" name="customer" value="<?php echo $filter_user_id; ?>">
          </th>
          <th>
            <input class="form-control" maxlength="255" name="creator" value="<?php echo $filter_creator; ?>">
          </th>
          <th>
            <select class="form-control" name="store_id">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach ($_stores as $store_id => $store) { ?>
              <option value="<?php echo $store_id; ?>"<?php echo isset($filter_store_id) && $filter_store_id == $store_id ? ' selected' : ''; ?>><?php echo $this->e($store['name']); ?></option>
              <?php } ?>
            </select>
          </th>
          <th>
            <select name="status" class="form-control">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach ($statuses as $status) { ?>
              <option value="<?php echo $status; ?>"<?php echo isset($filter_status) && $filter_status == $status ? ' selected' : ''; ?>><?php echo $this->text($status); ?></option>
              <?php } ?>
            </select>
          </th>
          <th><input class="form-control" maxlength="255" name="total" value="<?php echo $filter_total; ?>"></th>
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
        <?php if ($_filtering && empty($orders)) { ?>
        <tr>
          <td colspan="9">
            <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($orders as $id => $order) { ?>
        <tr data-order-id="<?php echo $id; ?>">
          <td class="middle">
            <input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $id; ?>">
          </td>
          <td class="middle">
            <?php echo $id; ?>
          </td>
          <td class="middle">
            <?php if (is_numeric($order['user_id'])) { ?>
            <?php if ($order['customer_email']) { ?>
            <?php echo $this->truncate($this->e("{$order['customer_name']} ({$order['customer_email']})")); ?>
            <?php } else { ?>
            <?php echo $this->text('Unknown'); ?>
            <?php } ?>
            <?php } else { ?>
            <?php echo $this->text('Anonymous'); ?>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if ($order['creator']) { ?>
            <?php echo $this->truncate($this->e($order['creator'])); ?>
            <?php } else { ?>
            <?php echo $this->text('Customer'); ?>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (isset($_stores[$order['store_id']])) { ?>
            <?php echo $this->e($_stores[$order['store_id']]['name']); ?>
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (isset($statuses[$order['status']])) { ?>
            <?php echo $this->e($statuses[$order['status']]); ?>
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
            <?php } ?>
          </td>
          <td class="middle"><?php echo $this->e($order['total_formatted']); ?></td>
          <td class="middle">
            <?php echo $this->date($order['created']); ?>
            <?php if ($order['is_new']) { ?>
            <span class="label label-danger"><?php echo $this->text('new'); ?></span>
            <?php } ?>
          </td>
          <td>
            <ul class="list-inline">
              <li>
                <a href="<?php echo $this->url("admin/sale/order/$id"); ?>">
                  <?php echo $this->lower($this->text('View')); ?>
                </a>
              </li>
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
<?php echo $this->text('There are no items yet'); ?>&nbsp;
<?php if ($this->access('order_add') && $this->access('user')) { ?>
<?php echo $this->text('You can add an order for an <a href="@url">existing user</a>', array('@url' => $this->url('admin/user/list'))); ?>
<?php } ?>
<?php } ?>