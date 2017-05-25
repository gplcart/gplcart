<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($categories)) { ?>
<div class="panel panel-default">
  <div class="panel-heading clearfix">
    <div class="btn-group pull-left">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
         <span class="caret"></span>
      </button>
      <?php $access_actions = false; $access_action_edit = false; ?>
      <?php if ($this->access('category_edit') || $this->access('category_delete')) { ?>
      <?php $access_actions = true; ?>
      <ul class="dropdown-menu">
        <?php if ($this->access('category_edit')) { ?>
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
        <?php if ($this->access('category_delete')) { ?>
        <li>
          <a data-action="delete" data-action-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>" href="#">
            <?php echo $this->text('Delete'); ?>
          </a>
        </li>
        <?php } ?>
      </ul>
      <?php } ?>
    </div>
    <div class="btn-toolbar pull-right">
      <?php if ($this->access('category_add')) { ?>
      <a href="<?php echo $this->url("admin/content/category/add/$category_group_id"); ?>" class="btn btn-default add">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
      <?php } ?>
    </div>
  </div>
  <div class="panel-body table-responsive">
    <table class="table table-condensed categories" data-sortable-weight="true">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
          <th><?php echo $this->text('ID'); ?></th>
          <th><?php echo $this->text('Title'); ?></th>
          <th><?php echo $this->text('Enabled'); ?></th>
          <th><?php echo $this->text('Weight'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $category) { ?>
        <tr data-id="<?php echo $category['category_id']; ?>">
          <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $category['category_id']; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>></td>
          <td class="middle"><?php echo $category['category_id']; ?></td>
          <td class="middle"><?php echo $category['indentation']; ?><a target="_blank" href="<?php echo $this->escape($category['url']); ?>"><?php echo $this->truncate($this->escape($category['title'])); ?></a></td>
          <td class="middle">
            <?php if (empty($category['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if($this->access('category_edit')) { ?>
            <i class="fa fa-arrows handle"></i>
            <?php } ?>
            <span class="weight"><?php echo $this->escape($category['weight']); ?></span>
          </td>
          <td class="middle">
              <ul class="list-inline">
                <li>
                  <a href="<?php echo $this->escape($category['url']); ?>">
                    <?php echo $this->lower($this->text('View')); ?>
                  </a>
                </li>
                <?php if ($this->access('category_edit')) { ?>
                <li>
                  <a href="<?php echo $this->url("admin/content/category/edit/$category_group_id/{$category['category_id']}"); ?>">
                    <?php echo $this->lower($this->text('Edit')); ?>
                  </a>
                </li>
                <?php } ?>
                <?php if ($this->access('category_add')) { ?>
                <li>
                  <a href="<?php echo $this->url("admin/content/category/add/$category_group_id", array('parent_id' => $category['category_id'])); ?>">
                    <?php echo $this->lower($this->text('Add subcategory')); ?>
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
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('This category group has no categories yet'); ?>
    <?php if ($this->access('category_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url("admin/content/category/add/$category_group_id"); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>