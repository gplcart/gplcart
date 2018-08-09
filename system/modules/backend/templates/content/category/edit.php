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
    <div class="form-group row">
      <div class="col-md-6">
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
          <label class="btn btn-outline-secondary<?php echo !isset($category['status']) || $category['status'] ? ' active' : ''; ?>">
            <input name="category[status]" type="radio" autocomplete="off" value="1"<?php echo!isset($category['status']) || $category['status'] ? ' checked' : ''; ?>>
            <?php echo $this->text('Enabled'); ?>
          </label>
          <label class="btn btn-outline-secondary<?php echo !isset($category['status']) || $category['status'] ? '' : ' active'; ?>">
            <input name="category[status]" type="radio" autocomplete="off" value="0"<?php echo!isset($category['status']) || $category['status'] ? '' : ' checked'; ?>>
            <?php echo $this->text('Disabled'); ?>
          </label>
        </div>
        <div class="form-text">
          <div class="description">
              <?php echo $this->text('Disabled categories will not be available to customers and search engines'); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group row required<?php echo $this->error('title', ' has-error'); ?>">
      <div class="col-md-8">
        <label><?php echo $this->text('Title'); ?></label>
        <input maxlength="255" name="category[title]" class="form-control" value="<?php echo isset($category['title']) ? $this->e($category['title']) : ''; ?>">
        <div class="form-text">
          <?php echo $this->error('title'); ?>
          <div class="description">
            <?php echo $this->text('The title will be used on the category page and menu'); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group row">
      <div class="col-md-8">
        <label><?php echo $this->text('First description'); ?></label>
        <textarea class="form-control" rows="10" name="category[description_1]"><?php echo isset($category['description_1']) ? $this->filter($category['description_1']) : ''; ?></textarea>
        <div class="form-text">
          <div class="description">
              <?php echo $this->text('The appearance of the text is controlled by the corresponding theme. Usually it will be displayed at the top of the category page'); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group row">
      <div class="col-md-8">
        <label><?php echo $this->text('Second description'); ?></label>
        <textarea class="form-control" rows="10" name="category[description_2]"><?php echo isset($category['description_2']) ? $this->filter($category['description_2']) : ''; ?></textarea>
        <div class="form-text">
          <div class="description">
              <?php echo $this->text('The appearance of the text is controlled by the corresponding theme. Usually it will be displayed at the bottom of the category page'); ?>
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
      <?php foreach ($languages as $code => $language) { ?>
      <div class="form-group row<?php echo $this->error("translation.$code.title", ' has-error'); ?>">
        <div class="col-md-8">
          <label><?php echo $this->text('Title %language', array('%language' => $language['native_name'])); ?></label>
          <input maxlength="255" name="category[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo isset($category['translation'][$code]['title']) ? $this->e($category['translation'][$code]['title']) : ''; ?>">
          <div class="form-text"><?php echo $this->error("translation.$code.title"); ?></div>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-8">
          <label><?php echo $this->text('First description %language', array('%language' => $language['native_name'])); ?></label>
          <textarea class="form-control" rows="10" name="category[translation][<?php echo $code; ?>][description_1]"><?php echo isset($category['translation'][$code]['description_1']) ? $this->filter($category['translation'][$code]['description_1']) : ''; ?></textarea>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-8">
          <label><?php echo $this->text('Second description %language', array('%language' => $language['native_name'])); ?></label>
          <textarea class="form-control" rows="10" name="category[translation][<?php echo $code; ?>][description_2]"><?php echo isset($category['translation'][$code]['description_2']) ? $this->filter($category['translation'][$code]['description_2']) : ''; ?></textarea>
        </div>
      </div>
      <?php } ?>
    </div>
    <?php } ?>
    <div class="form-group row<?php echo $this->error('weight', ' has-error'); ?>">
      <div class="col-md-4">
        <label><?php echo $this->text('Weight'); ?></label>
        <input maxlength="255" name="category[weight]" class="form-control" value="<?php echo isset($category['weight']) ? $this->e($category['weight']) : 0; ?>">
        <div class="form-text">
          <?php echo $this->error('weight'); ?>
          <div class="description">
            <?php echo $this->text('Items are sorted in lists by the weight value. Lower value means higher position'); ?>
          </div>
        </div>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend></legend>
    <div class="form-group row">
      <div class="col-md-4">
        <label><?php echo $this->text('Parent category'); ?></label>
        <?php if (isset($category['parent_id'])) { ?>
          <?php $parent_id = $category['parent_id']; ?>
        <?php } ?>
        <select class="form-control" id="parent_id" name="category[parent_id]">
          <option value="0"><?php echo $this->text('Root'); ?></option>
          <?php foreach ($categories as $category_id => $category_name) { ?>
          <option value="<?php echo $category_id; ?>"<?php echo $category_id == $parent_id ? ' selected' : ''; ?>><?php echo $this->e($category_name); ?></option>
          <?php } ?>
        </select>
        <div class="form-text">
          <div class="description">
              <?php echo $this->text('Select a parent of the category. Specify "Root" for top-level parentless category'); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group row<?php echo $this->error('alias', ' has-error'); ?>">
      <div class="col-md-4">
        <label><?php echo $this->text('Alias'); ?></label>
        <input type="text" name="category[alias]" class="form-control" value="<?php echo isset($category['alias']) ? $this->e($category['alias']) : ''; ?>" placeholder="<?php echo $this->text('Generate automatically'); ?>">
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
      <div class="col-md-8">
        <label><?php echo $this->text('Meta title'); ?></label>
        <input maxlength="60" name="category[meta_title]" class="form-control" value="<?php echo isset($category['meta_title']) ? $this->e($category['meta_title']) : ''; ?>">
        <div class="form-text">
          <?php echo $this->error('meta_title'); ?>
          <div class="description">
            <?php echo $this->text('Optional text to be placed between %tags tags', array('%tags' => '<title>')); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group row">
      <div class="col-md-8">
        <label><?php echo $this->text('Meta description'); ?></label>
        <textarea maxlength="160" class="form-control" name="category[meta_description]"><?php echo isset($category['meta_description']) ? $this->e($category['meta_description']) : ''; ?></textarea>
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
      <?php foreach ($languages as $code => $language) { ?>
      <div class="form-group row<?php echo $this->error("translation.$code.meta_title", ' has-error'); ?>">
        <div class="col-md-8">
          <label><?php echo $this->text('Meta title %language', array('%language' => $language['native_name'])); ?></label>
          <input maxlength="60" name="category[translation][<?php echo $code; ?>][meta_title]" class="form-control" id="title-<?php echo $code; ?>" value="<?php echo isset($category['translation'][$code]['meta_title']) ? $this->e($category['translation'][$code]['meta_title']) : ''; ?>">
          <div class="form-text"><?php echo $this->error("translation.$code.meta_title"); ?></div>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-8">
          <label><?php echo $this->text('Meta description %language', array('%language' => $language['native_name'])); ?></label>
          <textarea maxlength="160" class="form-control" name="category[translation][<?php echo $code; ?>][meta_description]"><?php echo isset($category['translation'][$code]['meta_description']) ? $this->e($category['translation'][$code]['meta_description']) : ''; ?></textarea>
        </div>
      </div>
      <?php } ?>
    </div>
    <?php } ?>
  </fieldset>
  <div class="btn-toolbar">
      <?php if ($can_delete) { ?>
        <button class="btn btn-danger" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a class="btn cancel" href="<?php echo $this->url("admin/content/category/{$category_group['category_group_id']}"); ?>">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('category_add') || $this->access('category_edit')) { ?>
        <button class="btn btn-success save" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
  </div>
</form>
