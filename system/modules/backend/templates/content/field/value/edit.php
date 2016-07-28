<form method="post" enctype="multipart/form-data" onsubmit="return confirm();" id="edit-field-value" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6 col-md-offset-6 text-right">
      <div class="btn-toolbar">
        <?php if (isset($field_value['field_value_id']) && $this->access('field_value_delete')) { ?>
          <button class="btn btn-danger delete" name="delete" value="1">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
        <?php } ?>
        <a href="<?php echo $this->url("admin/content/field/value/{$field['field_id']}"); ?>" class="btn btn-default cancel">
          <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('field_value_add') || $this->access('field_value_edit')) { ?>
        <button class="btn btn-primary save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="row margin-top-20">
    <div class="col-md-12">
      <div class="required form-group<?php echo $this->error('title', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Name of the value for all users'); ?>">
          <?php echo $this->text('Title'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input maxlength="255" name="field_value[title]" class="form-control" value="<?php echo (isset($field_value['title'])) ? $this->escape($field_value['title']) : ''; ?>">
          <?php if ($this->error('title', true)) { ?>
          <div class="help-block"><?php echo $this->error('title'); ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('color', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Select a color for the field value. It\'s applicable only for fields with color widgets'); ?>">
          <?php echo $this->text('Color'); ?>
          </span>
        </label>
        <div class="col-md-3">
          <div class="input-group color">
            <input class="form-control" name="field_value[color]" value="<?php echo empty($field_value['color']) ? '' : $this->escape($field_value['color']); ?>">
            <span class="input-group-addon"><i></i></span>
          </div>
          <?php if ($this->error('color', true)) { ?>
          <div class="help-block"><?php echo $this->error('color'); ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('image', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Upload an image for the field value. It\'s applicable only for fields with image widgets'); ?>">
            <?php echo $this->text('Image'); ?>
          </span>
        </label>
        <div class="col-md-3">
          <?php if ($this->access('file_upload')) { ?>
          <input type="file" name="file" accept="image/*" class="form-control">
          <?php } ?>
          <input type="hidden" name="field_value[path]" value="<?php echo isset($field_value['path']) ? $this->escape($field_value['path']) : ''; ?>">
          <?php if ($this->error('image', true)) { ?>
          <div class="help-block"><?php echo $this->error('image'); ?></div>
          <?php } ?>
        </div>
      </div>
      <?php if (isset($field_value['thumb'])) { ?>
      <div class="form-group">
        <div class="col-md-2 col-md-offset-2">
          <img class="img-responsive" src="<?php echo $this->escape($field_value['thumb']); ?>">
        </div>
      </div>
      <?php } ?>
      <div class="form-group<?php echo $this->error('weight', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Weight'); ?>
        </label>
        <div class="col-md-1">
          <input maxlength="2" name="field_value[weight]" class="form-control" value="<?php echo (isset($field_value['weight'])) ? $this->escape($field_value['weight']) : 0; ?>">
          <?php if ($this->error('weight', true)) { ?>
          <div class="help-block"><?php echo $this->error('weight'); ?></div>
          <?php } ?>
        </div>
      </div>
      <?php if ($languages) { ?>
      <div class="form-group">
        <div class="col-md-6 col-md-offset-2">
          <a data-toggle="collapse" href="#translations">
            <?php echo $this->text('Translations'); ?> <span class="caret"></span>
          </a>
        </div>
      </div>
      <div id="translations" class="collapse translations<?php echo $this->error(null, ' in'); ?>">
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
      </div>
      <?php } ?>
    </div>
  </div>
</form>