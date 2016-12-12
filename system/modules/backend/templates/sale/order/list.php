<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($orders)) { ?>
<form method="post" id="orders" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
        <?php if ($this->access('order_edit') || $this->access('order_delete')) { ?>
          <div class="btn-group pull-left">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
              <?php echo $this->text('With selected'); ?> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
              <?php if ($this->access('order_edit')) { ?>
              <?php foreach($statuses as $status_id => $status_name) { ?>
              <li>
                <a data-action="status" data-action-value="<?php echo $this->escape($status_id); ?>" data-action-confirm="<?php echo $this->text('Are you sure?'); ?>" href="#">
                  <?php echo $this->text('Status'); ?>: <?php echo $this->escape($status_name); ?>
                </a>
              </li>
              <?php } ?>
              <?php } ?>
              <?php if ($this->access('order_delete')) { ?>
              <li class="divider"></li>
              <li>
                <a data-action="delete" data-action-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>" href="#">
                  <?php echo $this->text('Delete'); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
          </div>
      <?php } ?>
      <?php if ($this->access('order_add')) { ?>
      <a class="btn btn-default pull-right" href="<?php echo $this->url('admin/sale/order/add'); ?>">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
      <?php } ?>
    </div>
    <div class="panel-body table-condensed">
      <table class="table order-list">
        <thead>
          <tr>
            <th>
              <input type="checkbox" id="select-all" value="1">
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
          <tr class="filters active">
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
                <option value=""<?php echo (isset($filter_store_id)) ? '' : ' selected'; ?>><?php echo $this->text('Any'); ?></option>
                <?php foreach ($stores as $store_id => $store) { ?>
                <option value="<?php echo $store_id; ?>"<?php echo (isset($filter_store_id) && (int) $filter_store_id === $store_id) ? ' selected' : ''; ?>><?php echo $this->escape($store); ?></option>
                <?php } ?>
              </select>
            </th>
            <th>
              <select name="status" class="form-control">
                <option value=""<?php echo (isset($filter_status)) ? '' : ' selected'; ?>><?php echo $this->text('Any'); ?></option>
                <?php foreach ($statuses as $status) { ?>
                <option value="<?php echo $status; ?>"<?php echo (isset($filter_status) && $filter_status === $status) ? ' selected' : ''; ?>><?php echo $this->text($status, array(), $status); ?></option>
                <?php } ?>
              </select>
            </th>
            <th><input class="form-control" maxlength="255" name="total" value="<?php echo $filter_total; ?>"></th>
            <th></th>
            <th>
              <div class="btn-group">
                <button type="button" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
                  <i class="fa fa-refresh"></i>
                </button>
                <button type="button" class="btn btn-default filter" title="<?php echo $this->text('Filter'); ?>">
                  <i class="fa fa-search"></i>
                </button>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $id => $order) { ?>
          <tr class="<?php echo empty($order['is_new']) ? '' : 'danger'; ?>" data-order-id="<?php echo $id; ?>">
            <td class="middle">
              <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id; ?>">
            </td>
            <td class="middle"><?php echo $id; ?></td>
            <td class="middle">
            <?php if (is_numeric($order['user_id'])) { ?>
            <?php if ($order['customer_email']) { ?>
            <?php echo $this->truncate($this->escape("{$order['customer_name']} ({$order['customer_email']})")); ?>
            <?php } else { ?>
            <?php echo $this->text('Unknown'); ?>
            <?php } ?>
            <?php } else { ?>
            <?php echo $this->text('Anonymous'); ?>
            <?php } ?>
            </td>
            <td class="middle">
            <?php if ($order['creator']) { ?>
            <?php echo $this->truncate($this->escape($order['creator'])); ?>
            <?php } else { ?>
            <?php echo $this->text('Customer'); ?>
            <?php } ?>
            </td>
            <td class="middle">
              <?php if (isset($stores[$order['store_id']])) { ?>
              <?php echo $this->escape($stores[$order['store_id']]); ?>
              <?php } else { ?>
              <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
              <?php } ?>
            </td>
            <td class="middle">
              <?php if (isset($statuses[$order['status']])) { ?>
              <?php echo $this->escape($statuses[$order['status']]); ?>
              <?php } else { ?>
              <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
              <?php } ?>
            </td>
            <td class="middle"><?php echo $this->escape($order['total_formatted']); ?></td>
            <td class="middle"><?php echo $this->date($order['created']); ?></td>
            <td>
              <ul class="list-inline">
                <li>
                  <a href="<?php echo $this->url("admin/sale/order/$id"); ?>">
                    <?php echo mb_strtolower($this->text('View')); ?>
                  </a>
                </li>
                <?php if ($this->access('order_edit')) { ?>
                <li>
                  <a href="<?php echo $this->url("checkout/edit/$id"); ?>">
                    <?php echo mb_strtolower($this->text('Edit')); ?>
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
    <div class="panel-footer text-right"><?php echo $pager; ?></div>
    <?php } ?>
  </div>
</form>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no orders.'); ?>
    <?php if ($this->access('order_add')) { ?>
    <?php echo $this->text('You can add an order for an <a href="@url">existing user</a>', array('@url' => $this->url('admin/user/list'))); ?>
    <?php } ?>
  </div>
</div>
<?php } ?>