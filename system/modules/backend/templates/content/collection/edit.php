<form method="post" id="edit-collection" onsubmit="return confirm();" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Status'); ?>
        </label>
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
      <div class="form-group required<?php echo isset($this->errors['title']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Title'); ?>
        </label>
        <div class="col-md-4">
          <input name="collection[title]" maxlength="255" class="form-control" value="<?php echo isset($collection['title']) ? $this->escape($collection['title']) : ''; ?>">
          <?php if (isset($this->errors['title'])) { ?>
          <div class="help-block"><?php echo $this->errors['title']; ?></div>
          <?php } ?>
        </div>
      </div>
      <?php if (!empty($languages)) { ?>
        <?php foreach ($languages as $code => $language) { ?>
        <div class="form-group<?php echo isset($this->errors['translation'][$code]['title']) ? ' has-error' : ''; ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $language['native_name'])); ?></label>
          <div class="col-md-4">
            <input maxlength="255" name="collection[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($collection['translation'][$code]['title'])) ? $this->escape($collection['translation'][$code]['title']) : ''; ?>">
            <?php if (isset($this->errors['translation'][$code]['title'])) { ?>
            <div class="help-block"><?php echo $this->xss($this->errors['translation'][$code]['title']); ?></div>
            <?php } ?>
          </div>
        </div>
        <?php } ?>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <?php if(empty($collection['collection_id'])) { ?>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Type'); ?>
        </label>
        <div class="col-md-4">
          <select name="collection[type]" class="form-control">
            <?php foreach($handlers as $handler_id => $handler) { ?>
            <?php if (isset($collection['type']) && $collection['type'] == $handler_id) { ?>
            <option value="<?php echo $this->escape($handler_id); ?>" selected>
              <?php echo $this->escape($handler['title']); ?>
            </option>
            <?php } else { ?>
            <option value="<?php echo $this->escape($handler_id); ?>">
              <?php echo $this->escape($handler['title']); ?>
            </option>
            <?php } ?>
            <?php } ?>
          </select>
        </div>
      </div>
      <?php } ?>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Store'); ?>
        </label>
        <div class="col-md-4">
          <select name="collection[store_id]" class="form-control">
            <?php foreach ($stores as $store_id => $store_name) { ?>
            <?php if (isset($collection['store_id']) && $collection['store_id'] == $store_id) { ?>
            <option value="<?php echo $store_id; ?>" selected><?php echo $this->escape($store_name); ?></option>
            <?php } else { ?>
            <option value="<?php echo $store_id; ?>"><?php echo $this->escape($store_name); ?></option>
            <?php } ?>
            <?php } ?>
          </select>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        </div>
        <div class="col-md-4">
      <div class="btn-toolbar">
        <a class="btn btn-default" href="<?php echo $this->url('admin/content/collection'); ?>"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('collection_edit') || $this->access('collection_add')) { ?>
        <button class="btn btn-default" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
        </div>
      </div>
    </div>
  </div>
</form>