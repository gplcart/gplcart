<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" id="edit-category" enctype="multipart/form-data" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <input type="hidden" name="category[category_group_id]" value="<?php echo $category_group['category_group_id']; ?>">
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Description'); ?></div>
    <div class="panel-body">
      <div class="form-group required<?php echo $this->error('title', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Title'); ?>
        </label>
        <div class="col-md-8">
          <input maxlength="255" name="category[title]" class="form-control" value="<?php echo isset($category['title']) ? $this->e($category['title']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('title'); ?>
            <div class="text-muted">
              <?php echo $this->text('Required. The title will be used on the category page and menu'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('First description'); ?>
        </label>
        <div class="col-md-8">
          <textarea class="form-control" data-wysiwyg="true" rows="10" name="category[description_1]"><?php echo isset($category['description_1']) ? $this->filter($category['description_1']) : ''; ?></textarea>
          <div class="help-block">
            <?php echo $this->text('Optional. A text that usually placed at the top of the category page'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Second description'); ?>
        </label>
        <div class="col-md-8">
          <textarea class="form-control" data-wysiwyg="true" rows="10" name="category[description_2]"><?php echo isset($category['description_2']) ? $this->filter($category['description_2']) : ''; ?></textarea>
          <div class="help-block">
            <?php echo $this->text('Optional. A text that usually placed at the bottom of the category page'); ?>
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
        <?php foreach ($_languages as $code => $language) { ?>
        <div class="form-group<?php echo $this->error("translation.$code.title", ' has-error'); ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $language['native_name'])); ?></label>
          <div class="col-md-8">
            <input maxlength="255" name="category[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo isset($category['translation'][$code]['title']) ? $this->e($category['translation'][$code]['title']) : ''; ?>">
            <div class="help-block"><?php echo $this->error("translation.$code.title"); ?></div>
          </div>
        </div>
        <div class="form-group">
          <label class="col-md-2 control-label"><?php echo $this->text('First description %language', array('%language' => $language['native_name'])); ?></label>
          <div class="col-md-8">
            <textarea class="form-control" data-wysiwyg="true" rows="10" name="category[translation][<?php echo $code; ?>][description_1]"><?php echo isset($category['translation'][$code]['description_1']) ? $this->filter($category['translation'][$code]['description_1']) : ''; ?></textarea>
          </div>
        </div>
        <div class="form-group">
          <label class="col-md-2 control-label"><?php echo $this->text('Second description %language', array('%language' => $language['native_name'])); ?></label>
          <div class="col-md-8">
            <textarea class="form-control" data-wysiwyg="true" rows="10" name="category[translation][<?php echo $code; ?>][description_2]"><?php echo isset($category['translation'][$code]['description_2']) ? $this->filter($category['translation'][$code]['description_2']) : ''; ?></textarea>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading">
      <?php echo $this->text('Relations & accessibility'); ?>
    </div>
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Status'); ?>
        </label>
        <div class="col-md-6">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo (!isset($category['status']) || $category['status']) ? ' active' : ''; ?>">
              <input name="category[status]" type="radio" autocomplete="off" value="1"<?php echo (!isset($category['status']) || $category['status']) ? ' checked' : ''; ?>>
              <?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo (!isset($category['status']) || $category['status']) ? '' : ' active'; ?>">
              <input name="category[status]" type="radio" autocomplete="off" value="0"<?php echo (!isset($category['status']) || $category['status']) ? '' : ' checked'; ?>>
              <?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
            <?php echo $this->text('Disabled categories will not be available for frontend users and search engines'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Parent category'); ?>
        </label>
        <div class="col-md-4">
          <?php if (isset($category['parent_id'])) { ?>
          <?php $parent_id = $category['parent_id']; ?>
          <?php } ?>
          <select data-live-search="true" name="category[parent_id]" class="form-control selectpicker" id="parent_id">
            <option value="0"><?php echo $this->text('Root'); ?></option>
            <?php foreach ($categories as $category_id => $category_name) { ?>
            <option value="<?php echo $category_id; ?>"<?php echo ($category_id == $parent_id) ? ' selected' : ''; ?>><?php echo $this->e($category_name); ?></option>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select a parent of the category. Specify "Root" for top-level parentless category'); ?>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('alias', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Alias'); ?>
        </label>
        <div class="col-md-6">
          <input type="text" name="category[alias]" class="form-control" value="<?php echo isset($category['alias']) ? $this->e($category['alias']) : ''; ?>" placeholder="<?php echo $this->text('Generate automatically'); ?>">
          <div class="help-block">
            <?php echo $this->error('alias'); ?>
            <div class="text-muted">
              <?php echo $this->text('An alternative path by which this category can be accessed. Leave empty to generate it automatically'); ?>
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
        <label class="col-md-2 control-label">
          <?php echo $this->text('Meta title'); ?>
        </label>
        <div class="col-md-8">
          <input maxlength="60" name="category[meta_title]" class="form-control" value="<?php echo isset($category['meta_title']) ? $this->e($category['meta_title']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('meta_title'); ?>
            <div class="help-block">
              <?php echo $this->text('An optional text to be placed between %tags tags. Important for SEO', array('%tags' => '<title>')); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Meta description'); ?>
        </label>
        <div class="col-md-8">
          <textarea maxlength="160" class="form-control" name="category[meta_description]"><?php echo isset($category['meta_description']) ? $this->e($category['meta_description']) : ''; ?></textarea>
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
        <?php foreach ($_languages as $code => $language) { ?>
        <div class="form-group<?php echo $this->error("translation.$code.meta_title", ' has-error'); ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Meta title %language', array('%language' => $language['native_name'])); ?></label>
          <div class="col-md-8">
            <input maxlength="60" name="category[translation][<?php echo $code; ?>][meta_title]" class="form-control" id="title-<?php echo $code; ?>" value="<?php echo isset($category['translation'][$code]['meta_title']) ? $this->e($category['translation'][$code]['meta_title']) : ''; ?>">
            <div class="help-block"><?php echo $this->error("translation.$code.meta_title"); ?></div>
          </div>
        </div>
        <div class="form-group">
          <label class="col-md-2 control-label"><?php echo $this->text('Meta description %language', array('%language' => $language['native_name'])); ?></label>
          <div class="col-md-8">
            <textarea maxlength="160" class="form-control" name="category[translation][<?php echo $code; ?>][meta_description]"><?php echo isset($category['translation'][$code]['meta_description']) ? $this->e($category['translation'][$code]['meta_description']) : ''; ?></textarea>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <?php if ($can_delete) { ?>
          <button class="btn btn-danger" name="delete" value="1" onclick="return confirm(GplCart.text('Delete? It cannot be undone!'));">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-10">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url("admin/content/category/{$category_group['category_group_id']}"); ?>" class="btn btn-default cancel">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('category_add') || $this->access('category_edit')) { ?>
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
