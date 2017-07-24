<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($items)) { ?>
<?php if ($this->access('collection_item_edit') || $this->access('collection_item_delete') || $this->access('collection_item_add')) { ?>
<div class="btn-toolbar actions">
  <?php $access_actions = false; ?>
  <?php if ($this->access('collection_item_edit') || $this->access('collection_item_delete')) { ?>
  <?php $access_actions = true; ?>
  <div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
      <?php echo $this->text('With selected'); ?> <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
      <?php if ($this->access('collection_item_edit')) { ?>
      <li>
        <a data-action="status" data-action-value="1" data-action-confirm="<?php echo $this->text('Are you sure?'); ?>" href="#">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
        </a>
      </li>
      <li>
        <a data-action="status" data-action-value="0" data-action-confirm="<?php echo $this->text('Are you sure?'); ?>" href="#">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
        </a>
      </li>
      <?php } ?>
      <?php if ($this->access('collection_item_delete')) { ?>
      <li>
        <a data-action="delete" data-action-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>" href="#">
          <?php echo $this->text('Delete'); ?>
        </a>
      </li>
      <?php } ?>
    </ul>
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
        <th><input type="checkbox" id="select-all" value="1"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
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
        <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $this->e($item['collection_item']['collection_item_id']); ?>"<?php echo $access_actions ? '' : ' disabled'; ?>></td>
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
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>&nbsp;
<?php if ($this->access('collection_item_add')) { ?>
<a class="btn btn-default" href="<?php echo $this->url("admin/content/collection-item/{$collection['collection_id']}/add"); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>