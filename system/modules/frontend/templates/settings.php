<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\frontend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <fieldset>
    <legend><?php echo $this->text('Catalog'); ?></legend>
    <div class="form-group<?php echo $this->error('catalog_limit', ' has-error'); ?>">
      <label class="col-md-2 control-label">
        <?php echo $this->text('Products per page'); ?>
      </label>
      <div class="col-md-4">
        <input name="settings[catalog_limit]" class="form-control" value="<?php echo $this->e($settings['catalog_limit']); ?>">
        <div class="help-block">
          <?php echo $this->error('catalog_limit'); ?>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Default view'); ?></label>
      <div class="col-md-4">
        <select  name="settings[catalog_view]" class="form-control">
          <option value="grid"<?php echo $settings['catalog_view'] === 'grid' ? ' selected' : ''; ?>>
            <?php echo $this->text('Grid'); ?>
          </option>
          <option value="list"<?php echo $settings['catalog_view'] === 'list' ? ' selected' : ''; ?>>
            <?php echo $this->text('List'); ?>
          </option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Sorting field'); ?></label>
      <div class="col-md-4">
        <select  name="settings[catalog_sort]" class="form-control">
          <option value="price"<?php echo $settings['catalog_sort'] === 'price' ? ' selected' : ''; ?>>
            <?php echo $this->text('Price'); ?>
          </option>
          <option value="title"<?php echo $settings['catalog_sort'] === 'title' ? ' selected' : ''; ?>>
            <?php echo $this->text('Title'); ?>
          </option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Sorting direction'); ?></label>
      <div class="col-md-4">
        <select  name="settings[catalog_order]" class="form-control">
          <option value="asc"<?php echo $settings['catalog_order'] === 'asc' ? ' selected' : ''; ?>>
            <?php echo $this->text('Ascending'); ?>
          </option>
          <option value="desc"<?php echo $settings['catalog_order'] === 'desc' ? ' selected' : ''; ?>>
            <?php echo $this->text('Descending'); ?>
          </option>
        </select>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo $this->text('Image styles'); ?></legend>
    <?php foreach ($imagestyle_fields as $key => $label) { ?>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $label; ?></label>
      <div class="col-md-4">
        <select name="settings[<?php echo $key; ?>]" class="form-control">
          <option value=""><?php echo $this->text('Default'); ?></option>
          <?php foreach ($imagestyles as $id => $imagestyle) { ?>
          <option value="<?php echo $this->e($id); ?>"<?php echo $settings[$key] == $id ? ' selected' : ''; ?>><?php echo $this->e($imagestyle['name']); ?></option>
          <?php } ?>
        </select>
      </div>
    </div>
    <?php } ?>
  </fieldset>
  <div class="form-group">
    <div class="col-md-10 col-md-offset-2">
      <div class="btn-toolbar">
        <button class="btn btn-danger reset" name="reset" value="1" onclick="return confirm('<?php echo $this->text('Are you sure?'); ?>');">
          <?php echo $this->text('Reset'); ?>
        </button>
        <a href="<?php echo $this->url('admin/module/list'); ?>" class="btn btn-default cancel">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <button class="btn btn-default save" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
      </div>
    </div>
  </div>
</form>