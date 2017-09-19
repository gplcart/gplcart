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
<?php if (!empty($products) || $_filtering) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php if ($this->access('product_edit') || $this->access('product_delete') || $this->access('product_add')) { ?>
  <div class="form-inline actions">
    <?php $access_actions = false; ?>
    <?php if ($this->access('product_edit') || $this->access('product_delete')) { ?>
    <?php $access_actions = true; ?>
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="GplCart.action(event);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <?php if ($this->access('product_edit')) { ?>
        <option value="status|1" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
        </option>
        <option value="status|0" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
        </option>
        <?php } ?>
        <?php if ($this->access('product_delete')) { ?>
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
    <?php if ($this->access('product_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/product/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table products">
      <thead>
        <tr>
          <th class="middle"><input type="checkbox" id="select-all" value="1"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
          <th class="middle">
            <a href="<?php echo $sort_product_id; ?>">
              <?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_title; ?>">
              <?php echo $this->text('Title'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_sku_like; ?>">
              <?php echo $this->text('SKU'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_price; ?>">
              <?php echo $this->text('Price'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_currency; ?>">
              <?php echo $this->text('Currency'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_stock; ?>">
              <?php echo $this->text('Stock'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_store_id; ?>">
              <?php echo $this->text('Store'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_status; ?>">
              <?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th></th>
        </tr>
        <tr class="filters active hidden-no-js">
          <th></th>
          <th></th>
          <th class="middle">
            <input class="form-control" placeholder="<?php echo $this->text('Any'); ?>" maxlength="255" name="title" value="<?php echo $filter_title; ?>">
          </th>
          <th class="middle">
            <input class="form-control" placeholder="<?php echo $this->text('Any'); ?>" maxlength="255" name="sku_like" value="<?php echo $filter_sku_like; ?>">
          </th>
          <th class="middle">
            <input class="form-control" placeholder="<?php echo $this->text('Any'); ?>" maxlength="8" name="price" value="<?php echo $filter_price; ?>">
          </th>
          <th class="middle">
            <select class="form-control" name="currency">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach ($currencies as $code => $currency) { ?>
              <option value="<?php echo $this->e($code); ?>"<?php echo isset($filter_currency) && $filter_currency === $code ? ' selected' : ''; ?>><?php echo $this->e($code); ?></option>
              <?php } ?>
            </select>
          </th>
          <th class="middle">
            <input class="form-control" name="stock" placeholder="<?php echo $this->text('Any'); ?>" maxlength="10" value="<?php echo $filter_stock; ?>">
          </th>
          <th class="middle">
            <select class="form-control" name="store_id">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach ($_stores as $store_id => $store) { ?>
              <option value="<?php echo $store_id; ?>"<?php echo isset($filter_store_id) && $filter_store_id == $store_id ? ' selected' : ''; ?>><?php echo $this->e($store['name']); ?></option>
              <?php } ?>
            </select>
          </th>
          <th class="text-center middle">
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
        <?php if (empty($products) && $_filtering) { ?>
        <tr>
          <td colspan="10">
            <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } else { ?>
        <?php foreach ($products as $id => $product) { ?>
        <tr data-product-id="<?php echo $id; ?>">
          <td class="middle">
            <input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>>
          </td>
          <td class="middle"><?php echo $id; ?></td>
          <td class="middle">
            <?php echo $this->truncate($this->e($product['title']), 30); ?>
          </td>
          <td class="middle">
            <?php echo $this->e($product['sku']); ?>
          </td>
          <td class="middle">
            <?php echo $this->e($product['price']); ?>
          </td>
          <td class="middle">
            <?php echo $this->e($product['currency']); ?>
          </td>
          <td class="middle">
            <?php echo $this->e($product['stock']); ?>
          </td>
          <td class="middle">
            <?php if (empty($_stores[$product['store_id']])) { ?>
            <?php echo $this->text('Unknown'); ?>
            <?php } else { ?>
            <?php echo $this->e($_stores[$product['store_id']]['name']); ?>
            <?php } ?>
          </td>
          <td class="middle text-center">
            <?php if (empty($product['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <ul class="list-inline">
              <li>
                <a href="<?php echo $product['url']; ?>">
                  <?php echo $this->lower($this->text('View')); ?>
                </a>
              </li>
              <?php if ($this->access('product_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/product/edit/$id"); ?>">
                  <?php echo $this->lower($this->text('Edit')); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
          </td>
        </tr>
        <?php } ?>
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
<?php if ($this->access('product_add')) { ?>
<a class="btn btn-default" href="<?php echo $this->url('admin/content/product/add'); ?>">
<?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>