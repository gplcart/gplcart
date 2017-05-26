<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" enctype="multipart/form-data" id="edit-field-value" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="required form-group<?php echo $this->error('title', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Title'); ?></label>
        <div class="col-md-4">
          <input maxlength="255" name="field_value[title]" class="form-control" value="<?php echo (isset($field_value['title'])) ? $this->e($field_value['title']) : ''; ?>" autofocus>
          <div class="help-block">
            <?php echo $this->error('title'); ?>
            <div class="text-muted"><?php echo $this->text('Required. The title will be displayed to customers on product pages'); ?></div>
          </div>
        </div>
      </div>
      <?php if (!empty($_languages)) { ?>
      <div class="form-group">
        <div class="col-md-10 col-md-offset-2">
          <a data-toggle="collapse" href="#translations">
            <?php echo $this->text('Translations'); ?> <span class="caret"></span>
          </a>
        </div>
      </div>
      <div id="translations" class="collapse translations<?php echo $this->error(null, ' in'); ?>">
        <?php foreach ($_languages as $code => $language) { ?>
        <div class="form-group<?php echo $this->error("translation.$code.title", ' has-error'); ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $language['native_name'])); ?></label>
          <div class="col-md-4">
            <input maxlength="255" name="field_value[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($field_value['translation'][$code]['title'])) ? $this->e($field_value['translation'][$code]['title']) : ''; ?>">
            <div class="help-block">
              <?php echo $this->error("translation.$code.title"); ?>
              <div class="text-muted">
              <?php echo $this->text('An optional translation for language %name', array('%name' => $language['name'])); ?>
              </div>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('color', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Color'); ?>
        </label>
        <div class="col-md-3">
          <input class="form-control" type="color" name="field_value[color]" value="<?php echo empty($field_value['color']) ? '#000000' : $this->e($field_value['color']); ?>">
          <div class="help-block">
            <?php echo $this->error('color'); ?>
            <div class="text-muted">
            <?php echo $this->text('Specify a HEX color code. It\'s applicable only for fields with color widgets'); ?>
            </div>
          </div>
        </div>
      </div>
      <?php if ($this->access('file_upload')) { ?>
      <div class="form-group<?php echo $this->error('file', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Image'); ?></label>
        <div class="col-md-3">
          <input type="file" name="file" accept="image/*" class="form-control">
          <div class="help-block">
            <?php echo $this->error('file'); ?>
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
      <div class="form-group<?php echo $this->error('weight', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Weight'); ?>
        </label>
        <div class="col-md-3">
          <input maxlength="2" name="field_value[weight]" class="form-control" value="<?php echo (isset($field_value['weight'])) ? $this->e($field_value['weight']) : 0; ?>">
          <div class="help-block">
            <?php echo $this->error('weight'); ?>
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
          <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Delete? It cannot be undone!'));">
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