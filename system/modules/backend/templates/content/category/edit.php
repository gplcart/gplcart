<form method="post" id="edit-category" onsubmit="return confirm();" class="form-horizontal<?php echo $this->error(null, ' form-errors'); ?>">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <input type="hidden" name="category[category_group_id]" value="<?php echo $category_group['category_group_id']; ?>">
  <div class="row">
    <div class="col-md-6">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo (!isset($category['status']) || $category['status']) ? ' active' : ''; ?>">
          <input name="category[status]" type="radio" autocomplete="off" value="1"<?php echo (!isset($category['status']) || $category['status']) ? ' checked' : ''; ?>>
          <?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-default hint<?php echo (!isset($category['status']) || $category['status']) ? '' : ' active'; ?>" title="<?php echo $this->text('Disabled categories will not be available for customers and search engines'); ?>">
          <input name="category[status]" type="radio" autocomplete="off" value="0"<?php echo (!isset($category['status']) || $category['status']) ? '' : ' checked'; ?>>
          <?php echo $this->text('Disabled'); ?>
        </label>
      </div>
    </div>
    <div class="col-md-6 text-right">
      <div class="btn-toolbar">
        <?php if (isset($category['category_id']) && $this->access('category_delete') && $can_delete) { ?>
        <button class="btn btn-danger" name="delete" value="1">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url("admin/content/category/{$category_group['category_group_id']}"); ?>" class="btn btn-default cancel">
          <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('category_add') || $this->access('category_edit')) { ?>
        <button class="btn btn-primary save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="row category-form margin-top-20">
    <div class="col-md-12">
      <div class="panel-group" id="edit-category-accordion">
        <div class="panel panel-default">
          <div class="panel-heading clearfix">
            <h4 class="panel-title pull-left">
              <a class="collapsed" data-toggle="collapse" data-parent="#edit-category-accordion" href="#pane-description">
                <?php echo $this->text('Description'); ?>
              </a>
            </h4>
            <span class="pull-right"><i class="fa fa-chevron-up"></i></span>
          </div>
          <div id="pane-description" class="panel-collapse collapse in always-visible">
            <div class="panel-body">
              <div class="form-group required<?php echo $this->error('title', ' has-error'); ?>">
                <label class="col-md-2 control-label">
                  <span title="<?php echo $this->text('Category name to be used on the category page and menu'); ?>" class="hint">
                    <?php echo $this->text('Title'); ?>
                  </span>
                </label>
                <div class="col-md-4">
                  <input maxlength="255" name="category[title]" class="form-control" value="<?php echo isset($category['title']) ? $this->escape($category['title']) : ''; ?>">
                  <?php if ($this->error('title', true)) { ?>
                  <div class="help-block"><?php echo $this->error('title'); ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-md-2 control-label">
                  <span class="hint" title="<?php echo $this->text('Main category description, usually placed on the top of the category page'); ?>">
                  <?php echo $this->text('First description'); ?>
                  </span>
                </label>
                <div class="col-md-8">
                  <textarea class="form-control summernote" name="category[description_1]"><?php echo isset($category['description_1']) ? $this->xss($category['description_1']) : ''; ?></textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="col-md-2 control-label">
                  <span class="hint" title="<?php echo $this->text('Additional category description, usually placed on the bottom of the category page'); ?>">
                  <?php echo $this->text('Second description'); ?>
                  </span>
                </label>
                <div class="col-md-8">
                  <textarea class="form-control summernote" name="category[description_2]"><?php echo isset($category['description_2']) ? $this->xss($category['description_2']) : ''; ?></textarea>
                </div>
              </div>
              <?php if ($languages) { ?>
              <div class="form-group">
                <div class="col-md-6 col-md-offset-2">
                  <a data-toggle="collapse" href="#translations">
                    <?php echo $this->text('Translations'); ?> <span class="caret"></span>
                  </a>
                </div>
              </div>
              <div id="translations" class="collapse translations<?php echo $this->error(null, ' in'); ?>">
                <?php foreach ($languages as $code => $language) { ?>
                <div class="form-group<?php echo isset($this->errors['translation'][$code]['title']) ? ' has-error' : ''; ?>">
                  <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $language['native_name'])); ?></label>
                  <div class="col-md-4">
                    <input maxlength="255" name="category[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo isset($category['translation'][$code]['title']) ? $this->escape($category['translation'][$code]['title']) : ''; ?>">
                    <?php if (isset($this->errors['translation'][$code]['title'])) { ?>
                      <div class="help-block"><?php echo $this->errors['translation'][$code]['title']; ?></div>
                    <?php } ?>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-2 control-label"><?php echo $this->text('First description %language', array('%language' => $language['native_name'])); ?></label>
                  <div class="col-md-8">
                    <textarea class="form-control summernote" name="category[translation][<?php echo $code; ?>][description_1]"><?php echo isset($category['translation'][$code]['description_1']) ? $this->xss($category['translation'][$code]['description_1']) : ''; ?></textarea>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-2 control-label"><?php echo $this->text('Second description %language', array('%language' => $language['native_name'])); ?></label>
                  <div class="col-md-8">
                    <textarea class="form-control summernote" name="category[translation][<?php echo $code; ?>][description_2]"><?php echo isset($category['translation'][$code]['description_2']) ? $this->xss($category['translation'][$code]['description_2']) : ''; ?></textarea>
                  </div>
                </div>
                <?php } ?>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#edit-category-accordion" href="#pane-data">
              <?php echo $this->text('Data'); ?>
              </a>
            </h4>
          </div>
          <div id="pane-data" class="panel-collapse collapse in always-visible">
            <div class="panel-body">
              <div class="form-group">
                <label class="col-md-2 control-label">
                  <span class="hint" title="<?php echo $this->text('Select Root to make this category parentless, i.e top level'); ?>">
                  <?php echo $this->text('Parent category'); ?>
                  </span>
                </label>
                <div class="col-md-4">
                  <?php if (isset($category['parent_id'])) { ?>
                  <?php $parent_id = $category['parent_id']; ?>
                  <?php } ?>
                  <select data-live-search="true" name="category[parent_id]" class="form-control selectpicker" id="parent_id">
                    <option value="0"><?php echo $this->text('Root'); ?></option>
                    <?php foreach ($categories as $category_id => $category_name) { ?>
                    <option value="<?php echo $category_id; ?>"<?php echo ($category_id == $parent_id) ? ' selected' : ''; ?>><?php echo $this->escape($category_name); ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group<?php echo $this->error('alias', ' has-error'); ?>">
                <label class="col-md-2 control-label">
                  <span class="hint" title="<?php echo $this->text('An alternative, SEO-friendly URL for the category. Leave empty to generate automatically'); ?>">
                  <?php echo $this->text('Alias'); ?>
                  </span>
                </label>
                <div class="col-md-4">
                  <input type="text" name="category[alias]" class="form-control" value="<?php echo isset($category['alias']) ? $this->escape($category['alias']) : ''; ?>" placeholder="<?php echo $this->text('Generate automatically'); ?>">
                  <?php if ($this->error('alias', true)) { ?>
                  <div class="help-block"><?php echo $this->error('alias'); ?></div>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a class="collapsed" data-toggle="collapse" data-parent="#edit-category-accordion" href="#pane-meta-description">
              <?php echo $this->text('Meta'); ?>
              </a>
            </h4>
          </div>
          <div id="pane-meta-description" class="panel-collapse collapse">
            <div class="panel-body">
              <div class="form-group<?php echo $this->error('meta_title', ' has-error'); ?>">
                <label class="col-md-2 control-label">
                  <span class="hint" title="<?php echo $this->text('HTML meta title tag on the category page. Important for SEO'); ?>">
                  <?php echo $this->text('Meta title'); ?>
                  </span>
                </label>
                <div class="col-md-4">
                  <input maxlength="60" name="category[meta_title]" class="form-control" value="<?php echo isset($category['meta_title']) ? $this->escape($category['meta_title']) : ''; ?>">
                  <?php if ($this->error('meta_title', true)) { ?>
                  <div class="help-block"><?php echo $this->error('meta_title'); ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-md-2 control-label">
                  <span class="hint" title="<?php echo $this->text('HTML meta description tag on the category page. Describes the category to search engines. Important for SEO'); ?>">
                  <?php echo $this->text('Meta description'); ?>
                  </span>
                </label>
                <div class="col-md-6">
                  <textarea maxlength="160" class="form-control" name="category[meta_description]"><?php echo isset($category['meta_description']) ? $this->escape($category['meta_description']) : ''; ?></textarea>
                </div>
              </div>
              <?php if ($languages) { ?>
              <div class="form-group">
                <div class="col-md-6 col-md-offset-2">
                  <a data-toggle="collapse" href="#meta-translations">
                    <?php echo $this->text('Translations'); ?> <span class="caret"></span>
                  </a>
                </div>
              </div>
              <div id="meta-translations" class="collapse translations<?php echo $this->error(null, ' in'); ?>">
              <?php foreach ($languages as $code => $language) { ?>
                <div class="form-group<?php echo isset($this->errors['translation'][$code]['meta_title']) ? ' has-error' : ''; ?>">
                  <label class="col-md-2 control-label"><?php echo $this->text('Meta title %language', array('%language' => $language['native_name'])); ?></label>
                  <div class="col-md-4">
                    <input maxlength="60" name="category[translation][<?php echo $code; ?>][meta_title]" class="form-control" id="title-<?php echo $code; ?>" value="<?php echo isset($category['translation'][$code]['meta_title']) ? $this->escape($category['translation'][$code]['meta_title']) : ''; ?>">
                    <?php if (isset($this->errors['translation'][$code]['meta_title'])) { ?>
                        <div class="help-block"><?php echo $this->errors['translation'][$code]['meta_title']; ?></div>
                    <?php } ?>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-2 control-label"><?php echo $this->text('Meta description %language', array('%language' => $language['native_name'])); ?></label>
                  <div class="col-md-6">
                    <textarea maxlength="160" class="form-control" name="category[translation][<?php echo $code; ?>][meta_description]"><?php echo isset($category['translation'][$code]['meta_description']) ? $this->escape($category['translation'][$code]['meta_description']) : ''; ?></textarea>
                  </div>
                </div>
              <?php } ?>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a class="collapsed" data-toggle="collapse" data-parent="#edit-category-accordion" href="#pane-image">
              <?php echo $this->text('Image'); ?>
              </a>
            </h4>
          </div>
          <div id="pane-image" class="panel-collapse collapse">
            <div class="panel-body">
              <div class="row">
                <div class="col-md-12">
                  <div class="row image-container">
                  <?php if (!empty($attached_images)) { ?>
                  <?php echo $attached_images; ?>
                  <?php } ?>
                  </div>
                </div>
              </div>
              <?php if ($this->access('file_upload')) { ?>
              <div class="row">
                <div class="col-md-12">
                  <label for="fileinput" class="btn btn-primary"><i class="fa fa-upload"></i> <?php echo $this->text('Upload'); ?></label>
                  <input class="hide" type="file" id="fileinput" name="file" data-entity-type="category" multiple="multiple" accept="image/*">
                </div>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
