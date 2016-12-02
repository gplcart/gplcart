<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" id="edit-language" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group required<?php echo $this->error('code', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Code'); ?></label>
        <div class="col-md-4">
          <input name="language[code]" maxlength="2" class="form-control" value="<?php echo isset($language['code']) ? $this->escape($language['code']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('code'); ?>
            <div class="text-muted">
              <?php echo $this->text('Required. A language code according to ISO 639-1, culture names also accepted'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('name', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-4">
          <input name="language[name]" class="form-control" maxlength="32" value="<?php echo isset($language['name']) ? $this->escape($language['name']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('name'); ?>
            <div class="text-muted">
              <?php echo $this->text('An international english name of the language according to ISO 639 standard'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('native_name', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Native name'); ?></label>
        <div class="col-md-4">
          <input name="language[native_name]" maxlength="50" class="form-control" value="<?php echo isset($language['native_name']) ? $this->escape($language['native_name']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('native_name'); ?>
            <div class="text-muted">
              <?php echo $this->text('A local name of the language, e.g 中文'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
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
      <div class="form-group<?php echo $this->error('status', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
        <div class="col-md-6">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo!empty($language['status']) ? ' active' : ''; ?>">
              <input name="language[status]" type="radio" autocomplete="off" value="1"<?php echo!empty($language['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($language['status']) ? ' active' : ''; ?>">
              <input name="language[status]" type="radio" autocomplete="off" value="0"<?php echo empty($language['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
            <?php echo $this->error('status'); ?>
            <div class="text-muted"><?php echo $this->text('Only enabled languages will be available to users'); ?></div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('weight', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Weight'); ?></label>
        <div class="col-md-4">
          <input name="language[weight]" maxlength="2" class="form-control" value="<?php echo isset($language['weight']) ? $this->escape($language['weight']) : 0; ?>">
          <div class="help-block">
            <?php echo $this->error('weight'); ?>
            <div class="text-muted">
              <?php echo $this->text('Languages are sorted in lists by the weight value. Lower value means higher position'); ?>
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
          <?php if ($can_delete) { ?>
          <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('Delete? It cannot be undone!');">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-4">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url('admin/settings/language'); ?>" class="btn btn-default cancel">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('language_edit') || $this->access('language_add')) { ?>
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