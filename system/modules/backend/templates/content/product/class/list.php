<?php if (!empty($classes)) { ?>
<div class="panel panel-default">
  <div class="panel-heading clearfix">
    <?php if ($this->access('product_class_edit') || $this->access('product_class_delete')) { ?>
    <div class="btn-group pull-left">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?php echo $this->text('With selected'); ?> <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <?php if ($this->access('product_class_edit')) { ?>
        <li>
          <a data-action="status" data-action-value="1" data-action-confirm="<?php echo $this->text('Do you want to enable selected product classes?'); ?>" href="#">
            <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
          </a>
        </li>
        <li>
          <a data-action="status" data-action-value="0" data-action-confirm="<?php echo $this->text('Do you want to disable selected product classes?'); ?>" href="#">
            <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
          </a>
        </li>
        <?php } ?>
        <?php if ($this->access('product_class_delete')) { ?>
        <li>
          <a data-action="delete" data-action-confirm="<?php echo $this->text('Do you want to delete selected product classes? It cannot be undone!'); ?>" href="#">
            <?php echo $this->text('Delete'); ?>
          </a>
        </li>
        <?php } ?>
      </ul>
    </div>
    <?php } ?>
    <?php if ($this->access('product_class_add')) { ?>    
    <div class="btn-group pull-right">
      <a class="btn btn-default" href="<?php echo $this->url('admin/content/product/class/add'); ?>">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
    </div>
    <?php } ?>  
  </div>
  <div class="panel-body table-responsive">
    <table class="table product-classes table-striped">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"></th>
          <th><a href="<?php echo $sort_title; ?>"><?php echo $this->text('Title'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Enabled'); ?> <i class="fa fa-sort"></i></a></th>
          <th><?php echo $this->text('Actions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($classes as $class) { ?>
        <tr>
          <td class="middle">
            <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $class['product_class_id']; ?>">
          </td>
          <td class="middle"><?php echo $this->escape($class['title']); ?></td>
          <td class="middle">
            <?php if (empty($class['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
              <ul class="list-inline">
                <?php if ($this->access('product_class_edit')) { ?>
                <li>
                  <a href="<?php echo $this->url("admin/content/product/class/edit/{$class['product_class_id']}"); ?>">
                    <?php echo strtolower($this->text('Edit')); ?>
                  </a>
                </li>
                <?php } ?>
                <?php if ($this->access('product_class_field')) { ?>
                <li>
                  <a href="<?php echo $this->url("admin/content/product/class/field/{$class['product_class_id']}"); ?>">
                    <?php echo strtolower($this->text('Fields')); ?>
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
  <div class="panel panel-footer"><?php echo $pager; ?></div>
  <?php } ?>
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no product classes yet'); ?>
    <?php if ($this->access('product_class_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/product/class/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>