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
<?php if (!empty($product_classes)) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php if ($this->access('product_class_edit') || $this->access('product_class_delete') || $this->access('product_class_add')) { ?>
  <div class="form-inline actions">
    <?php $access_actions = false; ?>
    <?php if ($this->access('product_class_edit') || $this->access('product_class_delete')) { ?>
    <?php $access_actions = true; ?>
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="Gplcart.action(this);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <?php if ($this->access('product_class_edit')) { ?>
        <option value="status|1" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
        </option>
        <option value="status|0" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
        </option>
        <?php } ?>
        <?php if ($this->access('product_class_delete')) { ?>
        <option value="delete" data-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>">
          <?php echo $this->text('Delete'); ?>
        </option>
        <?php } ?>
      </select>
      <span class="input-group-btn hidden-js">
        <button class="btn btn-default" name="action[submit]" value="1"><?php echo $this->text('OK'); ?></button>
      </span>
    </div>
    <?php if ($this->access('product_class_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/product-class/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table product-classes">
      <thead>
        <tr>
          <th><input type="checkbox" onchange="Gplcart.selectAll(this);"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
          <th><a href="<?php echo $sort_product_class_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_title; ?>"><?php echo $this->text('Title'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Enabled'); ?> <i class="fa fa-sort"></i></a></th>
          <th><?php echo $this->text('Fields'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($product_classes as $id => $product_class) { ?>
        <tr>
          <td class="middle">
            <input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>>
          </td>
          <td class="middle"><?php echo $id; ?></td>
          <td class="middle"><?php echo $this->e($product_class['title']); ?></td>
          <td class="middle">
            <?php if (empty($product_class['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <?php echo count($product_class['fields']); ?>
          </td>
          <td class="middle">
            <ul class="list-inline">
              <?php if ($this->access('product_class_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/content/product-class/edit/{$product_class['product_class_id']}"); ?>">
                  <?php echo $this->lower($this->text('Edit')); ?>
                </a>
              </li>
              <li>
                <?php if(empty($product_class['fields'])) { ?>
                <span class="text-muted"><?php echo $this->lower($this->text('Fields')); ?></span>
                <?php } else { ?>
                <a href="<?php echo $this->url("admin/content/product-class/field/{$product_class['product_class_id']}"); ?>">
                  <?php echo $this->lower($this->text('Fields')); ?>
                </a>
                <?php } ?>
              </li>
              <li>
                <a href="<?php echo $this->url("admin/content/product-class/field/{$product_class['product_class_id']}/add"); ?>">
                  <?php echo $this->lower($this->text('Add field')); ?>
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
<?php if ($this->access('product_class_add')) { ?>
<a class="btn btn-default" href="<?php echo $this->url('admin/content/product-class/add'); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>