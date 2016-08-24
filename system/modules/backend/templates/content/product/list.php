<?php if (!empty($products) || $filtering) { ?>
<form method="post" id="products" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <?php if ($this->access('product_edit') || $this->access('product_delete')) { ?>
      <div class="btn-group pull-left">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <?php echo $this->text('With selected'); ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <?php if ($this->access('product_edit')) { ?>
          <li>
            <a data-action="status" data-action-value="1" href="#">
              <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
            </a>
          </li>
          <li>
            <a data-action="status" data-action-value="0" href="#">
              <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
            </a>
          </li>
          <?php } ?>
          <?php if ($this->access('product_delete')) { ?>
          <li>
            <a data-action="delete" href="#">
              <?php echo $this->text('Delete'); ?>
            </a>
          </li>
          <?php } ?>
        </ul>
      </div>
      <?php } ?>
      <div class="btn-toolbar pull-right">
        <?php if ($this->access('product_add')) { ?>
        <a class="btn btn-default" href="<?php echo $this->url('admin/content/product/add'); ?>">
          <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
        </a>
        <?php } ?>
        <?php if ($this->access('import') && $this->access('file_upload')) { ?>
        <a class="btn btn-default" href="<?php echo $this->url('admin/tool/import/product'); ?>">
          <i class="fa fa-upload"></i> <?php echo $this->text('Import'); ?>
        </a>
        <?php } ?>
      </div>
    </div>
    <div class="panel-body table-responsive">
      <table class="table table-responsive table-striped products">
        <thead>
          <tr>
            <th class="middle"><input type="checkbox" id="select-all" value="1"></th>
            <th class="middle">
              <a href="<?php echo $sort_title; ?>">
                <?php echo $this->text('Title'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th class="middle">
              <a href="<?php echo $sort_sku; ?>">
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
          <tr class="filters active">
            <th></th>
            <th class="middle">
              <input class="form-control" placeholder="<?php echo $this->text('Any'); ?>" maxlength="255" name="title" value="<?php echo $filter_title; ?>">
            </th>
            <th class="middle">
              <input class="form-control" placeholder="<?php echo $this->text('Any'); ?>" maxlength="255" name="sku" value="<?php echo $filter_sku; ?>">
            </th>
            <th class="middle">
              <input class="form-control" placeholder="<?php echo $this->text('Any'); ?>" maxlength="8" name="price" value="<?php echo $filter_price; ?>">
            </th>
            <th class="middle">
              <select class="form-control" name="currency">
                <option value="any"><?php echo $this->text('Any'); ?></option>
                <?php foreach ($currencies as $code => $currency) { ?>
                    <option value="<?php echo $this->escape($code); ?>"<?php echo (isset($filter_currency) && $filter_currency === $code) ? ' selected' : ''; ?>><?php echo $this->escape($code); ?></option>
                <?php } ?>
              </select>
            </th>
            <th class="middle">
              <input class="form-control" name="stock" placeholder="<?php echo $this->text('Any'); ?>" maxlength="10" value="<?php echo $filter_stock; ?>">
            </th>
            <th class="middle">
              <select class="form-control" name="store_id">
                <option value="any"><?php echo $this->text('Any'); ?></option>
                <?php foreach ($stores as $store_id => $store) { ?>
                <option value="<?php echo $store_id; ?>"<?php echo (isset($filter_store_id) && (int) $filter_store_id === $store_id) ? ' selected' : ''; ?>><?php echo $this->escape($store); ?></option>
                <?php } ?>
              </select>
            </th>
            <th class="text-center middle">
              <select class="form-control" name="status">
                <option value="any">
                <?php echo $this->text('Any'); ?>
                </option>
                <option value="1"<?php echo ($filter_status === '1') ? ' selected' : ''; ?>>
                <?php echo $this->text('Enabled'); ?>
                </option>
                <option value="0"<?php echo ($filter_status === '0') ? ' selected' : ''; ?>>
                <?php echo $this->text('Disabled'); ?>
                </option>
              </select>
            </th>
            <th class="middle">
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
          <?php if (empty($products) && $filtering) { ?>
          <tr>
            <td colspan="10">
              <?php echo $this->text('No results'); ?>
              <a class="clear-filter" href="#"><?php echo $this->text('Reset'); ?></a>
            </td>
          </tr>
          <?php } else { ?>
          <?php foreach ($products as $id => $product) { ?>
          <tr data-product-id="<?php echo $id; ?>">
            <td class="middle">
              <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id; ?>">
              <input type="hidden" name="product[product_id]" value="<?php echo $id; ?>">
            </td>
            <td class="middle">
              <span class="hint" title="<?php echo $this->escape($product['title']); ?>">
                <?php echo $this->truncate($this->escape($product['title']), 30); ?>
              </span>
            </td>
            <td class="middle">
              <?php echo $this->escape($product['sku']); ?>
            </td>
            <td class="middle">
              <?php echo $this->escape($product['price']); ?>
            </td>
            <td class="middle">
              <?php echo $this->escape($product['currency']); ?>
            </td>
            <td class="middle">
              <?php echo $this->escape($product['stock']); ?>
            </td>
            <td class="middle">
              <?php if(empty($stores[$product['store_id']])) { ?>
              <?php echo $this->text('Unknown'); ?>
              <?php } else { ?>
              <?php echo $this->escape($stores[$product['store_id']]); ?>
              <?php } ?>
            </td>
            <td class="middle text-center">
              <?php if(empty($product['status'])) { ?>
              <i class="fa fa-square-o"></i>
              <?php } else { ?>
              <i class="fa fa-check-square-o"></i>
              <?php } ?>
            </td>
            <td class="middle">
                <ul class="list-inline">
                  <li>
                    <a href="<?php echo $product['view_url']; ?>">
                      <?php echo $this->text('View'); ?>
                    </a>
                  </li>
                  <?php if ($this->access('product_edit')) { ?>
                  <li>
                    <a href="<?php echo $this->url("admin/content/product/edit/$id"); ?>">
                      <?php echo $this->text('Edit'); ?>
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
    <?php if (!empty($pager)) { ?>
    <div class="panel-footer text-right"><?php echo $pager; ?></div>
    <?php } ?>
  </div>
</form>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no products yet'); ?>
    <?php if ($this->access('product_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/product/add'); ?>"><?php echo $this->text('Add'); ?></a>
    <?php } ?>
  </div>
</div>
<?php } ?>