<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($transactions) || $filtering) { ?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <?php if ($this->access('transaction_delete')) { ?>
      <div class="btn-group pull-left">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <li>
            <a data-action="delete" data-action-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>" href="#">
              <?php echo $this->text('Delete'); ?>
            </a>
          </li>
        </ul>
      </div>
      <?php } ?>
    </div>
    <div class="panel-body table-responsive">
      <table class="table table-condensed transactions">
        <thead>
          <tr>
            <th>
              <input type="checkbox" id="select-all" value="1">
            </th>
            <th>
              <a href="<?php echo $sort_order_id; ?>">
                <?php echo $this->text('Order ID'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_payment_method; ?>">
                <?php echo $this->text('Payment method'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_gateway_transaction_id; ?>">
                <?php echo $this->text('Gateway transaction ID'); ?> <i class="fa fa-sort"></i>
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
            <th>
              <input class="form-control" name="order_id" value="<?php echo $filter_order_id; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th>
              <select name="payment_method" class="form-control">
                <option value="any"><?php echo $this->text('Any'); ?></option>
                <?php foreach ($payment_methods as $method_id => $method) { ?>
                <option value="<?php echo $this->escape($method_id); ?>"<?php echo ($filter_payment_method == $method_id) ? ' selected' : '' ?>>
                  <?php echo $this->escape($method['title']); ?>
                </option>
                <?php } ?>
              </select>
            </th>
            <th>
              <input class="form-control" name="gateway_transaction_id" value="<?php echo $filter_gateway_transaction_id; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th></th>
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
          <?php if ($filtering && empty($transactions)) { ?>
          <tr>
            <td colspan="6">
              <?php echo $this->text('No results'); ?>
              <a href="#" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
            </td>
          </tr>
          <?php } ?>
          <?php foreach ($transactions as $transaction_id => $transaction) { ?>
          <tr>
            <td class="middle">
              <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $transaction_id; ?>">
            </td>
            <td class="middle">
              <a href="<?php echo $this->url("admin/sale/order/{$transaction['order_id']}"); ?>"><?php echo $this->escape($transaction['order_id']); ?></a>
            </td>
            <td class="middle">
              <?php echo isset($payment_methods[$transaction['payment_method']]['title']) ? $this->escape($payment_methods[$transaction['payment_method']]['title']) : $this->text('Unknown'); ?>
            </td>
            <td class="middle">
              <?php echo $this->escape($transaction['gateway_transaction_id']); ?>
            </td>
            <td class="middle">
              <?php echo $this->date($transaction['created']); ?>
            </td>
            <td></td>
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
    <?php echo $this->text('You have no transactions'); ?>
  </div>
</div>
<?php } ?>
