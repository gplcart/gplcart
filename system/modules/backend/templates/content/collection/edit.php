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
  <div class="form-group row">
    <div class="col-md-4">
      <div class="btn-group btn-group-toggle" data-toggle="buttons">
        <label class="btn btn-outline-secondary<?php echo empty($collection['status']) ? '' : ' active'; ?>">
          <input name="collection[status]" type="radio" autocomplete="off" value="1"<?php echo empty($collection['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-outline-secondary<?php echo empty($collection['status']) ? ' active' : ''; ?>">
          <input name="collection[status]" type="radio" autocomplete="off" value="0"<?php echo empty($collection['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <div class="form-text">
        <div class="description">
            <?php echo $this->text('Disabled collections will not be displayed to customers'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group row required<?php echo $this->error('title', ' has-error'); ?>">
    <div class="col-md-4">
      <label><?php echo $this->text('Title'); ?></label>
      <input name="collection[title]" maxlength="255" class="form-control" value="<?php echo isset($collection['title']) ? $this->e($collection['title']) : ''; ?>">
      <div class="form-text">
        <?php echo $this->error('title'); ?>
        <div class="description">
            <?php echo $this->text('The name will be displayed to customers in the corresponding block provided by the collection'); ?>
        </div>
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
    <?php foreach ($languages as $code => $language) { ?>
    <div class="form-group row<?php echo $this->error("translation.$code.title", ' has-error'); ?>">
      <div class="col-md-4">
        <label><?php echo $this->text('Title %language', array('%language' => $language['native_name'])); ?></label>
        <input maxlength="255" name="collection[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($collection['translation'][$code]['title'])) ? $this->e($collection['translation'][$code]['title']) : ''; ?>">
        <div class="form-text"><?php echo $this->error("translation.$code.title"); ?></div>
      </div>
    </div>
    <?php } ?>
  </div>
  <?php } ?>
  <?php if (empty($collection['collection_id'])) { ?>
  <div class="form-group row">
    <div class="col-md-4">
      <label><?php echo $this->text('Type'); ?></label>
      <select name="collection[type]" class="form-control">
        <?php foreach ($types as $handler_id => $name) { ?>
        <?php if (isset($collection['type']) && $collection['type'] == $handler_id) { ?>
        <option value="<?php echo $this->e($handler_id); ?>" selected>
          <?php echo $this->text($name); ?>
        </option>
        <?php } else { ?>
        <option value="<?php echo $this->e($handler_id); ?>">
          <?php echo $this->text($name); ?>
        </option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
  </div>
  <?php } ?>
  <div class="form-group row">
    <div class="col-md-4">
      <label><?php echo $this->text('Store'); ?></label>
      <select class="form-control" name="collection[store_id]">
        <?php foreach ($_stores as $store_id => $store) { ?>
        <?php if (isset($collection['store_id']) && $collection['store_id'] == $store_id) { ?>
        <option value="<?php echo $store_id; ?>" selected><?php echo $this->e($store['name']); ?></option>
        <?php } else { ?>
        <option value="<?php echo $store_id; ?>"><?php echo $this->e($store['name']); ?></option>
        <?php } ?>
        <?php } ?>
      </select>
      <div class="form-text">
        <?php echo $this->error('store_id'); ?>
        <div class="description"><?php echo $this->text('Select a store associated with this collection'); ?></div>
      </div>
    </div>
  </div>
      <div class="btn-toolbar">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a class="btn" href="<?php echo $this->url('admin/content/collection'); ?>"><?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('collection_edit') || $this->access('collection_add')) { ?>
        <button class="btn btn-success" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
</form>