<form method="post" id="edit-collection-item" onsubmit="return confirm();" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">    
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
          <?php echo $this->text('Only enabled items will be shown to frontend users'); ?>
          </div>
        </div>
      </div>
      <input type="hidden" name="collection_item[value]" value="<?php echo isset($collection_item['value']) ? $collection_item['value'] : ''; ?>">
      <div class="form-group required<?php echo isset($this->errors['value']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->escape($handler['title']); ?>
        </label>
        <div class="col-md-6">
          <input name="collection_item[input]" class="form-control" value="<?php echo isset($collection_item['input']) ? $this->escape($collection_item['input']) : ''; ?>">
          <div class="help-block">
          <?php if (isset($this->errors['value'])) { ?>
          <?php echo $this->errors['value']; ?>
          <?php } ?>
          <div class="text-muted">
          <?php echo $this->text('Required. Start to type in the field an entity title to get suggestions or enter a numeric entity ID'); ?>
          </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['data']['url']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Url'); ?></label>
        <div class="col-md-6">
          <input name="collection_item[data][url]" class="form-control" value="<?php echo isset($collection_item['data']['url']) ? $this->escape($collection_item['data']['url']) : ''; ?>">
          <div class="help-block">
          <?php if (isset($this->errors['data']['url'])) { ?>
          <?php echo $this->errors['data']['url']; ?>
          <?php } ?>
          <div class="text-muted">
          <?php echo $this->text('Optional. Enter a referring URL. You can use either absolute (with http://) or relative URLs'); ?>
          </div>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo isset($this->errors['weight']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Weight'); ?></label>
        <div class="col-md-3">
          <input name="collection_item[weight]" class="form-control" value="<?php echo isset($collection_item['weight']) ? $this->escape($collection_item['weight']) : $weight; ?>">
          <div class="help-block">
          <?php if (isset($this->errors['weight'])) { ?>
          <?php echo $this->errors['weight']; ?>
          <?php } ?>
          <div class="text-muted">
          <?php echo $this->text('Required. Position of the item. Items with lower weight go first'); ?>
          </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-10 col-md-offset-2">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url("admin/content/collection-item/{$collection['collection_id']}"); ?>" class="btn btn-default cancel">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('collection_item_add')) { ?>
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