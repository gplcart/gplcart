<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-group<?php echo $this->error('default', ' has-error'); ?>">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Default'); ?></label>
    <div class="col-md-6">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn<?php echo empty($language['default']) ? '' : ' active'; ?>">
          <input name="language[default]" type="radio" autocomplete="off" value="1"<?php echo empty($language['default']) ? '' : ' checked'; ?>><?php echo $this->text('Yes'); ?>
        </label>
        <label class="btn<?php echo empty($language['default']) ? ' active' : ''; ?>">
          <input name="language[default]" type="radio" autocomplete="off" value="0"<?php echo empty($language['default']) ? ' checked' : ''; ?>><?php echo $this->text('No'); ?>
        </label>
      </div>
      <div class="form-text">
        <?php echo $this->error('default'); ?>
        <div class="text-muted"><?php echo $this->text('If selected, then the language will be considered as default fallback language'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('status', ' has-error'); ?>">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Status'); ?></label>
    <div class="col-md-6">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn<?php echo empty($language['status']) ? '' : ' active'; ?>">
          <input name="language[status]" type="radio" autocomplete="off" value="1"<?php echo empty($language['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn<?php echo empty($language['status']) ? ' active' : ''; ?>">
          <input name="language[status]" type="radio" autocomplete="off" value="0"<?php echo empty($language['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <div class="form-text">
        <?php echo $this->error('status'); ?>
        <div class="text-muted"><?php echo $this->text('Disabled languages will not be available to customers'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('rtl', ' has-error'); ?>">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Right-to-left'); ?></label>
    <div class="col-md-6">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn<?php echo empty($language['rtl']) ? '' : ' active'; ?>">
          <input name="language[rtl]" type="radio" autocomplete="off" value="1"<?php echo empty($language['rtl']) ? '' : ' checked'; ?>><?php echo $this->text('Yes'); ?>
        </label>
        <label class="btn<?php echo empty($language['rtl']) ? ' active' : ''; ?>">
          <input name="language[rtl]" type="radio" autocomplete="off" value="0"<?php echo empty($language['rtl']) ? ' checked' : ''; ?>><?php echo $this->text('No'); ?>
        </label>
      </div>
      <div class="form-text">
        <?php echo $this->error('rtl'); ?>
        <div class="text-muted"><?php echo $this->text('Whether the language is written in right-to-left (RTL) script'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('code', ' has-error'); ?>">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Code'); ?></label>
    <div class="col-md-4">
      <input name="language[code]" class="form-control" value="<?php echo isset($language['code']) ? $this->e($language['code']) : ''; ?>"<?php echo empty($edit) ? '' : ' disabled'; ?>>
      <div class="form-text">
        <?php echo $this->error('code'); ?>
        <div class="text-muted">
          <?php echo $this->text('Language code according to ISO 639-1 standard. Culture names (e.g en-US) also accepted'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('name', ' has-error'); ?>">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Name'); ?></label>
    <div class="col-md-4">
      <input name="language[name]" class="form-control" maxlength="32" value="<?php echo isset($language['name']) ? $this->e($language['name']) : ''; ?>">
      <div class="form-text">
        <?php echo $this->error('name'); ?>
        <div class="text-muted">
          <?php echo $this->text('International english name of the language according to ISO 639 standard'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('native_name', ' has-error'); ?>">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Native name'); ?></label>
    <div class="col-md-4">
      <input name="language[native_name]" maxlength="50" class="form-control" value="<?php echo isset($language['native_name']) ? $this->e($language['native_name']) : ''; ?>">
      <div class="form-text">
        <?php echo $this->error('native_name'); ?>
        <div class="text-muted">
          <?php echo $this->text('Local name of the language, e.g 中文'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('weight', ' has-error'); ?>">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Weight'); ?></label>
    <div class="col-md-4">
      <input name="language[weight]" maxlength="2" class="form-control" value="<?php echo isset($language['weight']) ? $this->e($language['weight']) : 0; ?>">
      <div class="form-text">
        <?php echo $this->error('weight'); ?>
        <div class="text-muted">
          <?php echo $this->text('Items are sorted in lists by the weight value. Lower value means higher position'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-10 offset-md-2">
      <div class="btn-toolbar">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/settings/language'); ?>" class="btn cancel">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('language_edit') || $this->access('language_add')) { ?>
        <button class="btn save" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>