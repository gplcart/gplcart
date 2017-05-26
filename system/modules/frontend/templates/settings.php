<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" class="module-settings frontend-settings form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Options'); ?></div>
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('catalog_limit', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Catalog product limit'); ?>
        </label>
        <div class="col-md-3">
          <input name="settings[catalog_limit]" class="form-control" value="<?php echo $this->e($settings['catalog_limit']); ?>">
          <div class="help-block">
            <?php echo $this->error('catalog_limit'); ?>
            <div class="text-muted">
              <?php echo $this->text('Number of products per page in the product catalog'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Image styles'); ?></div>
    <div class="panel-body">
      <?php foreach($imagestyle_fields as $key => $label) { ?>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $label; ?></label>
        <div class="col-md-4">
          <select name="settings[<?php echo $key; ?>]" class="form-control">
            <option value=""><?php echo $this->text('Default'); ?></option>
            <?php foreach($imagestyles as $id => $name) { ?>
            <option value="<?php echo $this->e($id); ?>"<?php echo $settings[$key] == $id ? ' selected' : ''; ?>><?php echo $this->e($name); ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <button class="btn btn-danger reset" name="reset" value="1" onclick="return confirm(GplCart.text('Are you sure?'));">
            <i class="fa fa-refresh"></i> <?php echo $this->text('Reset'); ?>
          </button>
        </div>
        <div class="col-md-10">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url('admin/module/list'); ?>" class="btn btn-default cancel">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <button class="btn btn-default save" name="save" value="1">
              <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>