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
<?php if (!empty($categories)) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php if ($this->access('category_edit') || $this->access('category_delete') || $this->access('category_add')) { ?>
  <div class="form-inline actions">
    <?php $access_actions = false; ?>
    <?php if ($this->access('category_edit') || $this->access('category_delete')) { ?>
    <?php $access_actions = true; ?>
    <div class="input-group">
      <span class="input-group-addon"><?php echo $this->text('With selected'); ?></span>
      <select name="action[name]" class="form-control" onchange="GplCart.action(event);">
        <option value=""><?php echo $this->text('- do action -'); ?></option>
        <?php if ($this->access('category_edit')) { ?>
        <option value="status|1" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
        </option>
        <option value="status|0" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
        </option>
        <?php } ?>
        <?php if ($this->access('category_delete')) { ?>
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
    <?php if ($this->access('category_add')) { ?>
    <a class="btn btn-default add" href="<?php echo $this->url("admin/content/category/add/$category_group_id"); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table categories" data-sortable-weight="true">
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
          <td class="middle"><input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $category['category_id']; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>></td>
          <td class="middle"><?php echo $category['category_id']; ?></td>
          <td class="middle"><?php echo $category['indentation']; ?><a target="_blank" href="<?php echo $this->e($category['url']); ?>"><?php echo $this->truncate($this->e($category['title'])); ?></a></td>
          <td class="middle">
            <?php if (empty($category['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if ($this->access('category_edit')) { ?>
              <i class="fa fa-arrows handle"></i>
            <?php } ?>
            <span class="weight"><?php echo $this->e($category['weight']); ?></span>
          </td>
          <td class="middle">
            <ul class="list-inline">
              <li>
                <a href="<?php echo $this->e($category['url']); ?>">
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
</form>
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>&nbsp;
<?php if ($this->access('category_add')) { ?>
<a class="btn btn-default" href="<?php echo $this->url("admin/content/category/add/$category_group_id"); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>