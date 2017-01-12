<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" id="edit-field" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group required<?php echo $this->error('title', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Title'); ?>
        </label>
        <div class="col-md-4">
          <input maxlength="255" name="field[title]" class="form-control" value="<?php echo (isset($field['title'])) ? $this->escape($field['title']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('title'); ?>
            <div class="text-muted">
              <?php echo $this->text('Required. The title will be displayed to customers on product pages'); ?>
            </div>
          </div>
        </div>
      </div>
      <?php if (!empty($languages)) { ?>
      <?php foreach ($languages as $code => $language) { ?>
        <div class="form-group<?php echo $this->error("translation.$code.title", ' has-error'); ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $language['name'])); ?></label>
          <div class="col-md-4">
            <input maxlength="255" name="field[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($field['translation'][$code]['title'])) ? $this->escape($field['translation'][$code]['title']) : ''; ?>">
            <div class="help-block">
              <?php echo $this->error("translation.$code.title"); ?>
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
      <?php if (empty($field['field_id'])) { ?>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Type'); ?></label>
        <div class="col-md-4">
          <select name="field[type]" class="form-control">
            <?php foreach($types as $type => $type_name) { ?>
            <?php if (isset($field['type']) && $field['type'] == $type) { ?>
            <option value="<?php echo $this->escape($type); ?>" selected>
            <?php echo $this->escape($type_name); ?>
            </option>
            <?php } else { ?>
            <option value="<?php echo $this->escape($type); ?>">
            <?php echo $this->escape($type_name); ?>
            </option>
            <?php } ?>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->text('Atributes are facts about the products, options are interactive with the customer (Size, Color etc)'); ?>
          </div>
        </div>
      </div>
      <?php } ?>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Widget'); ?>
        </label>
        <div class="col-md-4">
          <select name="field[widget]" class="form-control">
            <?php foreach ($widget_types as $type => $name) { ?>
            <option value="<?php echo $type; ?>"<?php echo (isset($field['widget']) && $field['widget'] == $type) ? ' selected' : ''; ?>><?php echo $this->escape($name); ?></option>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select how to represent the field to customers. This is for options only'); ?>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('weight', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Weight'); ?></label>
        <div class="col-md-4">
          <input name="field[weight]" class="form-control" value="<?php echo isset($field['weight']) ? $this->escape($field['weight']) : 0; ?>">
        <div class="help-block">
          <?php echo $this->error('weight'); ?>
          <div class="text-muted">
            <?php echo $this->text('Fields are sorted in lists by the weight value. Lower value means higher position'); ?>
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
          <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Delete? It cannot be undone!'));">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-4">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url('admin/content/field'); ?>" class="cancel btn btn-default">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('field_edit') || $this->access('field_add')) { ?>
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