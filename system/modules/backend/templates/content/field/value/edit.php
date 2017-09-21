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
<form method="post" enctype="multipart/form-data" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="required form-group<?php echo $this->error('title', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Title'); ?></label>
    <div class="col-md-4">
      <input maxlength="255" name="field_value[title]" class="form-control" value="<?php echo (isset($field_value['title'])) ? $this->e($field_value['title']) : ''; ?>" autofocus>
      <div class="help-block">
        <?php echo $this->error('title'); ?>
        <div class="text-muted"><?php echo $this->text('The title will be displayed to customers on product pages'); ?></div>
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
        </div>
      </div>
    </div>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="form-group<?php echo $this->error('color', ' has-error'); ?>">
    <label class="col-md-2 control-label">
      <?php echo $this->text('Color'); ?>
    </label>
    <div class="col-md-4">
      <input class="form-control" type="color" name="field_value[color]" value="<?php echo empty($field_value['color']) ? '#000000' : $this->e($field_value['color']); ?>">
      <div class="help-block">
        <?php echo $this->error('color'); ?>
        <div class="text-muted">
        <?php echo $this->text("Specify a HEX color code. It's applicable only for fields with color widgets"); ?>
        </div>
      </div>
    </div>
  </div>
  <?php if ($this->access('file_upload')) { ?>
  <div class="form-group<?php echo $this->error('file', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Image'); ?></label>
    <div class="col-md-4">
      <input type="file" name="file" accept="image/*" class="form-control">
      <div class="help-block">
        <?php echo $this->error('file'); ?>
        <div class="text-muted">
        <?php echo $this->text("Upload an image. It's applicable only for fields with image widgets"); ?>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>
  <?php if (isset($field_value['thumb'])) { ?>
  <div class="form-group">
    <div class="col-md-4 col-md-offset-2">
      <div class="checkbox">
        <label>
          <input type="checkbox" name="delete_image" value="1"> <?php echo $this->text('Delete existing <a target="_blank" href="@url">image</a>', array('@url' => $field_value['thumb'])); ?>
        </label>
      </div>
    </div>
  </div>
  <?php } ?>
  <div class="form-group<?php echo $this->error('weight', ' has-error'); ?>">
    <label class="col-md-2 control-label">
      <?php echo $this->text('Weight'); ?>
    </label>
    <div class="col-md-4">
      <input maxlength="2" name="field_value[weight]" class="form-control" value="<?php echo (isset($field_value['weight'])) ? $this->e($field_value['weight']) : 0; ?>">
      <div class="help-block">
        <?php echo $this->error('weight'); ?>
        <div class="text-muted">
        <?php echo $this->text('Items are sorted in lists by the weight value. Lower value means higher position'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-10 col-md-offset-2">
      <div class="btn-toolbar">
        <?php if (isset($field_value['field_value_id']) && $this->access('field_value_delete')) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url("admin/content/field/value/{$field['field_id']}"); ?>" class="btn btn-default cancel">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('field_value_add') || $this->access('field_value_edit')) { ?>
        <button class="btn btn-default save" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>