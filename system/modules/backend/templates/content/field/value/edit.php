<form method="post" enctype="multipart/form-data" onsubmit="return confirm();" id="edit-field-value" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">      
      <div class="required form-group<?php echo isset($this->errors['title']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Title'); ?></label>
        <div class="col-md-4">
          <input maxlength="255" name="field_value[title]" class="form-control" value="<?php echo (isset($field_value['title'])) ? $this->escape($field_value['title']) : ''; ?>" autofocus>
          <?php if (isset($this->errors['title'])) { ?>
          <div class="help-block"><?php echo $this->errors['title']; ?></div>
          <?php } ?>
        </div>
      </div>
      <?php if (!empty($languages)) { ?>
        <?php foreach ($languages as $code => $language) { ?>
        <div class="form-group<?php echo isset($this->errors['translation'][$code]['title']) ? ' has-error' : ''; ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $language['native_name'])); ?></label>
          <div class="col-md-4">
            <input maxlength="255" name="field_value[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($field_value['translation'][$code]['title'])) ? $this->escape($field_value['translation'][$code]['title']) : ''; ?>">
            <?php if (isset($this->errors['translation'][$code]['title'])) { ?>
                <div class="help-block"><?php echo $this->errors['translation'][$code]['title']; ?></div>
            <?php } ?>
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
          <span class="hint" title="<?php echo $this->text('Select a color for the field value. It\'s applicable only for fields with color widgets'); ?>">
            <?php echo $this->text('Color'); ?>
          </span>
        </label>
        <div class="col-md-3">
          <div class="input-group color">
            <input class="form-control" name="field_value[color]" value="<?php echo empty($field_value['color']) ? '' : $this->escape($field_value['color']); ?>">
            <span class="input-group-addon"><span class="btn btn-default swatch"></span></span>
          </div>
          <?php if (isset($this->errors['color'])) { ?>
          <div class="help-block"><?php echo $this->errors['color']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['image']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Upload an image for the field value. It\'s applicable only for fields with image widgets'); ?>">
            <?php echo $this->text('Image'); ?>
          </span>
        </label>
        <div class="col-md-3">
          <?php if ($this->access('file_upload')) { ?>
          <input type="file" name="file" accept="image/*" class="form-control">
          <?php } ?>
          <?php if (isset($this->errors['image'])) { ?>
          <div class="help-block"><?php echo $this->errors['image']; ?></div>
          <?php } ?>
        </div>
      </div>
      <?php if (isset($field_value['thumb'])) { ?>
      <div class="form-group">
        <div class="col-md-2 col-md-offset-2">
          <div class="checkbox">
            <label>
              <input type="checkbox" name="field_value[delete_image]" value="1"> <?php echo $this->text('Delete existing <a target="_blank" href="@href">image</a>', array('@href' => $field_value['thumb'])); ?>
            </label>
          </div>
        </div>
      </div>
      <?php } ?>      
      <div class="form-group<?php echo isset($this->errors['weight']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Weight'); ?>
        </label>
        <div class="col-md-1">
          <input maxlength="2" name="field_value[weight]" class="form-control" value="<?php echo (isset($field_value['weight'])) ? $this->escape($field_value['weight']) : 0; ?>">
          <?php if (isset($this->errors['weight'])) { ?>
          <div class="help-block"><?php echo $this->errors['weight']; ?></div>
          <?php } ?>
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
        <div class="col-md-4 text-right">
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