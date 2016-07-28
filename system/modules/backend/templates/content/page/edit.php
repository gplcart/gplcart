<form method="post" id="edit-page" onsubmit="return confirm();" class="form-horizontal<?php echo isset($form_errors) ? ' form-errors' : ''; ?>">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo (!isset($page['status']) || $page['status']) ? ' active' : ''; ?>">
        <input name="page[status]" type="radio" autocomplete="off" value="1"<?php echo (!isset($page['status']) || $page['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-default hint<?php echo (isset($page['status']) && !$page['status']) ? ' active' : ''; ?>" title="<?php echo $this->text('Disabled pages are unavailable for customers and search engines'); ?>">
        <input name="page[status]" type="radio" autocomplete="off" value="0"<?php echo (isset($page['status']) && !$page['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
        </label>
      </div>
    </div>
    <div class="col-md-6 text-right">
      <div class="btn-toolbar">
        <?php if (isset($page['page_id']) && $this->access('page_delete')) { ?>
        <button name="delete" value="1" class="btn btn-danger">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/content/page'); ?>" class="btn btn-default cancel"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('page_edit') || $this->access('page_add')) { ?>
        <button class="btn btn-primary save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="panel-group margin-top-20" id="page-accordion">
        <div class="panel panel-default">
          <div class="panel-heading clearfix">
            <h4 class="panel-title pull-left" style="padding-top:7px;">
              <a data-toggle="collapse" data-parent="#page-accordion" href="#pane-description"><?php echo $this->text('Description'); ?></a>
            </h4>
            <span class="pull-right"><i class="fa fa-chevron-up"></i></span>
          </div>
          <div id="pane-description" class="panel-collapse collapse always-visible in">
            <div class="panel-body">
              <div class="form-group required<?php echo isset($form_errors['title']) ? ' has-error' : ''; ?>">
                <label class="col-md-2 control-label">
                <?php echo $this->text('Title'); ?>
                </label>
                <div class="col-md-4">
                  <input maxlength="255" name="page[title]" class="form-control" value="<?php echo (isset($page['title'])) ? $this->escape($page['title']) : ''; ?>" autofocus>
                  <?php if (isset($form_errors['title'])) { ?>
                  <div class="help-block"><?php echo $form_errors['title']; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group required<?php echo isset($form_errors['description']) ? ' has-error' : ''; ?>">
                <label class="col-md-2 control-label"><?php echo $this->text('Text'); ?></label>
                <div class="col-md-8">
                  <textarea class="form-control summernote" name="page[description]"><?php echo (isset($page['description'])) ? $this->xss($page['description']) : ''; ?></textarea>
                  <?php if (isset($form_errors['description'])) { ?>
                  <div class="help-block"><?php echo $form_errors['description']; ?></div>
                  <?php } ?>
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
              <div id="translations" class="collapse translations<?php echo isset($form_errors) ? ' in' : ''; ?>">
                <?php foreach ($languages as $code => $info) { ?>
                <div class="form-group<?php echo isset($form_errors['translation'][$code]['title']) ? ' has-error' : ''; ?>">
                  <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $info['native_name'])); ?></label>
                  <div class="col-md-4">
                    <input maxlength="255" name="page[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($page['translation'][$code]['title'])) ? $this->escape($page['translation'][$code]['title']) : ''; ?>">
                    <?php if (isset($form_errors['translation'][$code]['title'])) { ?>
                    <div class="help-block"><?php echo $form_errors['translation'][$code]['title']; ?></div>
                    <?php } ?>
                  </div>
                </div>
                <div class="form-group<?php echo isset($form_errors['translation'][$code]['description']) ? ' has-error' : ''; ?>">
                  <label class="col-md-2 control-label"><?php echo $this->text('Text %language', array('%language' => $info['native_name'])); ?></label>
                  <div class="col-md-8">
                    <textarea class="form-control summernote" name="page[translation][<?php echo $code; ?>][description]"><?php echo (isset($page['translation'][$code]['description'])) ? $this->xss($page['translation'][$code]['description']) : ''; ?></textarea>
                    <?php if (isset($form_errors['translation'][$code]['description'])) { ?>
                    <div class="help-block"><?php echo $form_errors['translation'][$code]['description']; ?></div>
                    <?php } ?>
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
              <a data-toggle="collapse" data-parent="#page-accordion" href="#pane-data"><?php echo $this->text('Links'); ?></a>
            </h4>
          </div>
          <div id="pane-data" class="panel-collapse always-visible collapse in">
            <div class="panel-body">
              <div class="form-group">
                <label class="col-md-2 control-label">
                  <span class="hint" title="<?php echo $this->text('Show this page on the front page'); ?>">
                  <?php echo $this->text('Front page'); ?>
                  </span>
                </label>
                <div class="col-md-4">
                  <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default<?php echo empty($page['front']) ? '' : ' active'; ?>">
                      <input name="page[front]" type="radio" autocomplete="off" value="1"<?php echo empty($page['front']) ? '' : ' checked'; ?>>
                      <?php echo $this->text('Yes'); ?>
                    </label>
                    <label class="btn btn-default<?php echo empty($page['front']) ? ' active' : ''; ?>">
                      <input name="page[front]" type="radio" autocomplete="off" value="0"<?php echo empty($page['front']) ? ' checked' : ''; ?>>
                      <?php echo $this->text('No'); ?>
                    </label>
                  </div>
                </div>
              </div>
              <?php if (count($stores) > 1) { ?>
              <div class="form-group">
                <label class="col-md-2 control-label"><?php echo $this->text('Store'); ?></label>
                <div class="col-md-4">
                  <select class="form-control" name="page[store_id]">
                    <option value=""><?php echo $this->text('Select'); ?></option>
                    <?php foreach ($stores as $store_id => $store_name) { ?>
                    <option value="<?php echo $store_id; ?>"<?php echo (isset($page['store_id']) && $page['store_id'] == $store_id) ? ' selected' : ''; ?>><?php echo $this->escape($store_name); ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <?php } ?>
              <div class="form-group">
                <label class="col-md-2 control-label"><?php echo $this->text('Category'); ?></label>
                <div class="col-md-4">
                  <select data-live-search="true" name="page[category_id]" class="form-control selectpicker">
                   <?php foreach ($categories as $category_group_name => $options) { ?>
                    <optgroup label="<?php echo $category_group_name; ?>">
                    <?php foreach ($options as $category_id => $category_name) { ?>
                    <option value="<?php echo $category_id; ?>"<?php echo (isset($page['category_id']) && $page['category_id'] == $category_id) ? ' selected' : ''; ?>><?php echo $this->escape($category_name); ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group<?php echo isset($form_errors['alias']) ? ' has-error' : ''; ?>">
                <label class="col-md-2 control-label">
                  <span class="hint" title="<?php echo $this->text('An alternative URL of the page. Leave empty to generate automatically'); ?>">
                  <?php echo $this->text('Alias'); ?>
                  </span>
                </label>
                <div class="col-md-4">
                  <input name="page[alias]" maxlength="255" class="form-control" value="<?php echo isset($page['alias']) ? $this->escape($page['alias']) : ''; ?>" placeholder="<?php echo $this->text('Generate automatically'); ?>">
                  <?php if (isset($form_errors['alias'])) { ?>
                  <div class="help-block"><?php echo $form_errors['alias']; ?></div>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#page-accordion" href="#pane-images"><?php echo $this->text('Image'); ?></a>
            </h4>
          </div>
          <div id="pane-images" class="panel-collapse collapse">
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
                  <input class="hide" type="file" id="fileinput" data-entity-type="page" name="file" multiple="multiple" accept="image/*">
                </div>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#page-accordion" href="#pane-meta"><?php echo $this->text('Meta'); ?></a>
            </h4>
          </div>
          <div id="pane-meta" class="panel-collapse collapse">
            <div class="panel-body">
              <div class="form-group<?php echo isset($form_errors['meta_title']) ? ' has-error' : ''; ?>">
                <label class="col-md-2 control-label"><?php echo $this->text('Meta title'); ?></label>
                <div class="col-md-6">
                  <input maxlength="60" name="page[meta_title]" class="form-control" value="<?php echo (isset($page['meta_title'])) ? $this->escape($page['meta_title']) : ''; ?>">
                  <?php if (isset($form_errors['meta_title'])) { ?>
                  <div class="help-block"><?php echo $form_errors['meta_title']; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-md-2 control-label"><?php echo $this->text('Meta description'); ?></label>
                <div class="col-md-6">
                  <textarea maxlength="160" class="form-control" name="page[meta_description]"><?php echo (isset($page['meta_description'])) ? $this->escape($page['meta_description']) : ''; ?></textarea>
                </div>
              </div>
              <?php if (!empty($languages)) { ?>
              <div class="form-group">
                <div class="col-md-6 col-md-offset-2">
                  <a data-toggle="collapse" href="#meta-translations">
                    <?php echo $this->text('Translations'); ?> <span class="caret"></span>
                  </a>
                </div>
              </div>
              <div id="meta-translations" class="collapse translations<?php echo isset($form_errors) ? ' in' : ''; ?>">
                <?php foreach ($languages as $code => $info) { ?>
                <div class="form-group<?php echo isset($form_errors['translation'][$code]['meta_title']) ? ' has-error' : ''; ?>">
                  <label class="col-md-2 control-label"><?php echo $this->text('Meta title %language', array('%language' => $info['native_name'])); ?></label>
                  <div class="col-md-6">
                    <input maxlength="60" name="page[translation][<?php echo $code; ?>][meta_title]" class="form-control" value="<?php echo (isset($page['translation'][$code]['meta_title'])) ? $this->escape($page['translation'][$code]['meta_title']) : ''; ?>">
                    <?php if (isset($form_errors['translation'][$code]['meta_title'])) { ?>
                    <div class="help-block"><?php echo $form_errors['translation'][$code]['meta_title']; ?></div>
                    <?php } ?>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-2 control-label"><?php echo $this->text('Meta description %language', array('%language' => $info['native_name'])); ?></label>
                  <div class="col-md-6">
                    <textarea maxlength="160" class="form-control" name="page[translation][<?php echo $code; ?>][meta_description]"><?php echo (isset($page['translation'][$code]['meta_description'])) ? $this->escape($page['translation'][$code]['meta_description']) : ''; ?></textarea>
                  </div>
                </div>
                <?php } ?>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
        <?php if (isset($page['page_id'])) { ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#page-accordion" href="#pane-info">
              <?php echo $this->text('Information'); ?>
              </a>
            </h4>
          </div>
          <div id="pane-info" class="panel-collapse collapse">
            <div class="panel-body">
              <div class="row">
                <div class="col-md-4">
                  <ul class="list-unstyled">
                    <li><?php echo $this->text('User'); ?>: <?php echo $page['author']; ?></li>
                    <li><?php echo $this->text('Created'); ?>: <?php echo $this->date($page['created']); ?></li>
                    <?php if ($page['modified'] > $page['created']) { ?>
                    <li><?php echo $this->text('Modified'); ?>: <?php echo $this->date($page['modified']); ?></li>
                    <?php } ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>
</form>