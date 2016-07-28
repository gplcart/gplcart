<form method="post" id="edit-module-settings" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-12 text-right">
      <div class="btn-toolbar">
        <a href="<?php echo $this->url('admin/module/theme'); ?>" class="btn btn-default cancel">
          <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
        </a>
        <button class="btn btn-primary save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('catalog_limit', ' has-error'); ?>">
    <label class="col-md-2 control-label">
      <span class="hint" title="<?php echo $this->text('Number of products per page in the product catalog'); ?>">
        <?php echo $this->text('Catalog product limit'); ?>
      </span>
    </label>
    <div class="col-md-4">
      <input name="settings[catalog_limit]" class="form-control" value="<?php echo $this->escape($settings['catalog_limit']); ?>">
      <?php if ($this->error('catalog_limit', true)) { ?>
      <div class="help-block"><?php echo $this->error('catalog_limit'); ?></div>
      <?php } ?>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('catalog_front_limit', ' has-error'); ?>">
    <label class="col-md-2 control-label">
      <span class="hint" title="<?php echo $this->text('Number of products to be shown on the front page'); ?>">
        <?php echo $this->text('Front page product limit'); ?>
      </span>
    </label>
    <div class="col-md-4">
      <input name="settings[catalog_front_limit]" class="form-control" value="<?php echo $this->escape($settings['catalog_front_limit']); ?>">
      <?php if ($this->error('catalog_front_limit', true)) { ?>
      <div class="help-block"><?php echo $this->error('catalog_front_limit'); ?></div>
      <?php } ?>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-10 col-md-offset-2"><h4><?php echo $this->text('Image styles'); ?></h4></div>
  </div>
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Category page (main)'); ?></label>
    <div class="col-md-4">
      <select name="settings[image_style_category]" class="form-control">
        <?php foreach ($imagestyles as $id => $name) { ?>
        <option value="<?php echo $id; ?>"<?php echo ($settings['image_style_category'] == $id) ? ' selected' : ''; ?>>
        <?php echo $this->escape($name); ?>
        </option>
        <?php } ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-2 control-label">
        <?php echo $this->text('Category page (child)'); ?>
    </label>
    <div class="col-md-4">
      <select name="settings[image_style_category_child]" class="form-control">
        <?php foreach ($imagestyles as $id => $name) { ?>
        <option value="<?php echo $id; ?>"<?php echo ($settings['image_style_category_child'] == $id) ? ' selected' : ''; ?>>
        <?php echo $this->escape($name); ?>
        </option>
        <?php } ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Product page (main)'); ?></label>
    <div class="col-md-4">
      <select name="settings[image_style_product]" class="form-control">
        <?php foreach ($imagestyles as $id => $name) { ?>
        <option value="<?php echo $id; ?>"<?php echo ($settings['image_style_product'] == $id) ? ' selected' : ''; ?>>
        <?php echo $this->escape($name); ?>
        </option>
        <?php } ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Product page (extra)'); ?></label>
    <div class="col-md-4">
      <select name="settings[image_style_product_extra]" class="form-control">
        <?php foreach ($imagestyles as $id => $name) { ?>
        <option value="<?php echo $id; ?>"<?php echo ($settings['image_style_product_extra'] == $id) ? ' selected' : ''; ?>>
        <?php echo $this->escape($name); ?>
        </option>
        <?php } ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Product catalog (grid)'); ?></label>
    <div class="col-md-4">
      <select name="settings[image_style_product_grid]" class="form-control">
        <?php foreach ($imagestyles as $id => $name) { ?>
        <option value="<?php echo $id; ?>"<?php echo ($settings['image_style_product_grid'] == $id) ? ' selected' : ''; ?>>
        <?php echo $this->escape($name); ?>
        </option>
        <?php } ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Product catalog (list)'); ?></label>
    <div class="col-md-4">
      <select name="settings[image_style_product_list]" class="form-control">
        <?php foreach ($imagestyles as $id => $name) { ?>
        <option value="<?php echo $id; ?>"<?php echo ($settings['image_style_product_list'] == $id) ? ' selected' : ''; ?>>
        <?php echo $this->escape($name); ?>
        </option>
        <?php } ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Cart'); ?></label>
    <div class="col-md-4">
      <select name="settings[image_style_cart]" class="form-control">
        <?php foreach ($imagestyles as $id => $name) { ?>
        <option value="<?php echo $id; ?>"<?php echo ($settings['image_style_cart'] == $id) ? ' selected' : ''; ?>>
        <?php echo $this->escape($name); ?>
        </option>
        <?php } ?>
      </select>
    </div>
  </div>
</form>