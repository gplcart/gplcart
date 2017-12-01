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
<?php if (!empty($reviews) || $_filtering) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php if ($this->access('review_edit') || $this->access('review_delete') || $this->access('review_add')) { ?>
  <div class="form-inline actions">
    <?php $access_actions = false; ?>
    <?php if ($this->access('review_edit') || $this->access('review_delete')) { ?>
    <?php $access_actions = true; ?>
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="Gplcart.action(this);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <?php if ($this->access('review_edit')) { ?>
        <option value="status|1" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
        </option>
        <option value="status|0" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
        </option>
        <?php } ?>
        <?php if ($this->access('review_delete')) { ?>
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
    <?php if ($this->access('review_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/review/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table reviews">
      <thead>
        <tr>
          <th class="middle"><input type="checkbox" onchange="Gplcart.selectAll(this);"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
          <th class="middle"><a href="<?php echo $sort_review_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th class="middle"><a href="<?php echo $sort_text; ?>"><?php echo $this->text('Text'); ?> <i class="fa fa-sort"></i></a></th>
          <th class="middle"><a href="<?php echo $sort_product_title; ?>"><?php echo $this->text('Product'); ?> <i class="fa fa-sort"></i></a></th>
          <th class="middle"><a href="<?php echo $sort_email_like; ?>"><?php echo $this->text('Author'); ?> <i class="fa fa-sort"></i></a></th>
          <th class="middle"><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Enabled'); ?> <i class="fa fa-sort"></i></a></th>
          <th class="middle"><a href="<?php echo $sort_created; ?>"><?php echo $this->text('Created'); ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
        <tr class="filters active hidden-no-js">
          <th></th>
          <th></th>
          <th class="middle">
            <input class="form-control" name="text" value="<?php echo $filter_text; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th class="middle">
            <input class="form-control product" name="product_title" value="<?php echo $filter_product_title; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th class="middle">
            <input class="form-control" name="email_like" value="<?php echo $filter_email_like; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th class="middle">
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
          <th></th>
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
        <?php if ($_filtering && empty($reviews)) { ?>
        <tr>
          <td colspan="8">
            <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($reviews as $id => $review) { ?>
        <tr data-review-id="<?php echo $id; ?>">
          <td class="middle"><input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>></td>
          <td class="middle"><?php echo $id; ?></td>
          <td class="middle">
            <a href="#review-id-<?php echo $id; ?>" data-toggle="collapse"><?php echo $this->truncate($this->e($review['text']), 30); ?></a>
          </td>
          <td class="middle">
            <?php if (!empty($review['product_id'])) { ?>
            <a target="_blank" href="<?php echo $this->url("product/{$review['product_id']}"); ?>">
              <?php echo $this->truncate($this->e($review['product_title']), 30); ?>
            </a>
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->text('Missing'); ?></span>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (!empty($review['email'])) { ?>
            <?php echo $this->e($review['email']); ?>
            <?php } else { ?>
            <?php echo $this->text('Missing'); ?>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (empty($review['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <?php echo $this->date($review['created']); ?>
          </td>
          <td class="middle">
            <?php if ($this->access('review_edit')) { ?>
            <a title="<?php echo $this->text('Edit'); ?>" href="<?php echo $this->url("admin/content/review/edit/$id"); ?>">
              <?php echo $this->lower($this->text('Edit')); ?>
            </a>
            <?php } ?>
          </td>
        </tr>
        <tr id="review-id-<?php echo $id; ?>" class="collapse active">
          <td colspan="8"><?php echo $this->e($review['text']); ?></td>
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
<?php if ($this->access('review_add')) { ?>
<a class="btn btn-default" href="<?php echo $this->url('admin/content/review/add'); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>