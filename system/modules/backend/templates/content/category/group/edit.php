<form method="post" id="edit-group" onsubmit="return confirm();" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-8">    
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Data'); ?></div>
        <div class="panel-body">
          <div class="form-group required<?php echo isset($this->errors['title']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <?php echo $this->text('Title'); ?>
            </label>
            <div class="col-md-4">
              <input name="category_group[title]" maxlength="255" class="form-control" value="<?php echo isset($category_group['title']) ? $this->escape($category_group['title']) : ''; ?>" autofocus>
              <?php if (isset($this->errors['title'])) { ?>
              <div class="help-block"><?php echo $this->errors['title']; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php echo isset($this->errors['type']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Brand categories are used for grouping products by trademark, catalog - grouping by type'); ?>">
                <?php echo $this->text('Type'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <select name="category_group[type]" class="form-control">
                <option value=""><?php echo $this->text('None'); ?></option>
                <?php foreach (array('catalog', 'brand') as $type) { ?>
                <option value="<?php echo $type; ?>"<?php echo (isset($category_group['type']) && $category_group['type'] == $type) ? ' selected' : ''; ?>><?php echo $this->text($type); ?></option>
                <?php } ?>
              </select>
              <?php if (isset($this->errors['type'])) { ?>
              <div class="help-block"><?php echo $this->errors['type']; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('This category group will be displayed only in the selected store'); ?>">
              <?php echo $this->text('Store'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <select name="category_group[store_id]" class="form-control">
                <?php foreach ($stores as $store_id => $store_name) { ?>
                <?php if (isset($category_group['store_id']) && $category_group['store_id'] == $store_id) { ?>
                <option value="<?php echo $store_id; ?>" selected><?php echo $store_name; ?></option>
                <?php } else { ?>
                <option value="<?php echo $store_id; ?>"><?php echo $store_name; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
        </div>
      </div>    
      <?php if (!empty($languages)) { ?> 
        <div class="panel panel-default">
          <div class="panel-heading"><?php echo $this->text('Translations'); ?></div>
            <div class="panel-body">
              <?php foreach ($languages as $code => $info) { ?>
              <div class="form-group<?php echo isset($this->errors['translation'][$code]['title']) ? ' has-error' : ''; ?>">
                <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $info['native_name'])); ?></label>
                <div class="col-md-4">
                  <input name="category_group[translation][<?php echo $code; ?>][title]" maxlength="255" class="form-control" value="<?php echo isset($category_group['translation'][$code]['title']) ? $this->escape($category_group['translation'][$code]['title']) : ''; ?>">
                  <?php if (isset($this->errors['translation'][$code]['title'])) { ?>
                  <div class="help-block"><?php echo $this->errors['translation'][$code]['title']; ?></div>
                  <?php } ?>
                </div>
              </div>
              <?php } ?>
            </div>
          </div>
      <?php } ?>  
    </div>
    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="btn-toolbar">
            <?php if (isset($category_group['category_group_id']) && $this->access('category_group_delete') && $can_delete) { ?>
            <button class="btn btn-danger delete" name="delete" value="1">
              <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
            </button>
            <?php } ?>
            <a class="btn btn-default" href="<?php echo $this->url('admin/content/category/group'); ?>">
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