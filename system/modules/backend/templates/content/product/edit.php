<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" id="edit-product" enctype="multipart/form-data" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Description'); ?></div>
    <div class="panel-body">
      <div class="form-group required<?php echo $this->error('title', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Title'); ?>
        </label>
        <div class="col-md-10">
          <input name="product[title]" maxlength="255" class="form-control" value="<?php echo isset($product['title']) ? $this->escape($product['title']) : ''; ?>" autofocus>
          <div class="help-block">
            <?php echo $this->error('title'); ?>
            <div class="text-muted"><?php echo $this->text('Required. The title will be used on the product page and menu'); ?></div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('description', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Description'); ?></label>
        <div class="col-md-10">
          <textarea class="form-control" data-wysiwyg="true" rows="10" name="product[description]"><?php echo isset($product['description']) ? $this->filter($product['description']) : ''; ?></textarea>
          <div class="help-block">
            <?php echo $this->error('description'); ?>
            <div class="text-muted"><?php echo $this->text('Required. You can use any HTML but user can see only allowed tags'); ?></div>
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
          <div class="col-md-10">
            <input name="product[translation][<?php echo $code; ?>][title]" maxlength="255" class="form-control" value="<?php echo isset($product['translation'][$code]['title']) ? $this->escape($product['translation'][$code]['title']) : ''; ?>">
            <div class="help-block">
              <?php echo $this->error("translation.$code.title"); ?>
              <div class="text-muted">
              <?php echo $this->text('An optional translation for language %name', array('%name' => $info['name'])); ?>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group<?php echo $this->error("translation.$code.description", ' has-error'); ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Description %language', array('%language' => $info['native_name'])); ?></label>
          <div class="col-md-10">
            <textarea class="form-control" rows="10" data-wysiwyg="true" name="product[translation][<?php echo $code; ?>][description]"><?php echo isset($product['translation'][$code]['description']) ? $this->filter($product['translation'][$code]['description']) : ''; ?></textarea>
            <div class="help-block">
              <?php echo $this->error("translation.$code.description"); ?>
              <div class="text-muted">
              <?php echo $this->text('An optional translation for language %name', array('%name' => $info['name'])); ?>
              </div>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Data'); ?></div>
    <div class="panel-body">
      <?php if (!empty($classes)) { ?>
      <div class="form-group<?php echo $this->error('product_class_id', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Product class'); ?>
        </label>
        <div class="col-md-4">
          <select class="form-control" name="product[product_class_id]">
            <option value=""><?php echo $this->text('None'); ?></option>
            <?php foreach ($classes as $class) { ?>
            <?php if (isset($product['product_class_id']) && $product['product_class_id'] == $class['product_class_id']) { ?>
            <option value="<?php echo $class['product_class_id']; ?>" selected> <?php echo $this->escape($class['title']); ?></option>
            <?php } else { ?>
            <option value="<?php echo $class['product_class_id']; ?>"> <?php echo $this->escape($class['title']); ?></option>
            <?php } ?>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->error('product_class_id'); ?>
            <div class="text-muted">
            <?php echo $this->text('Product class is a set of attributes and options that defines the type of the product'); ?>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>
      <div class="form-group">
        <div class="col-md-offset-2 col-md-4<?php echo $this->error('sku', ' has-error'); ?>">
          <label><?php echo $this->text('SKU'); ?></label>
          <input name="product[sku]" class="form-control" maxlength="255" value="<?php echo isset($product['sku']) ? $this->escape($product['sku']) : ''; ?>" placeholder="<?php echo $this->text('Generate automatically'); ?>">
          <div class="help-block">
            <?php echo $this->error('sku'); ?>
            <div class="text-muted">
            <?php echo $this->text('SKU is an identificator that allows it to be tracked for inventory purposes. Leave empty to generate it automatically'); ?>
            </div>
          </div>
        </div>
        <div class="col-md-2<?php echo $this->error('price', ' has-error'); ?>">
          <label><?php echo $this->text('Price'); ?></label>
          <div class="input-group">
            <input name="product[price]" class="form-control" value="<?php echo isset($product['price']) ? $product['price'] : 0; ?>">
            <span class="input-group-addon"><?php echo isset($product['currency']) ? $this->escape($product['currency']) : $this->escape($default_currency); ?></span>
          </div>
          <div class="help-block">
            <?php echo $this->error('price'); ?>
            <div class="text-muted"><?php echo $this->text('Integer or decimal value'); ?></div>
          </div>
        </div>
        <div class="col-md-3<?php echo $this->error('stock', ' has-error'); ?>">
          <label><?php echo $this->text('Stock'); ?></label>
          <div class="input-group">
            <input name="product[stock]" class="form-control" value="<?php echo isset($product['stock']) ? $product['stock'] : 0; ?>">
            <div class="input-group-btn">
              <div class="btn-group" data-toggle="buttons">
                <?php $subtract = isset($product['subtract']) ? $product['subtract'] : $subtract_default; ?>
                <label class="btn btn-default<?php echo $subtract ? ' active' : ''; ?>">
                  <input name="product[subtract]" type="checkbox" autocomplete="off" value="1"<?php echo $subtract ? ' checked' : ''; ?>><?php echo $this->text('Subtract'); ?>
                </label>
              </div>
            </div>
          </div>
          <div class="help-block">
            <?php echo $this->error('stock'); ?>
            <div class="text-muted">
              <?php echo $this->text('Quantity of the product kept on the premises of the store. If selected "Subtract", ordered products will be subtracted from the stock level'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="option-form-wrapper"><?php echo $option_form; ?></div>
  <div id="attribute-form-wrapper"><?php echo $attribute_form; ?></div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Relations & accessibility'); ?></div>
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo (!isset($product['status']) || $product['status']) ? ' active' : ''; ?>">
              <input name="product[status]" type="radio" autocomplete="off" value="1"<?php echo (!isset($product['status']) || $product['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo (!isset($product['status']) || $product['status']) ? '' : ' active'; ?>">
              <input name="product[status]" type="radio" autocomplete="off" value="0"<?php echo (!isset($product['status']) || $product['status']) ? '' : ' checked'; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
          <?php echo $this->text('Disabled products will not be available for frontend users and search engines'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Store'); ?></label>
        <div class="col-md-4">
          <select class="form-control" name="product[store_id]">
            <?php foreach ($_stores as $store_id => $store) { ?>
            <option value="<?php echo $store_id; ?>"<?php echo isset($product['store_id']) && $product['store_id'] == $store_id ? ' selected' : ''; ?>><?php echo $this->escape($store['name']); ?></option>
            <?php } ?>
          </select>
          <div class="help-block">
          <?php echo $this->text('Select a store where to display this product'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Brand'); ?></label>
        <div class="col-md-4">
          <select name="product[brand_category_id]" class="form-control">
            <option value="0"><?php echo $this->text('- select -'); ?></option>
            <?php if (!empty($categories['brand'])) { ?>
            <?php foreach ($categories['brand'] as $id => $name) { ?>
            <?php if (isset($product['brand_category_id']) && $product['brand_category_id'] == $id) { ?>
            <option value="<?php echo $id; ?>" selected><?php echo $this->escape($name); ?></option>
            <?php } else { ?>
            <option value="<?php echo $id; ?>"><?php echo $this->escape($name); ?></option>
            <?php } ?>
            <?php } ?>
            <?php } ?>
          </select>
          <div class="help-block">
          <?php echo $this->text('Trademark or manufacturer name identifying the product'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Category'); ?></label>
        <div class="col-md-4">
          <select data-live-search="true" name="product[category_id]" class="form-control selectpicker">
            <option value="0"><?php echo $this->text('- select -'); ?></option>
            <?php if (!empty($categories['catalog'])) { ?>
            <?php foreach ($categories['catalog'] as $id => $name) { ?>
            <?php if (isset($product['category_id']) && $product['category_id'] == $id) { ?>
            <option value="<?php echo $id; ?>" selected><?php echo $this->escape($name); ?></option>
            <?php } else { ?>
            <option value="<?php echo $id; ?>"><?php echo $this->escape($name); ?></option>
            <?php } ?>
            <?php } ?>
            <?php } ?>
          </select>
          <div class="help-block">
          <?php echo $this->text('Catalog category for this product. Multiple categories are not supported'); ?>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('alias', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Alias'); ?></label>
        <div class="col-md-4">
          <input name="product[alias]" class="form-control" value="<?php echo isset($product['alias']) ? $this->escape($product['alias']) : ''; ?>" placeholder="<?php echo $this->text('Generate automatically'); ?>">
          <div class="help-block">
            <?php echo $this->error('alias'); ?>
            <div class="text-muted">
            <?php echo $this->text('An alternative path by which this product can be accessed. Leave empty to generate it automatically'); ?>
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
    <div class="panel-heading"><?php echo $this->text('Related'); ?></div>
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Product'); ?></label>
        <div class="col-md-10">
          <input class="form-control related-product" value="">
          <div class="help-block">
          <?php echo $this->text('Autocomplete field. Select some products to be related to this product'); ?>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div id="related-products">
            <?php if (!empty($related)) { ?>
            <?php foreach ($related as $related_product_id => $related_product) { ?>
            <span class="related-product-item tag">
              <input type="hidden" name="product[related][]" value="<?php echo $related_product_id; ?>">
              <span class="btn btn-default">
                <a target="_blank" href="<?php echo $related_product['url']; ?>">
                <?php echo $this->escape($related_product['title']); ?> (<?php echo $this->escape($related_product_id); ?>)
                </a>
                <span class="badge"><i class="fa fa-times remove"></i></span>
              </span>
            </span>
            <?php } ?>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Meta'); ?></div>
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('meta_title', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Meta title'); ?></label>
        <div class="col-md-10">
          <input name="product[meta_title]" maxlength="60" class="form-control" value="<?php echo isset($product['meta_title']) ? $this->escape($product['meta_title']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('meta_title'); ?>
            <div class="text-muted">
            <?php echo $this->text('An optional text to be placed between %tags tags. Important for SEO', array('%tags' => '<title>')); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('meta_description', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Meta description'); ?></label>
        <div class="col-md-10">
          <textarea class="form-control" rows="3" maxlength="160" name="product[meta_description]"><?php echo isset($product['meta_description']) ? $this->escape($product['meta_description']) : ''; ?></textarea>
          <div class="help-block">
            <?php echo $this->error('meta_description'); ?>
            <div class="text-muted">
            <?php echo $this->text('An optional text to be used in meta description tag. The tag is commonly used on search engine result pages (SERPs) to display preview snippets for a given page. Important for SEO'); ?>
            </div>
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
          <div class="col-md-10">
            <input name="product[translation][<?php echo $code; ?>][meta_title]" maxlength="60" class="form-control" value="<?php echo isset($product['translation'][$code]['meta_title']) ? $this->escape($product['translation'][$code]['meta_title']) : ''; ?>">
            <div class="help-block">
               <?php echo $this->error("translation.$code.meta_title"); ?>
            </div>
          </div>
        </div>
        <div class="form-group<?php echo $this->error("translation.$code.meta_description", ' has-error'); ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Meta description %language', array('%language' => $info['native_name'])); ?></label>
          <div class="col-md-10">
            <textarea class="form-control" rows="3" maxlength="160" name="product[translation][<?php echo $code; ?>][meta_description]"><?php echo isset($product['translation'][$code]['meta_description']) ? $this->escape($product['translation'][$code]['meta_description']) : ''; ?></textarea>
            <div class="help-block">
              <?php echo $this->error("translation.$code.meta_description"); ?>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Dimensions'); ?></div>
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('width', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Width'); ?></label>
        <div class="col-md-4">
          <input name="product[width]" class="form-control" value="<?php echo isset($product['width']) ? $this->escape($product['width']) : $this->config->get('product_width', 0); ?>">
          <div class="help-block">
            <?php echo $this->error('width'); ?>
            <div class="text-muted">
            <?php echo $this->text('A physical width of the product. Used to calculate shipping cost'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('height', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Height'); ?></label>
        <div class="col-md-4">
          <input name="product[height]" class="form-control" value="<?php echo isset($product['height']) ? $this->escape($product['height']) : $this->config->get('product_height', 0); ?>">
          <div class="help-block">
            <?php echo $this->error('height'); ?>
            <div class="text-muted">
            <?php echo $this->text('A physical height of the product. Used to calculate shipping cost'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('length', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Length'); ?></label>
        <div class="col-md-4">
          <input name="product[length]" class="form-control" value="<?php echo isset($product['length']) ? $this->escape($product['length']) : $this->config->get('product_length', 0); ?>">
          <div class="help-block">
            <?php echo $this->error('length'); ?>
            <div class="text-muted">
            <?php echo $this->text('A physical length of the product. Used to calculate shipping cost'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('size_unit', ' has-error'); ?>">
        <label class="col-md-2 control-label">
        <?php echo $this->text('Size unit'); ?>
        </label>
        <div class="col-md-4">
          <?php $selected_size_unit = isset($product['size_unit']) ? $product['size_unit'] : $this->config('product_size_unit', 'mm'); ?>
          <select name="product[size_unit]" class="form-control">
            <?php foreach($size_units as $unit => $name) { ?>
            <option value="<?php echo $this->escape($unit); ?>"<?php echo ($selected_size_unit == $unit) ? ' selected' : ''; ?>><?php echo $this->escape($name); ?></option>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->error('size_unit'); ?>
            <div class="text-muted">
            <?php echo $this->text('Select a unit of measurement to use with the specified width, height and length'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('weight', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Weight'); ?></label>
        <div class="col-md-4">
          <input name="product[weight]" class="form-control" value="<?php echo isset($product['weight']) ? $this->escape($product['weight']) : $this->config->get('product_weight', 0); ?>">
          <div class="help-block">
            <?php echo $this->error('weight'); ?>
            <div class="text-muted">
            <?php echo $this->text('A physical weight of the product. Used to calculate shipping cost'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('weight_unit', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Weight unit'); ?></label>
        <div class="col-md-4">
          <?php $selected_weight_unit = isset($product['weight_unit']) ? $product['weight_unit'] : $this->config('product_weight_unit', 'g'); ?>
          <select name="product[weight_unit]" class="form-control">
            <?php foreach($weight_units as $unit => $name) { ?>
            <option value="<?php echo $this->escape($unit); ?>"<?php echo ($selected_weight_unit == $unit) ? ' selected' : ''; ?>><?php echo $this->escape($name); ?></option>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->error('weight_unit'); ?>
            <div class="text-muted">
            <?php echo $this->text('Select a unit of measurement to use with the specified weight'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php if (isset($product['product_id'])) { ?>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Information'); ?></div>
    <div class="panel-body">
      <div class="row">
        <div class="col-md-12">
          <ul class="list-unstyled">
            <li><?php echo $this->text('User'); ?>: <?php echo empty($product['author']) ? $this->text('Unknown') : $product['author']; ?></li>
            <li><?php echo $this->text('Created'); ?>: <?php echo $this->date($product['created']); ?></li>
            <?php if ($product['modified'] > $product['created']) { ?>
            <li><?php echo $this->text('Modified'); ?>: <?php echo $this->date($product['modified']); ?></li>
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
          <?php if (isset($product['product_id']) && $this->access('product_delete')) { ?>
          <button class="btn btn-danger" name="delete" value="1" onclick="return confirm(GplCart.text('Delete? It cannot be undone!'));">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-10">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url('admin/content/product'); ?>" class="btn btn-default"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
            <?php if ($this->access('product_edit') || $this->access('product_add')) { ?>
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