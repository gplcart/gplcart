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
<?php if (!empty($price_rules) || $_filtering) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php if ($this->access('price_rule_edit') || $this->access('price_rule_delete') || $this->access('price_rule_add')) { ?>
  <div class="form-inline actions">
    <?php $access_actions = false; ?>
    <?php if ($this->access('price_rule_edit') || $this->access('price_rule_delete')) { ?>
    <?php $access_actions = true; ?>
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="GplCart.action(event);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <?php if ($this->access('price_rule_edit')) { ?>
        <option value="status|1" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
        </option>
        <option value="status|0" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
        </option>
        <?php } ?>
        <?php if ($this->access('price_rule_delete')) { ?>
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
    <?php if ($this->access('price_rule_add')) { ?>
    <a class="btn btn-default add" href="<?php echo $this->url('admin/sale/price/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table price-rules">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
          <th>
            <a href="<?php echo $sort_name; ?>">
              <?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i>
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
            <a href="<?php echo $sort_status; ?>">
              <?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th></th>
        </tr>
        <tr class="filters active hidden-no-js">
          <th></th>
          <th>
            <input class="form-control" name="name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th><input class="form-control" name="code" value="<?php echo $filter_code; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
          <th>
            <input class="form-control" name="value" value="<?php echo $filter_value; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <select class="form-control" name="value_type">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <option value="percent"<?php echo $filter_value_type === 'percent' ? ' selected' : ''; ?>>
                <?php echo $this->text('Percent'); ?>
              </option>
              <option value="fixed"<?php echo $filter_value_type === 'fixed' ? ' selected' : ''; ?>>
                <?php echo $this->text('Fixed'); ?>
              </option>
            </select>
          </th>
          <th class="text-center">
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
        <?php if ($_filtering && empty($price_rules)) { ?>
        <tr>
          <td class="middle" colspan="8">
            <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($price_rules as $rule_id => $rule) { ?>
        <tr>
          <td class="middle">
            <input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $rule_id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>>
          </td>
          <td class="middle"><?php echo $this->e($rule['name']); ?></td>
          <td class="middle"><?php echo $this->e($rule['code']); ?></td>
          <td class="middle"><?php echo $this->e($rule['value']); ?></td>
          <td class="middle">
            <?php if ($rule['value_type'] === 'percent') { ?>
            <?php echo $this->text('Percent'); ?>
            <?php } else { ?>
            <?php echo $this->text('Fixed'); ?>
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
                  <?php echo $this->lower($this->text('Edit')); ?>
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
  <?php if (!empty($_pager)) { ?>
  <?php echo $_pager; ?>
  <?php } ?>
</form>
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>&nbsp;
<?php if ($this->access('price_rule_add')) { ?>
<a class="btn btn-default" href="<?php echo $this->url('admin/sale/price/add'); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>