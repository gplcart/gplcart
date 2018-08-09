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
  <div class="form-group row required<?php echo $this->error('title', ' has-error'); ?>">
    <div class="col-md-4">
      <label><?php echo $this->text('Title'); ?></label>
      <input name="category_group[title]" maxlength="255" class="form-control" value="<?php echo isset($category_group['title']) ? $this->e($category_group['title']) : ''; ?>" autofocus>
      <div class="form-text">
        <?php echo $this->error('title'); ?>
        <div class="description"><?php echo $this->text('Category group name to be shown to administrators and customers'); ?></div>
      </div>
    </div>
  </div>
  <?php if (!empty($languages)) { ?>
  <div class="form-group">
      <a data-toggle="collapse" href="#translations">
        <?php echo $this->text('Translations'); ?> <span class="dropdown-toggle"></span>
      </a>
  </div>
  <div id="translations" class="collapse translations<?php echo $this->error(null, ' show'); ?>">
    <?php foreach ($languages as $code => $info) { ?>
    <div class="form-group row<?php echo $this->error("translation.$code.title", ' has-error'); ?>">
      <div class="col-md-4">
        <label><?php echo $this->text('Title %language', array('%language' => $info['native_name'])); ?></label>
        <input name="category_group[translation][<?php echo $code; ?>][title]" maxlength="255" class="form-control" value="<?php echo isset($category_group['translation'][$code]['title']) ? $this->e($category_group['translation'][$code]['title']) : ''; ?>">
        <div class="form-text">
          <?php echo $this->error("translation.$code.title"); ?>
        </div>
      </div>
    </div>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="form-group row<?php echo $this->error('type', ' has-error'); ?>">
    <div class="col-md-4">
      <label><?php echo $this->text('Type'); ?></label>
      <select name="category_group[type]" class="form-control">
        <?php foreach ($category_group_types as $type_id => $type_name) { ?>
        <option value="<?php echo $type_id; ?>"<?php echo (isset($category_group['type']) && $category_group['type'] == $type_id) ? ' selected' : ''; ?>><?php echo $this->e($type_name); ?></option>
        <?php } ?>
      </select>
      <div class="form-text">
        <?php echo $this->error('type'); ?>
        <div class="description"><?php echo $this->text('Brand category groups will contain trademarks (Sony, Apple), catalog - normal categories like Computers, Monitors etc.'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-md-4">
      <label><?php echo $this->text('Store'); ?></label>
      <select class="form-control" name="category_group[store_id]">
        <?php foreach ($_stores as $store_id => $store) { ?>
        <?php if (isset($category_group['store_id']) && $category_group['store_id'] == $store_id) { ?>
        <option value="<?php echo $store_id; ?>" selected><?php echo $this->e($store['name']); ?></option>
        <?php } else { ?>
        <option value="<?php echo $store_id; ?>"><?php echo $this->e($store['name']); ?></option>
        <?php } ?>
        <?php } ?>
      </select>
      <div class="form-text">
        <div class="description"><?php echo $this->text('Select a store where to display this item'); ?></div>
      </div>
    </div>
  </div>
  <div class="btn-toolbar">
      <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
        </button>
      <?php } ?>
    <a class="btn" href="<?php echo $this->url('admin/content/category-group'); ?>">
        <?php echo $this->text('Cancel'); ?>
    </a>
      <?php if ($this->access('category_group_add') || $this->access('category_group_edit')) { ?>
        <button class="btn btn-success save" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
  </div>
</form>