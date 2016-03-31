<?php if ($values) {
    ?>
<div class="row">
  <div class="col-md-6">
    <div class="btn-group">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?php echo $this->text('With selected');
    ?> <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <?php if ($this->access('field_value_delete')) {
    ?>
        <li>
          <a data-action="delete" data-action-confirm="<?php echo $this->text('Do you want to delete selected values? It cannot be undone!');
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
    <div class="btn-toolbar">
      <a href="<?php echo $this->url('admin/content/field');
    ?>" class="btn btn-default cancel">
        <i class="fa fa-reply"></i> <?php echo $this->text('Cancel');
    ?>
      </a>
      <?php if ($this->access('field_value_add')) {
    ?>
      <a class="btn btn-success add" href="<?php echo $this->url("admin/content/field/value/{$field['field_id']}/add");
    ?>">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add');
    ?>
      </a>
      <?php if ($this->access('import') && $this->access('file_upload')) {
    ?>
      <a class="btn btn-primary import" href="<?php echo $this->url('admin/tool/import/field_value');
    ?>">
        <i class="fa fa-upload"></i> <?php echo $this->text('Import');
    ?>
      </a>
      <?php 
}
    ?>
      <?php 
}
    ?>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <table class="table margin-top-20 field-values">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"></th>
          <th><a href="<?php echo $sort_title;
    ?>"><?php echo $this->text('Title');
    ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_image;
    ?>"><?php echo $this->text('Image');
    ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_color;
    ?>"><?php echo $this->text('Color');
    ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_weight;
    ?>"><?php echo $this->text('Weight');
    ?> <i class="fa fa-sort"></i></a></th>
          <?php if ($this->access('field_value_edit')) {
    ?>
          <th><?php echo $this->text('Action');
    ?></th>
          <?php 
}
    ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($values as $value) {
    ?>
        <tr data-field-value-id="<?php echo $value['field_value_id'];
    ?>">
          <td class="middle">
            <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $value['field_value_id'];
    ?>">
          </td>
          <td class="middle title"><?php echo $this->escape($value['title']);
    ?></td>
          <td class="middle image">
            <div class="view thumb">
              <?php if (!empty($value['thumb'])) {
    ?>
              <img src="<?php echo $this->escape($value['thumb']);
    ?>">
              <?php 
} else {
    ?>
              <?php echo $this->text('None');
    ?>
              <?php 
}
    ?>
            </div>
          </td>
          <td class="middle color">
          <?php if ($value['color']) {
    ?>
          <div class="btn btn-default" style="background:<?php echo $this->escape($value['color']);
    ?>;"></div>
          <?php 
} else {
    ?>
          <div class="btn btn-default no-color"></div>
          <?php 
}
    ?>
          </td>
          <td class="middle weight"><?php echo (int) $value['weight'];
    ?></td>
          <?php if ($this->access('field_value_edit')) {
    ?>
          <td class="middle">
            <a href="<?php echo $this->url->get("admin/content/field/value/{$value['field_id']}/{$value['field_value_id']}");
    ?>" class="btn btn-default edit"><i class="fa fa-edit"></i></a>
          </td>
          <?php 
}
    ?>
        </tr>
        <?php 
}
    ?>
      </tbody>
    </table>
  </div>
</div>
<div class="row"><div class="col-md-12"><?php echo $pager;
    ?></div></div>
<?php 
} else {
    ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('This field has no values yet');
    ?>
    <?php if ($this->access('field_value_add')) {
    ?>
    <a href="<?php echo $this->url("admin/content/field/value/{$field['field_id']}/add");
    ?>"><?php echo $this->text('Add');
    ?></a>
    <?php 
}
    ?>
  </div>
</div>
<?php 
} ?>