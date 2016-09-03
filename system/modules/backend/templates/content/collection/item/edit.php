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
        </div>
      </div>
      <input type="hidden" name="collection_item[value]" value="<?php echo isset($collection_item['value']) ? $collection_item['value'] : ''; ?>">
      <div class="form-group required<?php echo isset($this->errors['input']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->escape($handler['title']); ?>
        </label>
        <div class="col-md-6">
          <input name="collection_item[input]" class="form-control" value="<?php echo isset($collection_item['input']) ? $this->escape($collection_item['input']) : ''; ?>">
          <?php if (isset($this->errors['input'])) { ?>
          <div class="help-block"><?php echo $this->errors['input']; ?></div>
          <?php } ?>
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