<?php if ($classes) {
    ?>
<div class="row">
  <div class="col-md-6">
    <div class="btn-group">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?php echo $this->text('With selected');
    ?> <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <?php if ($this->access('product_class_edit')) {
    ?>
        <li>
          <a data-action="status" data-action-value="1" data-action-confirm="<?php echo $this->text('Do you want to enable selected product classes?');
    ?>" href="#">
          <?php echo $this->text('Status');
    ?>: <?php echo $this->text('Enabled');
    ?>
          </a>
        </li>
        <li>
          <a data-action="status" data-action-value="0" data-action-confirm="<?php echo $this->text('Do you want to disable selected product classes?');
    ?>" href="#">
          <?php echo $this->text('Status');
    ?>: <?php echo $this->text('Disabled');
    ?>
          </a>
        </li>
        <?php 
}
    ?>
        <?php if ($this->access('product_class_delete')) {
    ?>
        <li>
          <a data-action="delete" data-action-confirm="<?php echo $this->text('Do you want to delete selected product classes? It cannot be undone!');
    ?>" href="#">
          <?php echo $this->text('Delete');
    ?>
          </a>
        </li>
        <?php 
}
    ?>
      </ul>
    </div>
  </div>
  <div class="col-md-6 text-right">
    <div class="btn-group">
      <?php if ($this->access('product_class_add')) {
    ?>
      <a class="btn btn-success" href="<?php echo $this->url('admin/content/product/class/add');
    ?>">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add');
    ?>
      </a>
      <?php 
}
    ?>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <table class="table product-classes margin-top-20">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"></th>
          <th><a href="<?php echo $sort_title;
    ?>"><?php echo $this->text('Title');
    ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_status;
    ?>"><?php echo $this->text('Enabled');
    ?> <i class="fa fa-sort"></i></a></th>
          <th><?php echo $this->text('Action');
    ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($classes as $class) {
    ?>
        <tr>
          <td class="middle">
            <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $class['product_class_id'];
    ?>">
          </td>
          <td class="middle"><?php echo $this->escape($class['title']);
    ?></td>
          <td class="middle">
            <?php if (empty($class['status'])) {
    ?>
            <i class="fa fa-square-o"></i>
            <?php 
} else {
    ?>
            <i class="fa fa-check-square-o"></i>
            <?php 
}
    ?>
          </td>
          <td class="middle">
            <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                  <?php if ($this->access('product_class_edit')) {
    ?>
                  <li>
                    <a href="<?php echo $this->url("admin/content/product/class/edit/{$class['product_class_id']}");
    ?>">
                      <?php echo $this->text('Edit');
    ?>
                    </a>
                  </li>
                  <?php 
}
    ?>
                  <?php if ($this->access('product_class_field')) {
    ?>
                  <li>
                    <a href="<?php echo $this->url("admin/content/product/class/field/{$class['product_class_id']}");
    ?>">
                      <?php echo $this->text('Fields');
    ?>
                    </a>
                  </li>
                  <?php 
}
    ?>
                </ul>
            </div>
          </td>
        </tr>
        <?php 
}
    ?>
      </tbody>
    </table>
  </div>
</div>
<div class="row">
  <div class="col-md-12"><?php echo $pager;
    ?></div>
</div>
<?php 
} else {
    ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no product classes yet');
    ?>
    <?php if ($this->access('product_class_add')) {
    ?>
    <a href="<?php echo $this->url('admin/content/product/class/add');
    ?>"><?php echo $this->text('Add');
    ?></a>
    <?php 
}
    ?>
  </div>
</div>
<?php 
} ?>