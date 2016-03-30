<form method="post" id="edit-language" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6 col-md-offset-6 text-right">
      <div class="btn-toolbar">
        <?php if (isset($language['code']) && $this->access('language_delete')) { ?>
        <button class="btn btn-danger delete" name="delete" value="1">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/settings/language'); ?>" class="btn btn-default cancel"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('language_edit') || $this->access('language_add')) { ?>
        <button class="btn btn-primary save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo isset($form_errors['status']) ? ' has-error' : ''; ?>">
    <label class="col-md-2 control-label">
      <span class="hint" title="<?php echo $this->text('Only enabled languages will be shown to users'); ?>">
      <?php echo $this->text('Status'); ?>
      </span>
    </label>
    <div class="col-md-6">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo!empty($language['status']) ? ' active' : ''; ?>">
          <input name="language[status]" type="radio" autocomplete="off" value="1"<?php echo!empty($language['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-default<?php echo empty($language['status']) ? ' active' : ''; ?>">
          <input name="language[status]" type="radio" autocomplete="off" value="0"<?php echo empty($language['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <?php if (isset($form_errors['status'])) { ?>
      <div class="help-block"><?php echo $form_errors['status']; ?></div>
      <?php } ?>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Default'); ?></label>
    <div class="col-md-6">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo!empty($language['default']) ? ' active' : ''; ?>">
          <input name="language[default]" type="radio" autocomplete="off" value="1"<?php echo!empty($language['default']) ? ' checked' : ''; ?>><?php echo $this->text('Yes'); ?>
        </label>
        <label class="btn btn-default<?php echo empty($language['default']) ? ' active' : ''; ?>">
          <input name="language[default]" type="radio" autocomplete="off" value="0"<?php echo empty($language['default']) ? ' checked' : ''; ?>><?php echo $this->text('No'); ?>
        </label>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo isset($form_errors['code']) ? ' has-error' : ''; ?>">
    <label class="col-md-2 control-label">
      <span class="hint" title="<?php echo $this->text('ISO 639-1 language code, culture names also accepted'); ?>">
      <?php echo $this->text('Code'); ?>
      </span>
    </label>
    <div class="col-md-1">
      <input name="language[code]" maxlength="2" class="form-control" value="<?php echo isset($language['code']) ? $this->escape($language['code']) : ''; ?>" required>
    </div>
    <?php if (isset($form_errors['code'])) { ?>
    <div class="help-block col-md-6"><?php echo $form_errors['code']; ?></div>
    <?php } ?>
  </div>
  <div class="form-group required<?php echo isset($form_errors['name']) ? ' has-error' : ''; ?>">
    <label class="col-md-2 control-label">
      <span class="hint" title="<?php echo $this->text('International name of the language in english according to ISO 639'); ?>">
      <?php echo $this->text('Name'); ?>
      </span>
    </label>
    <div class="col-md-4">
      <input name="language[name]" class="form-control" maxlength="32" value="<?php echo isset($language['name']) ? $this->escape($language['name']) : ''; ?>" required>
      <?php if (isset($form_errors['name'])) { ?>
      <div class="help-block"><?php echo $form_errors['name']; ?></div>
      <?php } ?>
    </div>
  </div>
  <div class="form-group required<?php echo isset($form_errors['native_name']) ? ' has-error' : ''; ?>">
    <label class="col-md-2 control-label">
      <span class="hint" title="<?php echo $this->text('Local name of the language'); ?>">
      <?php echo $this->text('Native name'); ?>
      </span>
    </label>
    <div class="col-md-4">
      <input name="language[native_name]" maxlength="255" class="form-control" value="<?php echo isset($language['native_name']) ? $this->escape($language['native_name']) : ''; ?>" required>
      <?php if (isset($form_errors['native_name'])) { ?>
      <div class="help-block"><?php echo $form_errors['native_name']; ?></div>
      <?php } ?>
    </div>
  </div>
  <div class="form-group required<?php echo isset($form_errors['weight']) ? ' has-error' : ''; ?>">
    <label class="col-md-2 control-label">
      <span class="hint" title="<?php echo $this->text('Items are displayed to users in ascending order by weight'); ?>">
      <?php echo $this->text('Weight'); ?>
      </span>
    </label>
    <div class="col-md-1">
      <input name="language[weight]" maxlength="2" class="form-control" value="<?php echo isset($language['weight']) ? $this->escape($language['weight']) : 0; ?>">
    </div>
    <?php if (isset($form_errors['weight'])) { ?>
    <div class="help-block col-md-6"><?php echo $form_errors['weight']; ?></div>
    <?php } ?>
  </div>
</form>