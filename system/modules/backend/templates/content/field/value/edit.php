<form method="post" enctype="multipart/form-data" onsubmit="return confirm();" id="edit-field-value" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="required form-group<?php echo isset($this->errors['title']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Title'); ?></label>
        <div class="col-md-4">
          <input maxlength="255" name="field_value[title]" class="form-control" value="<?php echo (isset($field_value['title'])) ? $this->escape($field_value['title']) : ''; ?>" autofocus>
          <div class="help-block">
            <?php if (isset($this->errors['title'])) { ?>
            <?php echo $this->errors['title']; ?>
            <?php } ?>
            <div class="text-muted"><?php echo $this->text('Required. The title will be displayed to customers on product pages'); ?></div>
          </div>
        </div>
      </div>
      <?php if (!empty($languages)) { ?>
        <?php foreach ($languages as $code => $language) { ?>
        <div class="form-group<?php echo isset($this->errors['translation'][$code]['title']) ? ' has-error' : ''; ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $language['native_name'])); ?></label>
          <div class="col-md-4">
            <input maxlength="255" name="field_value[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($field_value['translation'][$code]['title'])) ? $this->escape($field_value['translation'][$code]['title']) : ''; ?>">
            <div class="help-block">
              <?php if (isset($this->errors['translation'][$code]['title'])) { ?>
              <?php echo $this->errors['translation'][$code]['title']; ?>
              <?php } ?>
              <div class="text-muted">
              <?php echo $this->text('An optional translation for language %name', array('%name' => $language['name'])); ?>
              </div>
            </div>
          </div>
        </div>
        <?php } ?>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group<?php echo isset($this->errors['color']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Color'); ?>
        </label>
        <div class="col-md-3">
          <div class="input-group color">
            <input class="form-control" maxlength="7" name="field_value[color]" value="<?php echo empty($field_value['color']) ? '' : $this->escape($field_value['color']); ?>">
            <span class="input-group-addon"><span class="btn btn-default swatch"></span></span>
          </div>
          <div class="help-block">
            <?php if (isset($this->errors['color'])) { ?>
            <?php echo $this->errors['color']; ?>
            <?php } ?>
            <div class="text-muted">
            <?php echo $this->text('Specify a HEX color code. It\'s applicable only for fields with color widgets'); ?>
            </div>
          </div>
        </div>
      </div>
      <?php if ($this->access('file_upload')) { ?>
      <div class="form-group<?php echo isset($this->errors['file']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Image'); ?>
        </label>
        <div class="col-md-3">
          <input type="file" name="file" accept="image/*" class="form-control">
          <div class="help-block">
           <?php if (isset($this->errors['file'])) { ?>
           <?php echo $this->errors['file']; ?>
           <?php } ?>
            <div class="text-muted">
            <?php echo $this->text('Upload an image. It\'s applicable only for fields with image widgets'); ?>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>
      <?php if (isset($field_value['thumb'])) { ?>
      <div class="form-group">
        <div class="col-md-2 col-md-offset-2">
          <div class="checkbox">
            <label>
              <input type="checkbox" name="delete_image" value="1"> <?php echo $this->text('Delete existing <a target="_blank" href="@href">image</a>', array('@href' => $field_value['thumb'])); ?>
            </label>
          </div>
        </div>
      </div>
      <?php } ?>
      <div class="form-group<?php echo isset($this->errors['weight']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Weight'); ?>
        </label>
        <div class="col-md-3">
          <input maxlength="2" name="field_value[weight]" class="form-control" value="<?php echo (isset($field_value['weight'])) ? $this->escape($field_value['weight']) : 0; ?>">
          <div class="help-block">
            <?php if (isset($this->errors['weight'])) { ?>
            <?php echo $this->errors['weight']; ?>
            <?php } ?>
            <div class="text-muted">
            <?php echo $this->text('Field values are sorted in lists by the weight value. Lower value means higher position'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <?php if (isset($field_value['field_value_id']) && $this->access('field_value_delete')) { ?>
          <button class="btn btn-danger delete" name="delete" value="1">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-4">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url("admin/content/field/value/{$field['field_id']}"); ?>" class="btn btn-default cancel">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('field_value_add') || $this->access('field_value_edit')) { ?>
            <button class="btn btn-default save" name="save" value="1">
              <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
            </button>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>