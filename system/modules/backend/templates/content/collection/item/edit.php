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
<form method="post" id="edit-collection-item" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <input type="hidden" name="collection_item[value]" value="<?php echo isset($collection_item['value']) ? $collection_item['value'] : ''; ?>">
  <div class="form-group">
    <label class="col-md-2 control-label">
      <?php echo $this->text('Status'); ?>
    </label>
    <div class="col-md-6">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo empty($collection_item['status']) ? '' : ' active'; ?>">
          <input name="collection_item[status]" type="radio" autocomplete="off" value="1"<?php echo empty($collection_item['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-default<?php echo empty($collection_item['status']) ? ' active' : ''; ?>">
          <input name="collection_item[status]" type="radio" autocomplete="off" value="0"<?php echo empty($collection_item['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <div class="help-block">
      <?php echo $this->text('Only enabled items will be shown publicly'); ?>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('value', ' has-error'); ?>">
    <label class="col-md-2 control-label">
      <?php echo $this->e($handler['title']); ?>
    </label>
    <div class="col-md-6">
      <input name="collection_item[input]" class="form-control" value="<?php echo isset($collection_item['input']) ? $this->e($collection_item['input']) : ''; ?>">
      <div class="help-block">
        <?php echo $this->error('value'); ?>
        <div class="text-muted">
          <?php echo $this->text('Required. Start to type in the field an entity title to get suggestions or enter a numeric entity ID'); ?>
          <?php if($this->access('file_add') && $this->access('file_upload')) { ?>
          <a href="<?php echo $this->url('admin/content/file/add'); ?>"><?php echo $this->text('Upload new file'); ?></a>
          <?php } ?>
      </div>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('data.url', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Url'); ?></label>
    <div class="col-md-6">
      <input name="collection_item[data][url]" class="form-control" value="<?php echo isset($collection_item['data']['url']) ? $this->e($collection_item['data']['url']) : ''; ?>">
      <div class="help-block">
        <?php echo $this->error('data.url'); ?>
        <div class="text-muted">
          <?php echo $this->text('Optional. Enter a referring URL. You can use either absolute (with http://) or relative URLs'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('weight', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Weight'); ?></label>
    <div class="col-md-4">
      <input name="collection_item[weight]" class="form-control" value="<?php echo isset($collection_item['weight']) ? $this->e($collection_item['weight']) : $weight; ?>">
      <div class="help-block">
        <?php echo $this->error('weight'); ?>
        <div class="text-muted">
          <?php echo $this->text('Required. Position of the item. Items with lower weight go first'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-10 col-md-offset-2">
      <div class="btn-toolbar">
        <a href="<?php echo $this->url("admin/content/collection-item/{$collection['collection_id']}"); ?>" class="btn btn-default cancel">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('collection_item_add')) { ?>
        <button class="btn btn-default save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>