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
  <input type="hidden" name="collection_item[entity_id]" value="<?php echo isset($collection_item['entity_id']) ? $collection_item['entity_id'] : ''; ?>">
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
      <?php echo $this->text('Only enabled items will be available publicly'); ?>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('entity_id', ' has-error'); ?>">
    <label class="col-md-2 control-label">
      <?php echo $this->e($handler['title']); ?>
    </label>
    <div class="col-md-4">
      <input name="collection_item[title]" class="form-control" value="<?php echo isset($collection_item['title']) ? $collection_item['title'] : ''; ?>">
      <div class="help-block">
        <?php echo $this->error('entity_id'); ?>
        <div class="text-muted">
          <?php echo $this->text('Start to type in the field an entity title to get suggestions or enter a numeric entity ID'); ?>
          <?php if($this->access('file_add') && $this->access('file_upload')) { ?>
          <p><a href="<?php echo $this->url('admin/content/file/add'); ?>"><?php echo $this->text('Upload new file'); ?></a></p>
          <?php } ?>
      </div>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('data.url', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('URL'); ?></label>
    <div class="col-md-4">
      <input name="collection_item[data][url]" class="form-control" value="<?php echo isset($collection_item['data']['url']) ? $this->e($collection_item['data']['url']) : ''; ?>">
      <div class="help-block">
        <?php echo $this->error('data.url'); ?>
        <div class="text-muted">
          <?php echo $this->text('Enter a referring URL. You can use either absolute (i.e starting with <i>http://</i>) or relative URLs'); ?>
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
          <?php echo $this->text('Items are sorted in lists by the weight value. Lower value means higher position'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-10 col-md-offset-2">
      <div class="btn-toolbar">
        <?php if (isset($collection_item['collection_item_id']) && $this->access('collection_item_delete')) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url("admin/content/collection-item/{$collection['collection_id']}"); ?>" class="btn btn-default cancel">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('collection_item_add')) { ?>
        <button class="btn btn-default save" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>