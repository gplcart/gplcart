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
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <fieldset>
    <?php if (isset($page['page_id'])) { ?>
    <div class="form-group">
        <ul class="list-unstyled">
          <li><?php echo $this->text('Author'); ?>: <?php echo $page['author']; ?></li>
          <li><?php echo $this->text('Created'); ?>: <?php echo $this->date($page['created']); ?></li>
          <?php if ($page['modified'] > $page['created']) { ?>
          <li><?php echo $this->text('Modified'); ?>: <?php echo $this->date($page['modified']); ?></li>
          <?php } ?>
        </ul>
    </div>
    <?php } ?>
    <div class="form-group row">
      <div class="col-md-8">
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
          <label class="btn btn-outline-secondary<?php echo (!isset($page['status']) || $page['status']) ? ' active' : ''; ?>">
            <input name="page[status]" type="radio" autocomplete="off" value="1"<?php echo (!isset($page['status']) || $page['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
          </label>
          <label class="btn btn-outline-secondary<?php echo (isset($page['status']) && !$page['status']) ? ' active' : ''; ?>">
            <input name="page[status]" type="radio" autocomplete="off" value="0"<?php echo (isset($page['status']) && !$page['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
          </label>
        </div>
        <div class="form-text">
          <div class="description">
              <?php echo $this->text('Disabled pages will not be available to frontend users and search engines'); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group row">
      <div class="col-md-8">
        <label><?php echo $this->text('Blog post'); ?></label>
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
          <label class="btn btn-outline-secondary<?php echo empty($page['blog_post']) ? '' : ' active'; ?>">
            <input name="page[blog_post]" type="radio" autocomplete="off" value="1"<?php echo empty($page['blog_post']) ? '' : ' checked'; ?>><?php echo $this->text('Yes'); ?>
          </label>
          <label class="btn btn-outline-secondary<?php echo empty($page['blog_post']) ? ' active' : ''; ?>">
            <input name="page[blog_post]" type="radio" autocomplete="off" value="0"<?php echo empty($page['blog_post']) ? ' checked' : ''; ?>><?php echo $this->text('No'); ?>
          </label>
        </div>
        <div class="form-text">
          <div class="description">
              <?php echo $this->text('Whether to show this page as a blog post on the <a href="@url">blog page</a>', array('@url' => $this->url('blog'))); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group row required<?php echo $this->error('title', ' has-error'); ?>">
      <div class="col-md-10">
        <label><?php echo $this->text('Title'); ?></label>
        <input maxlength="255" name="page[title]" class="form-control" value="<?php echo (isset($page['title'])) ? $this->e($page['title']) : ''; ?>" autofocus>
        <div class="form-text">
          <?php echo $this->error('title'); ?>
          <div class="description"><?php echo $this->text('The title will be used on the page and menu'); ?></div>
        </div>
      </div>
    </div>
    <div class="form-group row required<?php echo $this->error('description', ' has-error'); ?>">
      <div class="col-md-10">
        <label><?php echo $this->text('Text'); ?></label>
        <textarea class="form-control" rows="10" name="page[description]"><?php echo (isset($page['description'])) ? $this->filter($page['description']) : ''; ?></textarea>
        <div class="form-text">
          <?php echo $this->error('description'); ?>
          <div class="description">
            <?php echo $this->text('You can use any HTML but user can see only allowed tags'); ?>
          </div>
        </div>
      </div>
    </div>
    <?php if (!empty($languages)) { ?>
    <div class="form-group">
        <a data-toggle="collapse" href="#translations">
          <?php echo $this->text('Translations'); ?> <span class="dropdown-toggle"></span>
        </a>
    </div>
    <div id="translations" class="collapse translations<?php echo $this->error(null, ' show'); ?>">
      <?php foreach ($languages as $code => $info) { ?>
      <div class="form-group row<?php echo $this->error("translation.$code.title", ' has-error'); ?>">
        <div class="col-md-10">
          <label><?php echo $this->text('Title %language', array('%language' => $info['native_name'])); ?></label>
          <input maxlength="255" name="page[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($page['translation'][$code]['title'])) ? $this->e($page['translation'][$code]['title']) : ''; ?>">
          <div class="form-text">
            <?php echo $this->error("translation.$code.title", ' has-error'); ?>
          </div>
        </div>
      </div>
      <div class="form-group row<?php echo $this->error("translation.$code.description", ' has-error'); ?>">
        <div class="col-md-10">
          <label><?php echo $this->text('Description %language', array('%language' => $info['native_name'])); ?></label>
          <textarea class="form-control" rows="10" name="page[translation][<?php echo $code; ?>][description]"><?php echo (isset($page['translation'][$code]['description'])) ? $this->filter($page['translation'][$code]['description']) : ''; ?></textarea>
          <div class="form-text">
            <?php echo $this->error("translation.$code.description"); ?>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
    <?php } ?>
  </fieldset>
  <fieldset>
    <legend></legend>
    <div class="form-group row required<?php echo $this->error('store_id', ' has-error'); ?>">
      <div class="col-md-4">
        <label><?php echo $this->text('Store'); ?></label>
        <select class="form-control" name="page[store_id]">
          <option value=""><?php echo $this->text('- select -'); ?></option>
          <?php foreach ($_stores as $store_id => $store) { ?>
          <option value="<?php echo $store_id; ?>"<?php echo isset($page['store_id']) && $page['store_id'] == $store_id ? ' selected' : ''; ?>><?php echo $this->e($store['name']); ?></option>
          <?php } ?>
        </select>
        <div class="form-text">
          <?php echo $this->error('store_id'); ?>
          <div class="description"><?php echo $this->text('Select a store where to display this item'); ?></div>
        </div>
      </div>
    </div>
    <div class="form-group row">
      <div class="col-md-4">
        <label><?php echo $this->text('Category'); ?></label>
        <select name="page[category_id]" class="form-control">
          <?php foreach ($categories as $category_group_name => $options) { ?>
          <optgroup label="<?php echo $category_group_name; ?>">
          <?php foreach ($options as $category_id => $category_name) { ?>
          <option value="<?php echo $category_id; ?>"<?php echo (isset($page['category_id']) && $page['category_id'] == $category_id) ? ' selected' : ''; ?>><?php echo $this->e($category_name); ?></option>
          <?php } ?>
          <?php } ?>
        </select>
        <div class="form-text">
          <div class="description"><?php echo $this->text('Select a category of the page'); ?></div>
        </div>
      </div>
    </div>
    <div class="form-group row<?php echo $this->error('alias', ' has-error'); ?>">
      <div class="col-md-8">
        <label><?php echo $this->text('Alias'); ?></label>
        <input name="page[alias]" maxlength="255" class="form-control" value="<?php echo isset($page['alias']) ? $this->e($page['alias']) : ''; ?>" placeholder="<?php echo $this->text('Generate automatically'); ?>">
        <div class="form-text">
          <?php echo $this->error('alias'); ?>
          <div class="description">
            <?php echo $this->text('Alternative path by which the entity item can be accessed. Leave empty to generate it automatically'); ?>
          </div>
        </div>
      </div>
    </div>
  </fieldset>
  <?php if (!empty($attached_images)) { ?>
  <fieldset>
    <legend></legend>
    <?php echo $attached_images; ?>
  </fieldset>
  <?php } ?>
  <fieldset>
    <legend></legend>
    <div class="form-group row<?php echo $this->error('meta_title', ' has-error'); ?>">
      <div class="col-md-10">
        <label><?php echo $this->text('Meta title'); ?></label>
        <input maxlength="60" name="page[meta_title]" class="form-control" value="<?php echo (isset($page['meta_title'])) ? $this->e($page['meta_title']) : ''; ?>">
        <div class="form-text">
          <?php echo $this->error('meta_title'); ?>
          <div class="description">
            <?php echo $this->text('Optional text to be placed between %tags tags', array('%tags' => '<title>')); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group row">
      <div class="col-md-10">
        <label><?php echo $this->text('Meta description'); ?></label>
        <textarea maxlength="160" class="form-control" name="page[meta_description]"><?php echo (isset($page['meta_description'])) ? $this->e($page['meta_description']) : ''; ?></textarea>
        <div class="form-text">
          <div class="description">
              <?php echo $this->text('An optional text to be used in meta description tag'); ?>
          </div>
        </div>
      </div>
    </div>
    <?php if (!empty($languages)) { ?>
    <div class="form-group">
        <a data-toggle="collapse" href="#meta-translations">
          <?php echo $this->text('Translations'); ?> <span class="dropdown-toggle"></span>
        </a>
    </div>
    <div id="meta-translations" class="collapse translations<?php echo $this->error(null, ' show'); ?>">
      <?php foreach ($languages as $code => $info) { ?>
      <div class="form-group row<?php echo $this->error("translation.$code.meta_title", ' has-error'); ?>">
        <div class="col-md-10">
          <label><?php echo $this->text('Meta title %language', array('%language' => $info['native_name'])); ?></label>
          <input maxlength="60" name="page[translation][<?php echo $code; ?>][meta_title]" class="form-control" value="<?php echo (isset($page['translation'][$code]['meta_title'])) ? $this->e($page['translation'][$code]['meta_title']) : ''; ?>">
          <div class="form-text">
            <?php echo $this->error("translation.$code.meta_title"); ?>
          </div>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-10">
          <label><?php echo $this->text('Meta description %language', array('%language' => $info['native_name'])); ?></label>
          <textarea maxlength="160" class="form-control" name="page[translation][<?php echo $code; ?>][meta_description]"><?php echo (isset($page['translation'][$code]['meta_description'])) ? $this->e($page['translation'][$code]['meta_description']) : ''; ?></textarea>
        </div>
      </div>
      <?php } ?>
    </div>
    <?php } ?>
  </fieldset>
      <div class="btn-toolbar">
        <?php if (isset($page['page_id']) && $this->access('page_delete')) { ?>
        <button name="delete" value="1" class="btn btn-danger" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/content/page'); ?>" class="btn cancel">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('page_edit') || $this->access('page_add')) { ?>
        <button class="btn save" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
</form>