<?php if ($categories) { ?>
<div class="row">
  <div class="col-md-6">
    <div class="btn-group">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?php echo $this->text('With selected'); ?> <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <?php if ($this->access('category_edit')) { ?>
        <li>
          <a data-action="status" data-action-value="1" href="#">
            <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
          </a>
        </li>
        <li>
          <a data-action="status" data-action-value="0" href="#">
            <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
          </a>
        </li>
        <?php } ?>
        <?php if ($this->access('category_delete')) { ?>
        <li>
          <a data-action="delete" href="#">
            <?php echo $this->text('Delete'); ?>
          </a>
        </li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="col-md-6 text-right">
    <div class="btn-toolbar">
      <?php if ($this->access('category_add')) { ?>
      <a href="<?php echo $this->url("admin/content/category/add/$category_group_id"); ?>" class="btn btn-success add">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
      <?php if ($this->access('import') && $this->access('file_upload')) { ?>
      <a class="btn btn-primary import" href="<?php echo $this->url('admin/tool/import/category'); ?>">
        <i class="fa fa-upload"></i> <?php echo $this->text('Import'); ?>
      </a>
      <?php } ?>
      <?php } ?>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <table class="table table-responsive margin-top-20 categories">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all" value="1"></th>
            <th><?php echo $this->text('Title'); ?></th>
            <th><?php echo $this->text('Alias'); ?></th>
            <th><?php echo $this->text('Enabled'); ?></th>
            <th><?php echo $this->text('Weight'); ?></th>
            <th></th>
          </tr>
        </thead>
      <tbody>
      <?php foreach ($categories as $category) { ?>
        <tr data-category-id="<?php echo $category['category_id']; ?>">
          <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $category['category_id']; ?>"></td>
          <td class="middle"><?php echo $category['indentation']; ?><?php echo $this->escape($category['title']); ?></td>
          <td class="middle">
            <a target="_blank" href="<?php echo $this->escape($category['url']); ?>"><?php echo $this->truncate($this->escape($category['alias'])); ?></a>
          </td>
        <td class="middle">
          <?php if(empty($category['status'])){ ?>
          <i class="fa fa-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } ?>
        </td>
        <td class="middle">
            <i class="fa fa-arrows handle"></i> <span class="weight"><?php echo $this->escape($category['weight']); ?></span>
        </td>
          <td class="text-right">
            <div class="btn-group">
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-bars"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-right">
                <li>
                  <a href="<?php echo $this->escape($category['url']); ?>">
                  <?php echo $this->text('View'); ?>
                  </a>
                </li>
                <?php if ($this->access('category_edit')) { ?>
                <li>
                  <a href="<?php echo $this->url("admin/content/category/edit/$category_group_id/{$category['category_id']}"); ?>">
                  <?php echo $this->text('Edit'); ?>
                  </a>
                </li>
                <?php } ?>
                <?php if ($this->access('category_add')) { ?>
                <li>
                  <a href="<?php echo $this->url("admin/content/category/add/$category_group_id", array('parent_id' => $category['category_id'])); ?>">
                  <?php echo $this->text('Add subcategory'); ?>
                  </a>
                </li>
                <?php } ?>
              </ul>
            </div>
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
    <a href="<?php echo $this->url("admin/content/category/add/$category_group_id"); ?>">
        <?php echo $this->text('Add'); ?>
    </a>
    <?php if ($this->access('import') && $this->access('file_upload')) { ?>
    <a href="<?php echo $this->url('admin/tool/import/category'); ?>">
        <?php echo $this->text('Import'); ?>
    </a>
    <?php } ?>
    <?php } ?>
  </div>
</div>
<?php } ?>