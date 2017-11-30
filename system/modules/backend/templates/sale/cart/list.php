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
<?php if (!empty($carts) || $_filtering) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php $access_actions = false; ?>
  <?php if ($this->access('cart_delete')) { ?>
  <?php $access_actions = true; ?>
  <div class="form-inline actions">
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="Gplcart.action(this);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <option value="delete" data-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>">
          <?php echo $this->text('Delete'); ?>
        </option>
      </select>
      <span class="input-group-btn hidden-js">
        <button class="btn btn-default" name="action[submit]" value="1"><?php echo $this->text('OK'); ?></button>
      </span>
    </div>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table carts">
      <thead>
        <tr>
          <th><input type="checkbox" onchange="Gplcart.selectAll(this);"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
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
        <tr class="filters active hidden-no-js">
          <th></th>
          <th>
            <input class="form-control" data-autocomplete-source="user" name="user_email" value="<?php echo $filter_user_email; ?>" placeholder="<?php echo $this->text('Any'); ?>">
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
            <input class="form-control" name="sku_like" value="<?php echo $filter_sku_like; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <input class="form-control" name="order_id" value="<?php echo $filter_order_id; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th></th>
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
        <?php if ($_filtering && empty($carts)) { ?>
        <tr>
          <td class="middle" colspan="8">
            <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($carts as $cart_id => $cart) { ?>
        <tr>
          <td class="middle">
            <input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $cart_id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>>
          </td>
          <td class="middle">
            <?php if (empty($cart['user_id'])) { ?>
            <?php echo $this->text('Unknown'); ?>
            <?php } else if (!is_numeric($cart['user_id'])) { ?>
            <?php echo $this->text('Anonymous'); ?>
            <?php } else { ?>
            <a href="<?php echo $this->url("account/{$cart['user_id']}"); ?>"><?php echo $this->e($cart['user_email']); ?></a>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (empty($_stores[$cart['store_id']])) { ?>
            <?php echo $this->text('Unknown'); ?>
            <?php } else { ?>
            <?php echo $this->e($_stores[$cart['store_id']]['name']); ?>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (isset($cart['product_status'])) { ?>
            <a href="<?php echo $cart['url']; ?>"><?php echo $this->e($this->truncate($cart['sku'], 30)); ?></a>
            <?php } else { ?>
            <?php echo $this->e($this->truncate($cart['sku'], 30)); ?>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (empty($cart['order_id'])) { ?>
            <?php echo $this->text('Abandoned / before checkout'); ?>
            <?php } else { ?>
            <a href="<?php echo $this->url("admin/sale/order/{$cart['order_id']}"); ?>"><?php echo $this->e($cart['order_id']); ?></a>
            <?php } ?>
          </td>
          <td class="middle"><?php echo $this->e($cart['quantity']); ?></td>
          <td class="middle"><?php echo $this->date($cart['created']); ?></td>
          <td></td>
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
<?php echo $this->text('There are no items yet'); ?>
<?php } ?>