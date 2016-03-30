<?php if ($fields) { ?>
<form method="post" id="product-class-fields" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6 col-md-offset-6 text-right">
      <div class="btn-toolbar">
        <a class="btn btn-default" href="<?php echo $this->url('admin/content/product/class'); ?>">
          <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
        </a>
        <button class="btn btn-primary" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <a class="btn btn-success" href="<?php echo $this->url("admin/content/product/class/field/{$product_class['product_class_id']}/add"); ?>">
          <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
        </a>
      </div>
    </div>
  </div>
  <div class="form-group margin-top-20">
    <div class="col-md-12">
      <table class="table fields">
        <thead>
          <tr>
            <th><?php echo $this->text('Name'); ?></th>
            <th><?php echo $this->text('Required'); ?></th>
            <th><?php echo $this->text('Multiple'); ?></th>
            <th><?php echo $this->text('Weight'); ?></th>
            <th><?php echo $this->text('Delete'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($fields as $field_id => $field) { ?>
          <tr>
            <td class="middle">
              <?php echo $this->escape($field['title']); ?>
              <input type="hidden" name="fields[<?php echo $field_id; ?>][weight]" value="<?php echo $field['weight']; ?>">
            </td>
            <td class="middle">
              <input type="checkbox" name="fields[<?php echo $field_id; ?>][required]" value="1"<?php echo $field['required'] ? ' checked' : ''; ?>>
            </td>
            <td class="middle">
              <input type="checkbox" name="fields[<?php echo $field_id; ?>][multiple]" value="1"<?php echo $field['multiple'] ? ' checked' : ''; ?>>
            </td>
            <td class="middle">
              <i class="fa fa-arrows handle"></i> <?php echo $this->escape($field['weight']); ?>
            </td>
            <td class="middle">
              <a title="<?php echo $this->text('Remove'); ?>" href="#" class="btn btn-default remove">
                <i class="fa fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</form>
<?php } else { ?>
<?php echo $this->text('This product class has no fields yet'); ?>
<a href="<?php echo $this->url("admin/content/product/class/field/{$product_class['product_class_id']}/add"); ?>">
    <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
