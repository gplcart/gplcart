<?php if (!empty($reviews) || $filtering) { ?>
<form method="post" id="reviews" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <?php if ($this->access('review_edit') || $this->access('review_delete')) { ?>
      <div class="btn-group pull-left">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <?php echo $this->text('With selected'); ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <?php if ($this->access('review_edit')) { ?>
          <li>
            <a data-action="status" data-action-value="1" href="#">
              <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?></a>
          </li>
          <li>
            <a data-action="status" data-action-value="0" href="#">
              <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
            </a>
          </li>
          <?php } ?>
          <?php if ($this->access('review_delete')) { ?>
          <li>
            <a data-action="delete" href="#">
              <?php echo $this->text('Delete'); ?>
            </a>
          </li>
          <?php } ?>
        </ul>
      </div>
      <?php } ?> 
      <?php if ($this->access('review_add')) { ?>
      <div class="btn-toolbar pull-right">
        <a class="btn btn-default" href="<?php echo $this->url('admin/content/review/add'); ?>">
          <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
        </a>
      </div>
      <?php } ?>   
    </div>
    <div class="panel-body table-responsive"> 
      <table class="table reviews">
        <thead>
          <tr>
            <th class="middle">
              <input type="checkbox" id="select-all" value="1">
            </th>
            <th class="middle">
              <a href="<?php echo $sort_text; ?>">
                <?php echo $this->text('Text'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th class="middle">
              <a href="<?php echo $sort_product_id; ?>">
                <?php echo $this->text('Product'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th class="middle">
              <a href="<?php echo $sort_user_id; ?>">
                <?php echo $this->text('Author'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th class="middle">
              <a href="<?php echo $sort_status; ?>">
                <?php echo $this->text('Enabled'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th class="middle">
              <a href="<?php echo $sort_created; ?>">
                <?php echo $this->text('Created'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th></th>
          </tr>
          <tr class="filters active">
            <th></th>
            <th class="middle">
              <input class="form-control" name="text" value="<?php echo $filter_text; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th class="middle">
              <input class="form-control" name="product" value="<?php echo $product; ?>" placeholder="<?php echo $this->text('Any'); ?>">
              <input type="hidden" name="product_id" value="<?php echo $filter_product_id; ?>">
            </th>
            <th class="middle">
              <input class="form-control" name="user" value="<?php echo $user; ?>" placeholder="<?php echo $this->text('Any'); ?>">
              <input type="hidden" name="user_id" value="<?php echo $filter_user_id; ?>">
            </th>
            <th class="middle">
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
            <th></th>
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
          <?php if ($filtering && empty($reviews)) { ?>
          <tr>
            <td colspan="7">
              <?php echo $this->text('No results'); ?>
              <a class="clear-filter" href="#"><?php echo $this->text('Reset'); ?></a>
            </td>
          </tr>
          <?php } ?>
          <?php foreach ($reviews as $id => $review) { ?>
          <tr data-review-id="<?php echo $id; ?>">
            <td class="middle">
              <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id; ?>">
            </td>
            <td class="middle">
              <a href="#review-id-<?php echo $id; ?>" data-toggle="collapse"><?php echo $this->truncate($this->escape($review['text']), 30); ?></a>
            </td>
            <td class="middle">
              <?php if ($review['product_id']) { ?>
              <a target="_blank" href="<?php echo $this->url("product/{$review['product_id']}"); ?>">
                <?php echo $this->truncate($this->escape($review['product']), 30); ?>
              </a>
              <?php } else { ?>
              <span class="text-danger"><?php echo $this->text('Missing'); ?></span>
              <?php } ?>
            </td> 
            <td class="middle">
            <?php if ($review['email']) { ?>
            <?php echo $this->escape($review['email']); ?>
            <?php } else { ?>
            <?php echo $this->text('Missing'); ?>
            <?php } ?>
            </td>
            <td class="middle">
              <?php echo!empty($review['status']) ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>'; ?>
            </td>
            <td class="middle">
                <?php echo $this->date($review['created']); ?>
            </td>
            <td class="middle">
              <?php if ($this->access('review_edit')) { ?>
              <a title="<?php echo $this->text('Edit'); ?>" href="<?php echo $this->url("admin/content/review/edit/$id"); ?>">
                <?php echo strtolower($this->text('Edit')); ?>
              </a>
              <?php } ?>
            </td>
          </tr>
          <tr id="review-id-<?php echo $id; ?>" class="collapse active">
            <td colspan="7"><?php echo $this->escape($review['text']); ?></td>
          </tr>
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
    <?php echo $this->text('You have no reviews yet'); ?>
    <?php if ($this->access('review_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/review/add'); ?>"><?php echo $this->text('Add'); ?></a>
    <?php } ?>
  </div>
</div>
<?php } ?>