<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" id="edit-page" enctype="multipart/form-data" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Description'); ?></div>
    <div class="panel-body">
      <div class="form-group required<?php echo $this->error('title', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Title'); ?></label>
        <div class="col-md-8">
          <input maxlength="255" name="page[title]" class="form-control" value="<?php echo (isset($page['title'])) ? $this->e($page['title']) : ''; ?>" autofocus>
          <div class="help-block">
            <?php echo $this->error('title'); ?>
            <div class="text-muted"><?php echo $this->text('Required. The title will be used on the page and menu'); ?></div>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('description', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Text'); ?></label>
        <div class="col-md-8">
          <textarea class="form-control" rows="10" data-wysiwyg="true" name="page[description]"><?php echo (isset($page['description'])) ? $this->filter($page['description']) : ''; ?></textarea>
          <div class="help-block">
            <?php echo $this->error('description'); ?>
            <div class="text-muted">
            <?php echo $this->text('Required. You can use any HTML but user can see only allowed tags'); ?>
            </div>
          </div>
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
        <?php foreach ($_languages as $code => $info) { ?>
        <div class="form-group<?php echo $this->error("translation.$code.title", ' has-error'); ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $info['native_name'])); ?></label>
          <div class="col-md-6">
            <input maxlength="255" name="page[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($page['translation'][$code]['title'])) ? $this->e($page['translation'][$code]['title']) : ''; ?>">
            <div class="help-block">
               <?php echo $this->error("translation.$code.title", ' has-error'); ?>
            </div>
          </div>
        </div>
        <div class="form-group<?php echo $this->error("translation.$code.description", ' has-error'); ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Description %language', array('%language' => $info['native_name'])); ?></label>
          <div class="col-md-6">
            <textarea class="form-control" rows="10" data-wysiwyg="true" name="page[translation][<?php echo $code; ?>][description]"><?php echo (isset($page['translation'][$code]['description'])) ? $this->filter($page['translation'][$code]['description']) : ''; ?></textarea>
            <div class="help-block">
               <?php echo $this->error("translation.$code.description"); ?>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Relations & accessibility'); ?></div>
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Status'); ?>
        </label>
        <div class="col-md-8">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo (!isset($page['status']) || $page['status']) ? ' active' : ''; ?>">
              <input name="page[status]" type="radio" autocomplete="off" value="1"<?php echo (!isset($page['status']) || $page['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo (isset($page['status']) && !$page['status']) ? ' active' : ''; ?>">
              <input name="page[status]" type="radio" autocomplete="off" value="0"<?php echo (isset($page['status']) && !$page['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
          <?php echo $this->text('Disabled pages will not be available for frontend users and search engines'); ?>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('store_id', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Store'); ?></label>
        <div class="col-md-4">
          <select class="form-control" name="page[store_id]">
            <option value=""><?php echo $this->text('- select -'); ?></option>
            <?php foreach ($_stores as $store_id => $store) { ?>
            <option value="<?php echo $store_id; ?>"<?php echo isset($page['store_id']) && $page['store_id'] == $store_id ? ' selected' : ''; ?>><?php echo $this->e($store['name']); ?></option>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->error('store_id'); ?>
            <div class="text-muted"><?php echo $this->text('Select a store where to display this page'); ?></div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Category'); ?></label>
        <div class="col-md-4">
          <select data-live-search="true" name="page[category_id]" class="form-control selectpicker">
            <?php foreach ($categories as $category_group_name => $options) { ?>
            <optgroup label="<?php echo $category_group_name; ?>">
            <?php foreach ($options as $category_id => $category_name) { ?>
            <option value="<?php echo $category_id; ?>"<?php echo (isset($page['category_id']) && $page['category_id'] == $category_id) ? ' selected' : ''; ?>><?php echo $this->e($category_name); ?></option>
            <?php } ?>
            <?php } ?>
          </select>
          <div class="help-block">
          <?php echo $this->text('Select a category of the page'); ?>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('alias', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Alias'); ?>
        </label>
        <div class="col-md-8">
          <input name="page[alias]" maxlength="255" class="form-control" value="<?php echo isset($page['alias']) ? $this->e($page['alias']) : ''; ?>" placeholder="<?php echo $this->text('Generate automatically'); ?>">
          <div class="help-block">
            <?php echo $this->error('alias'); ?>
            <div class="text-muted">
            <?php echo $this->text('An alternative path by which this page can be accessed. Leave empty to generate it automatically'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Image'); ?></div>
    <div class="panel-body">
      <?php if (!empty($attached_images)) { ?>
      <?php echo $attached_images; ?>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Meta'); ?></div>
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('meta_title', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Meta title'); ?></label>
        <div class="col-md-8">
          <input maxlength="60" name="page[meta_title]" class="form-control" value="<?php echo (isset($page['meta_title'])) ? $this->e($page['meta_title']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('meta_title'); ?>
            <div class="help-block">
            <?php echo $this->text('An optional text to be placed between %tags tags. Important for SEO', array('%tags' => '<title>')); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Meta description'); ?></label>
        <div class="col-md-8">
          <textarea maxlength="160" class="form-control" name="page[meta_description]"><?php echo (isset($page['meta_description'])) ? $this->e($page['meta_description']) : ''; ?></textarea>
          <div class="help-block">
            <?php echo $this->text('An optional text to be used in meta description tag. The tag is commonly used on search engine result pages (SERPs) to display preview snippets for a given page. Important for SEO'); ?>
          </div>
        </div>
      </div>
      <?php if (!empty($_languages)) { ?>
      <div class="form-group">
        <div class="col-md-10 col-md-offset-2">
          <a data-toggle="collapse" href="#meta-translations">
            <?php echo $this->text('Translations'); ?> <span class="caret"></span>
          </a>
        </div>
      </div>
      <div id="meta-translations" class="collapse translations<?php echo $this->error(null, ' in'); ?>">
        <?php foreach ($_languages as $code => $info) { ?>
        <div class="form-group<?php echo $this->error("translation.$code.meta_title", ' has-error'); ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Meta title %language', array('%language' => $info['native_name'])); ?></label>
          <div class="col-md-8">
            <input maxlength="60" name="page[translation][<?php echo $code; ?>][meta_title]" class="form-control" value="<?php echo (isset($page['translation'][$code]['meta_title'])) ? $this->e($page['translation'][$code]['meta_title']) : ''; ?>">
            <div class="help-block">
               <?php echo $this->error("translation.$code.meta_title"); ?>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="col-md-2 control-label"><?php echo $this->text('Meta description %language', array('%language' => $info['native_name'])); ?></label>
          <div class="col-md-8">
            <textarea maxlength="160" class="form-control" name="page[translation][<?php echo $code; ?>][meta_description]"><?php echo (isset($page['translation'][$code]['meta_description'])) ? $this->e($page['translation'][$code]['meta_description']) : ''; ?></textarea>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
    </div>
  </div>
  <?php if (isset($page['page_id'])) { ?>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Information'); ?></div>
    <div class="panel-body">
      <div class="row">
        <div class="col-md-12">
          <ul class="list-unstyled">
            <li><?php echo $this->text('Author'); ?>: <?php echo $page['author']; ?></li>
            <li><?php echo $this->text('Created'); ?>: <?php echo $this->date($page['created']); ?></li>
            <?php if ($page['modified'] > $page['created']) { ?>
            <li><?php echo $this->text('Modified'); ?>: <?php echo $this->date($page['modified']); ?></li>
            <?php } ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <?php if (isset($page['page_id']) && $this->access('page_delete')) { ?>
          <button name="delete" value="1" class="btn btn-danger" onclick="return confirm(GplCart.text('Delete? It cannot be undone!'));">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-10">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url('admin/content/page'); ?>" class="btn btn-default cancel">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('page_edit') || $this->access('page_add')) { ?>
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