<?php if ($products || $filtering) {
    ?>
<form method="post" id="products" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token;
    ?>">
  <div class="row">
    <div class="col-md-6">
      <?php if ($this->access('product_edit') || $this->access('product_delete')) {
    ?>
      <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <?php echo $this->text('With selected');
    ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <?php if ($this->access('product_edit')) {
    ?>
          <li>
            <a data-action="status" data-action-value="1" href="#">
              <?php echo $this->text('Status');
    ?>: <?php echo $this->text('Enabled');
    ?>
            </a>
          </li>
          <li>
            <a data-action="status" data-action-value="0" href="#">
              <?php echo $this->text('Status');
    ?>: <?php echo $this->text('Disabled');
    ?>
            </a>
          </li>
          <li>
            <a data-action="front" data-action-value="1" href="#">
              <?php echo $this->text('Front page');
    ?>: <?php echo $this->text('Add');
    ?>
            </a>
          </li>
          <li>
            <a data-action="front" data-action-value="0" href="#">
              <?php echo $this->text('Front page');
    ?>: <?php echo $this->text('Remove');
    ?>
            </a>
          </li>
          <?php 
}
    ?>
          <?php if ($this->access('product_delete')) {
    ?>
          <li>
            <a data-action="delete" href="#">
              <?php echo $this->text('Delete');
    ?>
            </a>
          </li>
          <?php 
}
    ?>
        </ul>
      </div>
      <?php 
}
    ?>
    </div>
    <div class="col-md-6 text-right">
      <?php if ($this->access('product_add')) {
    ?>
      <div class="btn-group">
        <a class="btn btn-success" href="<?php echo $this->url('admin/content/product/add');
    ?>">
          <i class="fa fa-plus"></i> <?php echo $this->text('Add');
    ?>
        </a>
      </div>
      <?php if ($this->access('import') && $this->access('file_upload')) {
    ?>
      <div class="btn-group">
        <a class="btn btn-primary" href="<?php echo $this->url('admin/tool/import/product');
    ?>">
          <i class="fa fa-upload"></i> <?php echo $this->text('Import');
    ?>
        </a>
      </div>
      <?php 
}
    ?>
      <?php 
}
    ?>
    </div>
  </div>
  <div class="row margin-top-20">
    <div class="col-md-12">
      <table class="table table-responsive table-editable products">
        <thead>
          <tr>
            <th class="middle"><input type="checkbox" id="select-all" value="1"></th>
            <th class="middle">
              <a href="<?php echo $sort_title;
    ?>">
                <?php echo $this->text('Title');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th class="middle">
              <a href="<?php echo $sort_sku;
    ?>">
                <?php echo $this->text('SKU');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th class="middle">
              <a href="<?php echo $sort_price;
    ?>">
                <?php echo $this->text('Price');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th class="middle">
              <a href="<?php echo $sort_currency;
    ?>">
                <?php echo $this->text('Currency');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th class="middle">
              <a href="<?php echo $sort_stock;
    ?>">
                <?php echo $this->text('Stock');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th class="middle">
              <a href="<?php echo $sort_store_id;
    ?>">
                <?php echo $this->text('Store');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th class="middle">
              <a href="<?php echo $sort_status;
    ?>">
                <?php echo $this->text('Status');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th class="middle">
              <a href="<?php echo $sort_front;
    ?>">
                <?php echo $this->text('Front');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th></th>
          </tr>
          <tr class="filters active">
            <th></th>
            <th class="middle">
              <input class="form-control" placeholder="<?php echo $this->text('Any');
    ?>" maxlength="255" name="title" value="<?php echo $filter_title;
    ?>">
            </th>
            <th class="middle">
              <input class="form-control" placeholder="<?php echo $this->text('Any');
    ?>" maxlength="255" name="sku" value="<?php echo $filter_sku;
    ?>">
            </th>
            <th class="middle">
              <input class="form-control" placeholder="<?php echo $this->text('Any');
    ?>" maxlength="8" name="price" value="<?php echo $filter_price;
    ?>">
            </th>
            <th class="middle">
              <select class="form-control" name="currency">
                <option value="any"><?php echo $this->text('Any');
    ?></option>
                <?php foreach ($currencies as $code => $currency) {
    ?>
                <option value="<?php echo $this->escape($code);
    ?>"<?php echo (isset($filter_currency) && $filter_currency === $code) ? ' selected' : '';
    ?>><?php echo $this->escape($code);
    ?></option>
                <?php 
}
    ?>
              </select>
            </th>
            <th class="middle">
              <input class="form-control" name="stock" placeholder="<?php echo $this->text('Any');
    ?>" maxlength="10" value="<?php echo $filter_stock;
    ?>">
            </th>
            <th class="middle">
              <select class="form-control" name="store_id">
                <option value="any"><?php echo $this->text('Any');
    ?></option>
                <?php foreach ($stores as $store_id => $store) {
    ?>
                <option value="<?php echo $store_id;
    ?>"<?php echo (isset($filter_store_id) && (int) $filter_store_id === $store_id) ? ' selected' : '';
    ?>><?php echo $this->escape($store);
    ?></option>
                <?php 
}
    ?>
              </select>
            </th>
            <th class="text-center middle">
              <select class="form-control" name="status">
                <option value="any">
                  <?php echo $this->text('Any');
    ?>
                </option>
                <option value="1"<?php echo ($filter_front === '1') ? ' selected' : '';
    ?>>
                  <?php echo $this->text('Enabled');
    ?>
                </option>
                <option value="0"<?php echo ($filter_front === '0') ? ' selected' : '';
    ?>>
                  <?php echo $this->text('Disabled');
    ?>
                </option>
              </select>
            </th>
            <th class="text-center middle">
              <select class="form-control" name="front">
                <option value="any">
                  <?php echo $this->text('Any');
    ?>
                </option>
                <option value="1"<?php echo ($filter_front === '1') ? ' selected' : '';
    ?>>
                    <?php echo $this->text('Yes');
    ?>
                </option>
                <option value="0"<?php echo ($filter_front === '0') ? ' selected' : '';
    ?>>
                  <?php echo $this->text('No');
    ?>
                </option>
              </select>
            </th>
            <th class="middle">
              <button type="button" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter');
    ?>">
                <i class="fa fa-refresh"></i>
              </button>
              <button type="button" class="btn btn-default filter" title="<?php echo $this->text('Filter');
    ?>">
                <i class="fa fa-search"></i>
              </button>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $id => $product) {
    ?>
          <tr data-product-id="<?php echo $id;
    ?>">
            <td class="middle">
                <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id;
    ?>">
                <input type="hidden" name="product[product_id]" value="<?php echo $id;
    ?>">
            </td>
            <td class="middle">
              <span class="hint" title="<?php echo $this->escape($product['title']);
    ?>">
              <?php echo $this->truncate($this->escape($product['title']), 30);
    ?>
              </span>
            </td>
            <td class="middle">
            <?php echo $this->escape($product['sku']);
    ?>
            </td>
            <td class="middle">
              <input name="product[price]" maxlength="8" class="form-control" value="<?php echo $this->escape($product['price']);
    ?>"<?php echo $this->access('product_edit') ? '' : ' disabled';
    ?>>
            </td>
            <td class="middle">
              <?php echo $this->escape($product['currency']);
    ?>
              <input type="hidden" name="product[currency]" value="<?php echo $this->escape($product['currency']);
    ?>">
            </td>
            <td class="middle">
              <input name="product[stock]" maxlength="10" class="form-control" value="<?php echo $this->escape($product['stock']);
    ?>"<?php echo $this->access('product_edit') ? '' : ' disabled';
    ?>>
            </td>
            <td class="middle">
              <select class="form-control" name="product[store_id]"<?php echo $this->access('product_edit') ? '' : ' disabled';
    ?>>
                <?php foreach ($stores as $store_id => $store) {
    ?>
                <option value="<?php echo $store_id;
    ?>"<?php echo ($product['store_id'] == $store_id) ? ' selected' : '';
    ?>><?php echo $this->escape($store);
    ?></option>
                <?php 
}
    ?>
              </select>
            </td>
            <td class="middle text-center">
              <input type="checkbox" name="product[status]" value="1" <?php echo empty($product['status']) ? '' : ' checked';
    ?><?php echo $this->access('product_edit') ? '' : ' disabled';
    ?>>
            </td>
            <td class="middle text-center">
              <input type="checkbox" name="product[front]" value="1" <?php echo empty($product['front']) ? '' : ' checked';
    ?><?php echo $this->access('product_edit') ? '' : ' disabled';
    ?>>
            </td>
            <td class="middle">
              <?php if ($this->access('product_edit')) {
    ?>
              <button type="button" class="btn btn-default save-row disabled">
                <i class="fa fa-floppy-o"></i>
              </button>
              <?php 
}
    ?>
              <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                  <li>
                    <a href="<?php echo $product['view_url'];
    ?>">
                      <?php echo $this->text('View');
    ?>
                    </a>
                  </li>
                  <?php if ($this->access('product_edit')) {
    ?>
                  <li>
                    <a href="<?php echo $this->url("admin/content/product/edit/$id");
    ?>">
                      <?php echo $this->text('Edit');
    ?>
                    </a>
                  </li>
                  <?php 
}
    ?>
                  <li>
                    <a href="#" class="load-options" onclick="return false;">
                      <?php echo $this->text('Options');
    ?>
                    </a>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
          <?php 
}
    ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12"><?php echo $pager;
    ?></div>
  </div>
</form>
<?php 
} else {
    ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no products yet');
    ?>
    <?php if ($this->access('product_add')) {
    ?>
    <a href="<?php echo $this->url('admin/content/product/add');
    ?>"><?php echo $this->text('Add');
    ?></a>
    <?php 
}
    ?>
  </div>
</div>
<?php 
} ?>