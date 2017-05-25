<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" id="edit-group" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group required<?php echo $this->error('title', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Title'); ?></label>
        <div class="col-md-4">
          <input name="category_group[title]" maxlength="255" class="form-control" value="<?php echo isset($category_group['title']) ? $this->escape($category_group['title']) : ''; ?>" autofocus>
          <div class="help-block">
            <?php echo $this->error('title'); ?>
            <div class="text-muted"><?php echo $this->text('Category group name to be shown to administrators and customers'); ?></div>
          </div>
        </div>
      </div>
      <?php if (!empty($_languages)) { ?>
      <?php foreach ($_languages as $code => $info) { ?>
      <div class="form-group<?php echo $this->error("translation.$code.title", ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $info['native_name'])); ?></label>
        <div class="col-md-4">
          <input name="category_group[translation][<?php echo $code; ?>][title]" maxlength="255" class="form-control" value="<?php echo isset($category_group['translation'][$code]['title']) ? $this->escape($category_group['translation'][$code]['title']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error("translation.$code.title"); ?>
          </div>
        </div>
      </div>
      <?php } ?>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('type', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Type'); ?>
        </label>
        <div class="col-md-4">
          <select name="category_group[type]" class="form-control">
            <option value=""><?php echo $this->text('None'); ?></option>
            <?php foreach ($types as $type => $name) { ?>
            <option value="<?php echo $type; ?>"<?php echo (isset($category_group['type']) && $category_group['type'] == $type) ? ' selected' : ''; ?>><?php echo $this->escape($name); ?></option>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->error('type'); ?>
            <div class="text-muted"><?php echo $this->text('Brand category groups will contain trademarks (Sony, Apple), catalog - normal categories like Computers, Monitors etc.'); ?></div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Store'); ?>
        </label>
        <div class="col-md-4">
          <select name="category_group[store_id]" class="form-control">
            <?php foreach ($_stores as $store_id => $store) { ?>
            <?php if (isset($category_group['store_id']) && $category_group['store_id'] == $store_id) { ?>
            <option value="<?php echo $store_id; ?>" selected><?php echo $this->e($store['name']); ?></option>
            <?php } else { ?>
            <option value="<?php echo $store_id; ?>"><?php echo $this->e($store['name']); ?></option>
            <?php } ?>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select a store where to display this category group'); ?>
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
            <a class="btn btn-default" href="<?php echo $this->url('admin/content/category-group'); ?>">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('category_group_add') || $this->access('category_group_edit')) { ?>
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