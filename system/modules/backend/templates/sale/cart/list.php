<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($carts) || $filtering) { ?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <?php $access_actions = false; ?>
      <?php if ($this->access('cart_delete')) { ?>
      <?php $access_actions = true; ?>
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
      <table class="table table-condensed carts">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all" value="1"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
            <th>
              <a href="<?php echo $sort_user_id; ?>">
                <?php echo $this->text('User'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_store_id; ?>">
                <?php echo $this->text('Store'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_sku; ?>">
                <?php echo $this->text('SKU'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_order_id; ?>">
                <?php echo $this->text('Order'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_quantity; ?>">
                <?php echo $this->text('Quantity'); ?> <i class="fa fa-sort"></i>
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
              <input class="form-control" data-autocomplete-source="user" name="user_email" value="<?php echo $filter_user_email; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th>
              <select class="form-control" name="store_id">
                <option value="any"><?php echo $this->text('Any'); ?></option>
                <?php foreach ($stores as $store_id => $store) { ?>
                <option value="<?php echo $store_id; ?>"<?php echo (isset($filter_store_id) && (int) $filter_store_id === $store_id) ? ' selected' : ''; ?>><?php echo $this->escape($store); ?></option>
                <?php } ?>
              </select>
            </th>
            <th>
              <input class="form-control" name="sku" value="<?php echo $filter_sku; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th>
              <input class="form-control" name="order_id" value="<?php echo $filter_order_id; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th></th>
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
          <?php if ($filtering && empty($carts)) { ?>
          <tr>
            <td class="middle" colspan="8">
              <?php echo $this->text('No results'); ?>
              <a href="#" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
            </td>
          </tr>
          <?php } ?>
          <?php foreach ($carts as $cart_id => $cart) { ?>
          <tr>
            <td class="middle">
              <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $cart_id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>>
            </td>
            <td class="middle">
              <?php if (empty($cart['user_id'])) { ?>
              <?php echo $this->text('Unknown'); ?>
              <?php } else if (!is_numeric($cart['user_id'])) { ?>
              <?php echo $this->text('Anonymous'); ?>
              <?php } else { ?>
              <a href="<?php echo $this->url("account/{$cart['user_id']}"); ?>"><?php echo $this->escape($cart['user_email']); ?></a>
              <?php } ?>
            </td>
            <td class="middle">
              <?php if(empty($stores[$cart['store_id']])) { ?>
              <?php echo $this->text('Unknown'); ?>
              <?php } else { ?>
              <?php echo $this->escape($stores[$cart['store_id']]); ?>
              <?php } ?>
            </td>
            <td class="middle">
              <?php if(isset($cart['product_status'])) { ?>
              <a href="<?php echo $cart['url']; ?>"><?php echo $this->escape($this->truncate($cart['sku'], 30)); ?></a>
              <?php } else { ?>
              <?php echo $this->escape($this->truncate($cart['sku'], 30)); ?>
              <?php } ?>
            </td>
            <td class="middle">
              <?php if (empty($cart['order_id'])) { ?>
              <?php echo $this->text('Abandoned / before checkout'); ?>
              <?php } else { ?>
              <a href="<?php echo $this->url("admin/sale/order/{$cart['order_id']}"); ?>"><?php echo $this->escape($cart['order_id']); ?></a>
              <?php } ?>
            </td>
            <td class="middle"><?php echo $this->escape($cart['quantity']); ?></td>
            <td class="middle"><?php echo $this->date($cart['created']); ?></td>
            <td></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <?php if (!empty($pager)) { ?>
    <div class="panel-footer"><?php echo $pager; ?></div>
    <?php } ?>
  </div>
</form>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no cart items yet'); ?>
  </div>
</div>
<?php } ?>