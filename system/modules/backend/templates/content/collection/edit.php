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
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
    <div class="col-md-4">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo empty($collection['status']) ? '' : ' active'; ?>">
          <input name="collection[status]" type="radio" autocomplete="off" value="1"<?php echo empty($collection['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-default<?php echo empty($collection['status']) ? ' active' : ''; ?>">
          <input name="collection[status]" type="radio" autocomplete="off" value="0"<?php echo empty($collection['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
        </label>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('title', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Title'); ?></label>
    <div class="col-md-4">
      <input name="collection[title]" maxlength="255" class="form-control" value="<?php echo isset($collection['title']) ? $this->e($collection['title']) : ''; ?>">
      <div class="help-block"><?php echo $this->error('title'); ?></div>
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
        <input maxlength="255" name="collection[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($collection['translation'][$code]['title'])) ? $this->e($collection['translation'][$code]['title']) : ''; ?>">
        <div class="help-block"><?php echo $this->error("translation.$code.title"); ?></div>
      </div>
    </div>
    <?php } ?>
  </div>
  <?php } ?>
  <?php if (empty($collection['collection_id'])) { ?>
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Type'); ?></label>
    <div class="col-md-4">
      <select name="collection[type]" class="form-control">
        <?php foreach ($types as $handler_id => $name) { ?>
        <?php if (isset($collection['type']) && $collection['type'] == $handler_id) { ?>
        <option value="<?php echo $this->e($handler_id); ?>" selected>
          <?php echo $this->e($name); ?>
        </option>
        <?php } else { ?>
        <option value="<?php echo $this->e($handler_id); ?>">
          <?php echo $this->e($name); ?>
        </option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
  </div>
  <?php } ?>
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Store'); ?></label>
    <div class="col-md-4">
      <select name="collection[store_id]" class="form-control">
        <?php foreach ($_stores as $store_id => $store) { ?>
        <?php if (isset($collection['store_id']) && $collection['store_id'] == $store_id) { ?>
        <option value="<?php echo $store_id; ?>" selected><?php echo $this->e($store['name']); ?></option>
        <?php } else { ?>
        <option value="<?php echo $store_id; ?>"><?php echo $this->e($store['name']); ?></option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
  </div>
  <div class="row">
    <div class="col-md-10 col-md-offset-2">
      <div class="btn-toolbar">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Are you sure? It cannot be undone!'));">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a class="btn btn-default" href="<?php echo $this->url('admin/content/collection'); ?>"><?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('collection_edit') || $this->access('collection_add')) { ?>
        <button class="btn btn-default" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>