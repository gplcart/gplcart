<form method="post" id="edit-field" onsubmit="return confirm();" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6 col-md-offset-6 text-right">
      <div class="btn-toolbar">
        <?php if (isset($field['field_id']) && $this->access('field_delete')) { ?>
        <button class="btn btn-danger delete" name="delete" value="1">
           <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/content/field'); ?>" class="btn btn-default cancel">
          <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('field_edit') || $this->access('field_add')) { ?>
        <button class="btn btn-primary save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="row margin-top-20">
    <div class="col-md-12">
      <?php if (empty($field['field_id'])) { ?>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Atributes are facts about the products, options are interactive with the customer'); ?>">
          <?php echo $this->text('Type'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <select name="field[type]" class="form-control">
            <?php if (isset($field['type']) && $field['type'] == 'attribute') { ?>
            <option value="attribute" selected><?php echo $this->text('Attribute'); ?></option>
            <option value="option"><?php echo $this->text('Option'); ?></option>
            <?php } else { ?>
            <option value="attribute"><?php echo $this->text('Attribute'); ?></option>
            <option value="option" selected><?php echo $this->text('Option'); ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <?php } ?>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('How to display the field for customers. This is for options only'); ?>">
          <?php echo $this->text('Widget'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <select name="field[widget]" class="form-control">
            <?php foreach ($widget_types as $type => $name) { ?>
            <option value="<?php echo $type; ?>"<?php echo (isset($field['widget']) && $field['widget'] == $type) ? ' selected' : ''; ?>><?php echo $this->escape($name); ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('title', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Name of the field for all users'); ?>">
          <?php echo $this->text('Title'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input maxlength="255" name="field[title]" class="form-control" value="<?php echo (isset($field['title'])) ? $this->escape($field['title']) : ''; ?>">
          <?php if ($this->error('title', true)) { ?>
          <div class="help-block"><?php echo $this->error('title'); ?></div>
          <?php } ?>
        </div>
      </div>
      <?php if ($languages) { ?>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2">
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
            <input maxlength="255" name="field[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($field['translation'][$code]['title'])) ? $this->escape($field['translation'][$code]['title']) : ''; ?>">
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