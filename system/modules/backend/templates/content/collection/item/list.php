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
<?php if (!empty($items)) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php if ($this->access('collection_item_edit') || $this->access('collection_item_delete') || $this->access('collection_item_add')) { ?>
  <div class="form-inline actions">
    <?php $access_actions = false; ?>
    <?php if ($this->access('collection_item_edit') || $this->access('collection_item_delete')) { ?>
    <?php $access_actions = true; ?>
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="Gplcart.action(this);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <?php if ($this->access('collection_item_edit')) { ?>
        <option value="status|1" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
        </option>
        <option value="status|0" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
        </option>
        <?php } ?>
        <?php if ($this->access('collection_item_delete')) { ?>
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
    <?php if ($this->access('collection_item_add')) { ?>
    <a href="<?php echo $this->url("admin/content/collection-item/{$collection['collection_id']}/add"); ?>" class="btn btn-default add">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table collection-items" data-sortable-weight="true">
      <thead>
        <tr>
          <th><input type="checkbox" onchange="Gplcart.selectAll(this);"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
          <th><?php echo $this->text('ID'); ?></th>
          <th><?php echo $this->text('Title'); ?></th>
          <th><?php echo $this->text('Status'); ?></th>
          <th><?php echo $this->text('Entity status'); ?></th>
          <th><?php echo $this->text('Weight'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item) { ?>
        <tr data-id="<?php echo $this->e($item['collection_item']['collection_item_id']); ?>">
          <td class="middle"><input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $this->e($item['collection_item']['collection_item_id']); ?>"<?php echo $access_actions ? '' : ' disabled'; ?>></td>
          <td class="middle"><?php echo $this->truncate($this->e($item['collection_item']['collection_item_id'])); ?></td>
          <td class="middle"><?php echo $this->truncate($this->e($item['title'])); ?></td>
          <td class="middle">
            <?php if (empty($item['collection_item']['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (!isset($item['status'])) { ?>
            <?php echo $this->text('Unknown'); ?>
            <?php } else if (empty($item['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if ($access_actions) { ?>
            <i class="fa fa-arrows handle"></i>
            <?php } ?>
            <span class="weight"><?php echo $item['collection_item']['weight']; ?></span>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</form>
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>&nbsp;
<?php if ($this->access('collection_item_add')) { ?>
<a class="btn btn-default" href="<?php echo $this->url("admin/content/collection-item/{$collection['collection_id']}/add"); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>