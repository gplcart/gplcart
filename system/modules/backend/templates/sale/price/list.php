<?php if (!empty($price_rules) || $filtering) { ?>
<form method="post" id="price-rules" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <div class="btn-group pull-left">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <?php echo $this->text('With selected'); ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <?php if ($this->access('price_rule_edit')) { ?>
          <li>
            <a data-action="status" data-action-value="1" data-action-confirm="<?php echo $this->text('Do you want to enable selected price rules?'); ?>" href="#">
              <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
            </a>
          </li>
          <li>
            <a data-action="status" data-action-value="0" data-action-confirm="<?php echo $this->text('Do you want to disable selected price rules?'); ?>" href="#">
              <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
            </a>
          </li>
          <?php } ?>
          <?php if ($this->access('price_rule_delete')) { ?>
          <li>
            <a data-action="delete" data-action-confirm="<?php echo $this->text('Do you want to delete selected price rules? It cannot be undone!'); ?>" href="#">
              <?php echo $this->text('Delete'); ?>
            </a>
          </li>
          <?php } ?>
        </ul>
      </div>
      <?php if ($this->access('price_rule_add')) { ?>
      <div class="btn-toolbar pull-right">
        <a href="<?php echo $this->url('admin/sale/price/add'); ?>" class="btn btn-default add">
          <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
        </a>
      </div>
      <?php } ?>
    </div>
    <div class="panel-body table-responsive">
      <table class="table table-striped price-rules">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all" value="1"></th>
            <th>
              <a href="<?php echo $sort_name; ?>">
                <?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_type; ?>">
                <?php echo $this->text('Type'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_code; ?>">
                <?php echo $this->text('Code'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_value; ?>">
                <?php echo $this->text('Value'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_value_type; ?>">
                <?php echo $this->text('Value type'); ?> <i class="fa fa-sort"></i>
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
            <th></th>
          </tr>
          <tr class="filters active">
            <th></th>
            <th>
              <input class="form-control" name="name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th>
              <select class="form-control" name="type">
                <option value="">
                <?php echo $this->text('Any'); ?>
                </option>
                <option value="order"<?php echo ($filter_type == 'order') ? ' selected' : ''; ?>>
                <?php echo $this->text('Order'); ?>
                </option>
                <option value="catalog"<?php echo ($filter_type == 'catalog') ? ' selected' : ''; ?>>
                <?php echo $this->text('Catalog'); ?>
                </option>
              </select>
            </th>
            <th><input class="form-control" name="code" value="<?php echo $filter_code; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
            <th>
              <input class="form-control" name="value" pattern="\d*" type="number" min="0" step="any" maxlength="32" value="<?php echo $filter_value; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th>
              <select class="form-control" name="value_type">
                <option value="">
                <?php echo $this->text('Any'); ?>
                </option>
                <option value="percent"<?php echo ($filter_value_type == 'percent') ? ' selected' : ''; ?>>
                <?php echo $this->text('Percent'); ?>
                </option>
                <option value="fixed"<?php echo ($filter_value_type == 'fixed') ? ' selected' : ''; ?>>
                <?php echo $this->text('Fixed'); ?>
                </option>
              </select>
            </th>
            <th>
              <select class="form-control" name="store_id">
                <option value=""<?php echo isset($filter_store_id) ? '' : ' selected'; ?>>
                <?php echo $this->text('Any'); ?>
                </option>
                <?php foreach ($stores as $store_id => $store) { ?>
                <option value="<?php echo $store_id; ?>"<?php echo ($filter_store_id == $store_id) ? ' selected' : ''; ?>>
                <?php echo $this->escape($store); ?>
                </option>
                <?php } ?>
              </select>
            </th>
            <th class="text-center">
              <select class="form-control" name="status">
                <option value="any"<?php echo ($filter_status === 'any') ? ' selected' : ''; ?>>
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
          <?php if ($filtering && empty($price_rules)) { ?>
           <tr>
             <td class="middle" colspan="10">
               <?php echo $this->text('No results'); ?>
               <a href="#" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
             </td>
           </tr>
          <?php } ?>
          <?php foreach ($price_rules as $rule_id => $rule) { ?>
          <tr>
            <td class="middle">
              <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $rule_id; ?>">
              <input type="hidden" value="<?php echo $rule_id; ?>" name="price_rule[price_rule_id]">
              <input type="hidden" value="<?php echo $this->escape($rule['currency']); ?>" name="price_rule[currency]">
            </td>
            <td class="middle"><?php echo $this->escape($rule['name']); ?></td>
            <td class="middle"><?php echo $this->text($rule['type']); ?></td>
            <td class="middle"><?php echo $this->escape($rule['code']); ?></td>
            <td class="middle"><?php echo $this->escape($rule['value']); ?></td>
            <td class="middle">
              <?php if ($rule['value_type'] == 'percent') { ?>
              <?php echo $this->text('Percent'); ?>
              <?php } else { ?>
              <?php echo $this->text('Fixed'); ?>
              <?php } ?>
            </td>
            <td class="middle">
              <?php if (isset($stores[$rule['store_id']])) { ?>
              <?php echo $this->escape($stores[$rule['store_id']]); ?>
              <?php } else { ?>
              <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
              <?php } ?>
            </td>
            <td class="middle">
              <?php if (empty($rule['status'])) { ?>
              <i class="fa fa-square-o"></i>
              <?php } else { ?>
              <i class="fa fa-check-square-o"></i>
              <?php } ?>
            </td>
            <td>
            <ul class="list-inline">
              <?php if ($this->access('price_rule_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/sale/price/edit/$rule_id"); ?>">
                  <?php echo strtolower($this->text('Edit')); ?>
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
    <div class="panel-footer"><?php echo $pager; ?></div>
    <?php } ?>
  </div>
</form>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no price rules yet'); ?>
    <?php if ($this->access('price_rule_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/sale/price/add'); ?>"><?php echo $this->text('Add'); ?></a>
    <?php } ?>
  </div>
</div>
<?php } ?>