<form method="post" id="edit-product" onsubmit="return confirm();" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <?php if (isset($product['currency'])) { ?>
  <input type="hidden" name="product[currency]" value="<?php echo $this->escape($product['currency']); ?>">
  <?php } ?>
  <div class="row">
    <div class="col-md-8">
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Description'); ?></div>
        <div class="panel-body">
          <div class="form-group required<?php echo isset($this->errors['title']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <?php echo $this->text('Title'); ?>
            </label>
            <div class="col-md-10">
              <input name="product[title]" maxlength="255" class="form-control" value="<?php echo isset($product['title']) ? $this->escape($product['title']) : ''; ?>" autofocus>
              <?php if (isset($this->errors['title'])) { ?>
              <div class="help-block"><?php echo $this->errors['title']; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-2 control-label"><?php echo $this->text('Description'); ?></label>
            <div class="col-md-10">
              <textarea class="form-control summernote" name="product[description]"><?php echo isset($product['description']) ? $this->xss($product['description']) : ''; ?></textarea>
            </div>
          </div>
          <?php if (!empty($languages)) { ?>
          <div class="form-group">
            <div class="col-md-10 col-md-offset-2">
              <a data-toggle="collapse" href="#translations">
                <?php echo $this->text('Translations'); ?> <span class="caret"></span>
              </a>
            </div>
          </div>
          <div id="translations" class="collapse translations<?php echo!empty($this->errors) ? ' in' : ''; ?>">
            <?php foreach ($languages as $code => $info) { ?>
            <div class="form-group<?php echo isset($this->errors['translation'][$code]['title']) ? ' has-error' : ''; ?>">
              <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $info['native_name'])); ?></label>
              <div class="col-md-10">
                <input name="product[translation][<?php echo $code; ?>][title]" maxlength="255" class="form-control" value="<?php echo isset($product['translation'][$code]['title']) ? $this->escape($product['translation'][$code]['title']) : ''; ?>">
                <?php if (isset($this->errors['translation'][$code]['title'])) { ?>
                <div class="help-block"><?php echo $this->errors['translation'][$code]['title']; ?></div>
                <?php } ?>
              </div>
            </div>
            <div class="form-group">
              <label class="col-md-2 control-label"><?php echo $this->text('Description %language', array('%language' => $info['native_name'])); ?></label>
              <div class="col-md-10">
                <textarea class="form-control summernote" name="product[translation][<?php echo $code; ?>][description]"><?php echo isset($product['translation'][$code]['description']) ? $this->xss($product['translation'][$code]['description']) : ''; ?></textarea>
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
          <?php if ($classes) { ?>
          <div class="form-group">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Product class is a set of attributes and options that defines type of the product'); ?>">
                <?php echo $this->text('Product class'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <select class="form-control" name="product[product_class_id]">
                <option value="0"><?php echo $this->text('None'); ?></option>
                <?php foreach ($classes as $class) { ?>
                <?php if (isset($product['product_class_id']) && $product['product_class_id'] == $class['product_class_id']) { ?>
                <option value="<?php echo $class['product_class_id']; ?>" selected> <?php echo $this->escape($class['title']); ?></option>
                <?php } else { ?>
                <option value="<?php echo $class['product_class_id']; ?>"> <?php echo $this->escape($class['title']); ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
          <?php } ?>
          <div class="form-group">
            <div class="col-md-offset-2 col-md-4<?php echo isset($this->errors['sku']) ? ' has-error' : ''; ?>">
              <label>
                <span class="hint" title="<?php echo $this->text('SKU is an identificator that allows it to be tracked for inventory purposes. Leave empty to generate automatically'); ?>">
                  <?php echo $this->text('SKU'); ?>
                </span>
              </label>
              <input name="product[sku]" class="form-control" maxlength="255" value="<?php echo isset($product['sku']) ? $this->escape($product['sku']) : ''; ?>" placeholder="<?php echo $this->text('Generate automatically'); ?>">
              <?php if (isset($this->errors['sku'])) { ?>
                <div class="help-block"><?php echo $this->errors['sku']; ?></div>
              <?php } ?>
            </div>
            <div class="col-md-2<?php echo isset($this->errors['price']) ? ' has-error' : ''; ?>">
              <label>
                <span class="hint" title="<?php echo $this->text('Integer or decimal value. Leave empty to set 0'); ?>">
                  <?php echo $this->text('Price'); ?>
                </span>
              </label>
              <div class="input-group">
                <input type="number" min="0" step="any" pattern="[0-9]+([\.|,][0-9]+)?" name="product[price]" class="form-control" value="<?php echo isset($product['price']) ? $product['price'] : ''; ?>">
                <span class="input-group-addon"><?php echo isset($product['currency']) ? $this->escape($product['currency']) : $this->escape($default_currency); ?></span>
              </div>
              <?php if (isset($this->errors['price'])) { ?>
              <div class="help-block"><?php echo $this->errors['price']; ?></div>
              <?php } ?>
            </div>
            <div class="col-md-3<?php echo isset($this->errors['stock']) ? ' has-error' : ''; ?>">
              <label>
                <span class="hint" title="<?php echo $this->text('Quantity of the product kept on the premises of the store'); ?>">
                <?php echo $this->text('Stock'); ?>
                </span>
              </label>
              <div class="input-group">
                <input name="product[stock]" class="form-control" type="number" min="0" step="1" pattern="\d*" maxlength="6" value="<?php echo isset($product['stock']) ? $product['stock'] : ''; ?>">
                <div class="input-group-btn hint" title="<?php echo $this->text('If selected, the ordered products will be subtracted from the stock level'); ?>">
                  <div class="btn-group" data-toggle="buttons">
                    <?php $subtract = isset($product['subtract']) ? $product['subtract'] : $this->config->get('product_subtract', 1); ?>
                    <label class="btn btn-default<?php echo $subtract ? ' active' : ''; ?>">
                      <input name="product[subtract]" type="checkbox" autocomplete="off" value="1"<?php echo $subtract ? ' checked' : ''; ?>><?php echo $this->text('Subtract'); ?>
                    </label>
                  </div>
                </div>
              </div>
              <?php if (isset($this->errors['stock'])) { ?>
              <div class="help-block"><?php echo $this->errors['stock']; ?></div>
              <?php } ?>
            </div>
          </div>
          <div id="option-form-wrapper"><?php echo $option_form; ?></div>
          <div id="attribute-form-wrapper"><?php echo $attribute_form; ?></div>
        </div>
      </div>
      <div class="panel panel-default image">
        <div class="panel-heading"><?php echo $this->text('Image'); ?></div>
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
              <label for="fileinput" class="btn btn-default">
                <i class="fa fa-upload"></i> <?php echo $this->text('Upload'); ?>
              </label>
              <input class="hide" type="file" id="fileinput" data-entity-type="product" name="file" multiple="multiple" accept="image/*">
            </div>
          </div>
          <?php } ?>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Related'); ?></div>
        <div class="panel-body">
          <div class="form-group">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Autocomplete field. Select a product that related to this product'); ?>">
              <?php echo $this->text('Product'); ?>
              </span>
            </label>
            <div class="col-md-10">
              <input class="form-control related-product" value="">
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
                    <a target="_blank" href="<?php echo $related_product['view_url']; ?>">
                    <?php echo $this->escape($related_product['title']); ?>
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
          <div class="form-group<?php echo isset($this->errors['meta_title']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('HTML meta title tag on the product page. Important for SEO'); ?>">
                <?php echo $this->text('Meta title'); ?>
              </span>
            </label>
            <div class="col-md-10">
              <input name="product[meta_title]" maxlength="60" class="form-control" value="<?php echo isset($product['meta_title']) ? $this->escape($product['meta_title']) : ''; ?>">
              <?php if (isset($this->errors['meta_title'])) { ?>
              <div class="help-block"><?php echo $this->errors['meta_title']; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php echo isset($this->errors['meta_description']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('HTML meta description tag on the product page. Describes the product to search engines. Important for SEO'); ?>">
              <?php echo $this->text('Meta description'); ?>
              </span>
            </label>
            <div class="col-md-10">
              <textarea class="form-control" rows="3" maxlength="160" name="product[meta_description]"><?php echo isset($product['meta_description']) ? $this->escape($product['meta_description']) : ''; ?></textarea>
              <?php if (isset($this->errors['meta_description'])) { ?>
              <div class="help-block"><?php echo $this->errors['meta_description']; ?></div>
              <?php } ?>
            </div>
          </div>
          <?php if (!empty($languages)) { ?>
          <div class="form-group">
            <div class="col-md-10 col-md-offset-2">
              <a data-toggle="collapse" href="#meta-translations">
                <?php echo $this->text('Translations'); ?> <span class="caret"></span>
              </a>
            </div>
          </div>
          <div id="meta-translations" class="collapse translations<?php echo!empty($this->errors) ? ' in' : ''; ?>">
            <?php foreach ($languages as $code => $info) { ?>
            <div class="form-group<?php echo isset($this->errors['translation'][$code]['meta_title']) ? ' has-error' : ''; ?>">
              <label class="col-md-2 control-label"><?php echo $this->text('Meta title %language', array('%language' => $info['native_name'])); ?></label>
              <div class="col-md-10">
                <input name="product[translation][<?php echo $code; ?>][meta_title]" maxlength="60" class="form-control" value="<?php echo isset($product['translation'][$code]['meta_title']) ? $this->escape($product['translation'][$code]['meta_title']) : ''; ?>">
                <?php if (isset($this->errors['translation'][$code]['meta_title'])) { ?>
                <div class="help-block"><?php echo $this->errors['translation'][$code]['meta_title']; ?></div>
                <?php } ?>
              </div>
            </div>
            <div class="form-group<?php echo isset($this->errors['translation'][$code]['meta_description']) ? ' has-error' : ''; ?>">
              <label class="col-md-2 control-label"><?php echo $this->text('Meta description %language', array('%language' => $info['native_name'])); ?></label>
              <div class="col-md-10">
                <textarea class="form-control" rows="3" maxlength="160" name="product[translation][<?php echo $code; ?>][meta_description]"><?php echo isset($product['translation'][$code]['meta_description']) ? $this->escape($product['translation'][$code]['meta_description']) : ''; ?></textarea>
                <?php if (isset($this->errors['translation'][$code]['meta_description'])) { ?>
                <div class="help-block"><?php echo $this->errors['translation'][$code]['meta_description']; ?></div>
                <?php } ?>
              </div>
            </div>
            <?php } ?>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Relations & accessibility'); ?></div>
        <div class="panel-body">
          <div class="form-group">
            <label class="col-md-4 control-label">
              <span class="hint" title="<?php echo $this->text('Disabled products are unavailable for customers and search engines'); ?>">
              <?php echo $this->text('Status'); ?>
              </span>
            </label>
            <div class="col-md-8">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default<?php echo (!isset($product['status']) || $product['status']) ? ' active' : ''; ?>">
                  <input name="product[status]" type="radio" autocomplete="off" value="1"<?php echo (!isset($product['status']) || $product['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
                </label>
                <label class="btn btn-default<?php echo (!isset($product['status']) || $product['status']) ? '' : ' active'; ?>">
                  <input name="product[status]" type="radio" autocomplete="off" value="0"<?php echo (!isset($product['status']) || $product['status']) ? '' : ' checked'; ?>><?php echo $this->text('Disabled'); ?>
                </label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 control-label">
              <span class="hint" title="<?php echo $this->text('Show this product on the front page'); ?>">
              <?php echo $this->text('Front page'); ?>
              </span>
            </label>
            <div class="col-md-8">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default<?php echo empty($product['front']) ? '' : ' active'; ?>">
                  <input name="product[front]" type="radio" autocomplete="off" value="1"<?php echo empty($product['front']) ? '' : ' checked'; ?>>
                  <?php echo $this->text('Yes'); ?>
                </label>
                <label class="btn btn-default<?php echo empty($product['front']) ? ' active' : ''; ?>">
                  <input name="product[front]" type="radio" autocomplete="off" value="0"<?php echo empty($product['front']) ? ' checked' : ''; ?>>
                  <?php echo $this->text('No'); ?>
                </label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 control-label">
              <span class="hint" title="<?php echo $this->text('This product will be displayed only in the selected store'); ?>">
              <?php echo $this->text('Store'); ?>
              </span>
            </label>
            <div class="col-md-8">
              <select class="form-control" name="product[store_id]">
                <?php foreach ($stores as $store_id => $store_name) { ?>
                <option value="<?php echo $store_id; ?>"<?php echo (isset($product['store_id']) && $product['store_id'] == $store_id) ? ' selected' : ''; ?>><?php echo $this->escape($store_name); ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 control-label">
              <span class="hint" title="<?php echo $this->text('Trademark or distinctive name identifying the product'); ?>">
              <?php echo $this->text('Brand'); ?>
              </span>
            </label>
            <div class="col-md-8">
              <select name="product[brand_category_id]" class="form-control">
                <option value=""></option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 control-label">
              <span class="hint" title="<?php echo $this->text('Category is a way to organize products in your store by the type of products you sell'); ?>">
              <?php echo $this->text('Category'); ?>
              </span>
            </label>
            <div class="col-md-8">
              <select data-live-search="true" name="product[category_id]" class="form-control selectpicker">
                <option value=""></option>
              </select>
            </div>
          </div>
          <div class="form-group<?php echo isset($this->errors['alias']) ? ' has-error' : ''; ?>">
            <label class="col-md-4 control-label">
              <span class="hint" title="<?php echo $this->text('An alternative, SEO-friendly URL for the product. Leave empty to generate automatically'); ?>">
              <?php echo $this->text('Alias'); ?>
              </span>
            </label>
            <div class="col-md-8">
              <input name="product[alias]" class="form-control" value="<?php echo isset($product['alias']) ? $this->escape($product['alias']) : ''; ?>" placeholder="<?php echo $this->text('Generate automatically'); ?>">
              <?php if (isset($this->errors['alias'])) { ?>
              <div class="help-block"><?php echo $this->errors['alias']; ?></div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Dimensions'); ?></div>
        <div class="panel-body">
          <div class="form-group<?php echo isset($this->errors['width']) ? ' has-error' : ''; ?>">
            <label class="col-md-4 control-label">
              <span class="hint" title="<?php echo $this->text('Physical width of the product. Used to calculate shipping cost'); ?>">
              <?php echo $this->text('Width'); ?>
              </span>
            </label>
            <div class="col-md-8">
              <input type="number" min="0" step="1" pattern="\d*" maxlength="6" name="product[width]" class="form-control" value="<?php echo isset($product['width']) ? $this->escape($product['width']) : $this->config->get('product_width', 0); ?>">
              <?php if (isset($this->errors['width'])) { ?>
                  <div class="help-block"><?php echo $this->errors['width']; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php echo isset($this->errors['height']) ? ' has-error' : ''; ?>">
            <label class="col-md-4 control-label">
              <span class="hint" title="<?php echo $this->text('Physical height of the product. Used to calculate shipping cost'); ?>">
              <?php echo $this->text('Height'); ?>
              </span>
            </label>
            <div class="col-md-8">
              <input type="number" min="0" step="1" pattern="\d*" maxlength="6" name="product[height]" class="form-control" value="<?php echo isset($product['height']) ? $this->escape($product['height']) : $this->config->get('product_height', 0); ?>">
              <?php if (isset($this->errors['height'])) { ?>
              <div class="help-block"><?php echo $this->errors['height']; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php echo isset($this->errors['length']) ? ' has-error' : ''; ?>">
            <label class="col-md-4 control-label">
              <span class="hint" title="<?php echo $this->text('Physical length/depth of the product. Used to calculate shipping cost'); ?>">
                  <?php echo $this->text('Length'); ?>
              </span>
            </label>
            <div class="col-md-8">
              <input type="number" min="0" step="1" pattern="\d*" maxlength="6" name="product[length]" class="form-control" value="<?php echo isset($product['length']) ? $this->escape($product['length']) : $this->config->get('product_length', 0); ?>">
              <?php if (isset($this->errors['length'])) { ?>
              <div class="help-block"><?php echo $this->errors['length']; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 control-label">
            <?php echo $this->text('Dimension unit'); ?>
            </label>
            <div class="col-md-8">
              <?php $volume_unit = isset($product['volume_unit']) ? $product['volume_unit'] : $this->config->get('product_volume_unit', 'mm'); ?>
              <select name="product[volume_unit]" class="form-control">
                <option value="mm"<?php echo ($volume_unit == 'mm') ? ' selected' : ''; ?>>
                <?php echo $this->text('millimeter'); ?>
                </option>
                <option value="cm"<?php echo ($volume_unit == 'cm') ? ' selected' : ''; ?>>
                <?php echo $this->text('centimetre'); ?>
                </option>
                <option value="in"<?php echo ($volume_unit == 'in') ? ' selected' : ''; ?>>
                <?php echo $this->text('inch'); ?>
                </option>
              </select>
            </div>
          </div>
          <div class="form-group<?php echo isset($this->errors['weight']) ? ' has-error' : ''; ?>">
            <label class="col-md-4 control-label">
              <span class="hint" title="<?php echo $this->text('Physical weight of the product. Used to calculate shipping cost'); ?>">
              <?php echo $this->text('Weight'); ?>
              </span>
            </label>
            <div class="col-md-8">
              <input type="number" min="0" step="1" pattern="\d*" maxlength="6" name="product[weight]" class="form-control" value="<?php echo isset($product['weight']) ? $this->escape($product['weight']) : $this->config->get('product_weight', 0); ?>">
              <?php if (isset($this->errors['weight'])) { ?>
              <div class="help-block"><?php echo $this->errors['weight']; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 control-label"><?php echo $this->text('Weight unit'); ?></label>
            <div class="col-md-8">
              <?php $weight_unit = isset($product['weight_unit']) ? $product['weight_unit'] : $this->config->get('product_weight_unit', 'g'); ?>
              <select name="product[weight_unit]" class="form-control">
                <option value="g"<?php echo ($weight_unit == 'g') ? ' selected' : ''; ?>>
                <?php echo $this->text('gram'); ?>
                </option>
                <option value="kg"<?php echo ($weight_unit == 'kg') ? ' selected' : ''; ?>>
                <?php echo $this->text('kilogram'); ?>
                </option>
                <option value="lb"<?php echo ($weight_unit == 'lb') ? ' selected' : ''; ?>>
                <?php echo $this->text('pound'); ?>
                </option>
                <option value="oz"<?php echo ($weight_unit == 'oz') ? ' selected' : ''; ?>>
                <?php echo $this->text('ounce'); ?>
                </option>
              </select>
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
                <li><?php echo $this->text('User'); ?>: <?php echo $product['author']; ?></li>
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
          <div class="btn-toolbar">
            <?php if (isset($product['product_id']) && $this->access('product_delete')) { ?>
            <button class="btn btn-danger" name="delete" value="1">
              <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
            </button>
            <?php } ?>
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