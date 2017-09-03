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
<form method="post" enctype="multipart/form-data" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <fieldset>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
      <div class="col-md-4">
        <div class="btn-group" data-toggle="buttons">
          <label class="btn btn-default<?php echo empty($store['status']) ? '' : ' active'; ?>">
            <input name="store[status]" type="radio" autocomplete="off" value="1"<?php echo empty($store['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
          </label>
          <label class="btn btn-default<?php echo empty($store['status']) ? ' active' : ''; ?>">
            <input name="store[status]" type="radio" autocomplete="off" value="0"<?php echo empty($store['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
          </label>
        </div>
        <div class="help-block">
          <?php echo $this->text('Only enabled stores are available publicly'); ?>
        </div>
      </div>
    </div>
    <?php if (!isset($store['store_id']) || $store['store_id']) { ?>
    <div class="form-group required<?php echo $this->error('name', ' has-error'); ?>">
      <label class="col-md-2 control-label">
        <?php echo $this->text('Name'); ?>
      </label>
      <div class="col-md-4">
        <input maxlength="255" name="store[name]" class="form-control" value="<?php echo isset($store['name']) ? $this->e($store['name']) : ''; ?>">
        <div class="help-block">
          <?php echo $this->error('name'); ?>
          <div class="text-muted"><?php echo $this->text('Required. A short name for administrators'); ?></div>
        </div>
      </div>
    </div>
    <?php } ?>
    <?php if (!$is_default) { ?>
    <div class="form-group required<?php echo $this->error('domain', ' has-error'); ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('Domain'); ?></label>
      <div class="col-md-4">
        <input maxlength="255" name="store[domain]" class="form-control" value="<?php echo isset($store['domain']) ? $this->e($store['domain']) : ''; ?>">
        <div class="help-block">
          <?php echo $this->error('domain'); ?>
          <div class="text-muted">
            <?php echo $this->text('Required. A domain name by which this store can be accessed'); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group<?php echo $this->error('basepath', ' has-error'); ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('Base path'); ?></label>
      <div class="col-md-4">
        <input maxlength="50" name="store[basepath]" class="form-control" value="<?php echo isset($store['basepath']) ? $this->e($store['basepath']) : ''; ?>">
        <div class="help-block">
          <?php echo $this->error('basepath'); ?>
          <div class="text-muted">
            <?php echo $this->text('An optional path if the store is not installed in the domain root'); ?>
          </div>
        </div>
      </div>
    </div>
    <?php } ?>
    <div class="required form-group<?php echo $this->error('data.title', ' has-error'); ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('Title'); ?></label>
      <div class="col-md-4">
        <input type="text" maxlength="70" name="store[data][title]" class="form-control" value="<?php echo $this->e($store['data']['title']); ?>">
        <div class="help-block">
          <?php echo $this->error('data.title'); ?>
          <div class="text-muted"><?php echo $this->text('Required. The default site name'); ?></div>
        </div>
      </div>
    </div>
    <div class="form-group required<?php echo $this->error('data.email', ' has-error'); ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('E-mail'); ?></label>
      <div class="col-md-4">
        <textarea name="store[data][email]" class="form-control"><?php echo $this->e($store['data']['email']); ?></textarea>
        <div class="help-block">
          <?php echo $this->error('data.email'); ?>
          <div class="text-muted">
            <?php echo $this->text('A list of e-mails, one per line'); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group<?php echo $this->error('data.owner', ' has-error'); ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('Owner'); ?></label>
      <div class="col-md-4">
        <input maxlength="32" name="store[data][owner]" class="form-control" value="<?php echo $this->e($store['data']['owner']); ?>">
        <div class="help-block">
          <?php echo $this->error('data.owner'); ?>
          <div class="text-muted">
            <?php echo $this->text('A name of the company that owns this store'); ?>
          </div>
        </div>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo $this->text('Description'); ?></legend>
    <div class="form-group<?php echo $this->error('data.meta_title', ' has-error'); ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('Meta title'); ?></label>
      <div class="col-md-4">
        <input type="text" maxlength="60" name="store[data][meta_title]" class="form-control" value="<?php echo $this->e($store['data']['meta_title']); ?>">
        <div class="help-block">
          <?php echo $this->error('data.meta_title'); ?>
          <div class="text-muted">
            <?php echo $this->text('An optional text to be placed between %tags tags', array('%tags' => '<title>')); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group<?php echo $this->error('data.meta_description', ' has-error'); ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('Meta description'); ?></label>
      <div class="col-md-4">
        <textarea maxlength="160" class="form-control" name="store[data][meta_description]"><?php echo $this->e($store['data']['meta_description']); ?></textarea>
        <div class="help-block">
          <?php echo $this->error('data.meta_description'); ?>
          <?php echo $this->text('An optional text to be used in meta description tag'); ?>
        </div>
      </div>
    </div>
    <?php if (!empty($_languages)) { ?>
    <div class="form-group">
      <div class="col-md-6 col-md-offset-2">
        <a data-toggle="collapse" href="#translations">
          <?php echo $this->text('Translations'); ?> <span class="caret"></span>
        </a>
      </div>
    </div>
    <div id="translations" class="collapse translations<?php echo $this->error(null, ' in'); ?>">
      <?php foreach ($_languages as $code => $info) { ?>
      <div class="form-group<?php echo $this->error("data.translation.$code.title", ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Title %language', array('%language' => $info['native_name'])); ?>
        </label>
        <div class="col-md-4">
          <input type="text" maxlength="70" name="store[data][translation][<?php echo $code; ?>][title]" class="form-control" id="title-<?php echo $code; ?>" value="<?php echo (isset($store['data']['translation'][$code]['title'])) ? $this->e($store['data']['translation'][$code]['title']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error("data.translation.$code.title"); ?>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error("data.translation.$code.meta_title", ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Meta title %language', array('%language' => $info['native_name'])); ?>
        </label>
        <div class="col-md-4">
          <input type="text" maxlength="60" name="store[data][translation][<?php echo $code; ?>][meta_title]" class="form-control" value="<?php echo (isset($store['data']['translation'][$code]['meta_title'])) ? $this->e($store['data']['translation'][$code]['meta_title']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error("data.translation.$code.meta_title"); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Meta description %language', array('%language' => $info['native_name'])); ?>
        </label>
        <div class="col-md-4">
          <textarea maxlength="160" id="description-<?php echo $code; ?>" class="form-control" name="store[data][translation][<?php echo $code; ?>][meta_description]"><?php echo (isset($store['data']['translation'][$code]['meta_description'])) ? $this->e($store['data']['translation'][$code]['meta_description']) : ''; ?></textarea>
        </div>
      </div>
      <?php } ?>
    </div>
    <?php } ?>
  </fieldset>
  <fieldset>
    <legend><?php echo $this->text('Contact'); ?></legend>
    <div class="row">
      <div class="col-md-6">
        <div class="form-group<?php echo $this->error('data.city', ' has-error'); ?>">
          <label class="col-md-4 control-label"><?php echo $this->text('City'); ?></label>
          <div class="col-md-8">
            <input name="store[data][city]" class="form-control" value="<?php echo $this->e($store['data']['city']); ?>">
            <div class="help-block">
              <?php echo $this->error('data.city'); ?>
              <div class="text-muted">
                <?php echo $this->text('City of the store'); ?>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group<?php echo $this->error('data.state', ' has-error'); ?>">
          <label class="col-md-4 control-label"><?php echo $this->text('State'); ?></label>
          <div class="col-md-8">
            <input name="store[data][state]" class="form-control" value="<?php echo $this->e($store['data']['state']); ?>">
            <div class="help-block">
              <?php echo $this->error('data.state'); ?>
              <div class="text-muted">
                <?php echo $this->text('Country state of the store'); ?>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group<?php echo $this->error('data.country', ' has-error'); ?>">
          <label class="col-md-4 control-label"><?php echo $this->text('Country'); ?></label>
          <div class="col-md-8">
            <select name="store[data][country]" class="form-control">
              <?php foreach ($countries as $code => $name) { ?>
              <option value="<?php echo $this->e($code); ?>"<?php echo $store['data']['country'] == $code ? ' selected' : ''; ?>><?php echo $this->e($name); ?></option>
              <?php } ?>
            </select>
            <div class="help-block">
              <?php echo $this->error('data.country'); ?>
              <div class="text-muted">
                <?php echo $this->text('Country of the store'); ?>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group<?php echo $this->error('data.address', ' has-error'); ?>">
          <label class="col-md-4 control-label"><?php echo $this->text('Address'); ?></label>
          <div class="col-md-8">
            <input name="store[data][address]" class="form-control" value="<?php echo $this->e($store['data']['address']); ?>">
            <div class="help-block">
              <?php echo $this->error('data.address'); ?>
              <div class="text-muted">
                <?php echo $this->text('A physical address of the store'); ?>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group<?php echo $this->error('data.map', ' has-error'); ?>">
          <label class="col-md-4 control-label"><?php echo $this->text('Map'); ?></label>
          <div class="col-md-8">
            <textarea name="store[data][map]" class="form-control"><?php echo $this->e($store['data']['map']); ?></textarea>
            <div class="help-block">
              <?php echo $this->error('data.map'); ?>
              <div class="text-muted">
                <?php echo $this->text('Latitude and longitude of the store, one value per line'); ?>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group<?php echo $this->error('data.phone', ' has-error'); ?>">
          <label class="col-md-4 control-label"><?php echo $this->text('Phone'); ?></label>
          <div class="col-md-8">
            <textarea name="store[data][phone]" class="form-control"><?php echo $this->e($store['data']['phone']); ?></textarea>
            <div class="help-block">
              <?php echo $this->error('data.phone'); ?>
              <div class="text-muted">
                <?php echo $this->text('A list of phone numbers, one per line'); ?>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group<?php echo $this->error('data.fax', ' has-error'); ?>">
          <label class="col-md-4 control-label"><?php echo $this->text('Fax'); ?></label>
          <div class="col-md-8">
            <textarea name="store[data][fax]" class="form-control"><?php echo $this->e($store['data']['fax']); ?></textarea>
            <div class="help-block">
              <?php echo $this->error('data.fax'); ?>
              <div class="text-muted">
                <?php echo $this->text('A list of fax numbers, one per line'); ?>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group<?php echo $this->error('data.postcode', ' has-error'); ?>">
          <label class="col-md-4 control-label"><?php echo $this->text('Post code'); ?></label>
          <div class="col-md-8">
            <input name="store[data][postcode]" class="form-control" value="<?php echo $this->e($store['data']['postcode']); ?>">
            <div class="help-block">
              <?php echo $this->error('data.postcode'); ?>
              <div class="text-muted">
                <?php echo $this->text('Post code of the store'); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="embed-responsive embed-responsive-4by3">
          <div id="map-container"></div>
        </div>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo $this->text('Appearance'); ?></legend>
    <div class="form-group<?php echo $this->error('data.theme', ' has-error'); ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('Theme'); ?></label>
      <div class="col-md-4">
        <select name="store[data][theme]" class="form-control">
          <?php foreach ($themes as $theme_id => $theme) { ?>
            <option value="<?php echo $theme_id; ?>"<?php echo $store['data']['theme'] == $theme_id ? ' selected' : ''; ?>><?php echo $this->e($theme['name']); ?></option>
          <?php } ?>
        </select>
        <div class="help-block">
          <?php echo $this->error('data.theme'); ?>
          <div class="text-muted">
            <?php echo $this->text('Select a theme module used to display front-end of the store to all devices'); ?>
          </div>
        </div>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo $this->text('Options'); ?></legend>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Anonymous checkout'); ?></label>
      <div class="col-md-4">
        <div class="btn-group" data-toggle="buttons">
          <label class="btn btn-default<?php echo empty($store['data']['anonymous_checkout']) ? '' : ' active'; ?>">
            <input name="store[data][anonymous_checkout]" type="radio" autocomplete="off" value="1"<?php echo empty($store['data']['anonymous_checkout']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
          </label>
          <label class="btn btn-default<?php echo empty($store['data']['anonymous_checkout']) ? ' active' : ''; ?>">
            <input name="store[data][anonymous_checkout]" type="radio" autocomplete="off" value="0"<?php echo empty($store['data']['anonymous_checkout']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
          </label>
        </div>
        <div class="help-block">
          <?php echo $this->text('If disabled customers must log in before checkout'); ?>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Code'); ?></label>
      <div class="col-md-6">
        <textarea name="store[data][js]" rows="10" class="form-control"><?php echo $this->e($store['data']['js']); ?></textarea>
        <div class="help-block">
           <?php echo $this->text('A .js code without script tags to add on each public, non-admin and non-internal page, e.g Google Analytics tracking code'); ?>
        </div>
      </div>
    </div>
  </fieldset>
  <?php if (isset($store['store_id']) && !empty($collections)) { ?>
  <fieldset>
    <legend><?php echo $this->text('Collections'); ?></legend>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('File'); ?></label>
      <div class="col-md-4">
        <select name="store[data][collection_file]" class="form-control">
          <option value="0"><?php echo $this->text('Disabled'); ?></option>
          <?php if (!empty($collections['file'])) { ?>
          <?php foreach ($collections['file'] as $collection_id => $collection) { ?>
          <?php if (isset($store['data']['collection_file']) && $store['data']['collection_file'] == $collection_id) { ?>
          <option value="<?php echo $collection_id; ?>" selected><?php echo $this->e($collection['title']); ?></option>
          <?php } else { ?>
          <option value="<?php echo $collection_id; ?>"><?php echo $this->e($collection['title']); ?></option>
          <?php } ?>
          <?php } ?>
          <?php } ?>
        </select>
        <div class="help-block">
          <?php echo $this->text('Select a <a href="@url">collection</a> to be used for banner slideshow on the front page', array('@url' => $this->url('admin/content/collection'))); ?>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Featured products'); ?></label>
      <div class="col-md-4">
        <select name="store[data][collection_product]" class="form-control">
          <option value="0"><?php echo $this->text('Disabled'); ?></option>
          <?php if (!empty($collections['product'])) { ?>
          <?php foreach ($collections['product'] as $collection_id => $collection) { ?>
          <?php if (isset($store['data']['collection_product']) && $store['data']['collection_product'] == $collection_id) { ?>
          <option value="<?php echo $collection_id; ?>" selected><?php echo $this->e($collection['title']); ?></option>
          <?php } else { ?>
          <option value="<?php echo $collection_id; ?>"><?php echo $this->e($collection['title']); ?></option>
          <?php } ?>
          <?php } ?>
          <?php } ?>
        </select>
        <div class="help-block">
          <?php echo $this->text('Select a <a href="@url">collection</a> to be used for list of featured products on the front page', array('@url' => $this->url('admin/content/collection'))); ?>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Pages'); ?></label>
      <div class="col-md-4">
        <select name="store[data][collection_page]" class="form-control">
          <option value="0"><?php echo $this->text('Disabled'); ?></option>
          <?php if (!empty($collections['page'])) { ?>
          <?php foreach ($collections['page'] as $collection_id => $collection) { ?>
          <?php if (isset($store['data']['collection_page']) && $store['data']['collection_page'] == $collection_id) { ?>
          <option value="<?php echo $collection_id; ?>" selected><?php echo $this->e($collection['title']); ?></option>
          <?php } else { ?>
          <option value="<?php echo $collection_id; ?>"><?php echo $this->e($collection['title']); ?></option>
          <?php } ?>
          <?php } ?>
          <?php } ?>
        </select>
        <div class="help-block">
          <?php echo $this->text('Select a <a href="@url">collection</a> to be used for list of articles/news on the front page', array('@url' => $this->url('admin/content/collection'))); ?>
        </div>
      </div>
    </div>
  </fieldset>
  <?php } ?>
  <fieldset>
    <legend><?php echo $this->text('Images'); ?></legend>
    <div class="form-group<?php echo $this->error('logo', ' has-error'); ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('Logo'); ?></label>
      <div class="col-md-4">
        <?php if ($this->access('file_upload')) { ?>
          <input type="file" name="logo" accept="image/*" class="form-control">
        <?php } ?>
        <input type="hidden" name="store[data][logo]" value="<?php echo isset($store['data']['logo']) ? $this->e($store['data']['logo']) : ''; ?>">
        <div class="help-block">
          <?php echo $this->error('logo'); ?>
          <div class="text-muted"><?php echo $this->text('The main site logo. Appearance of the image is controlled by the current theme'); ?></div>
        </div>
      </div>
    </div>
    <?php if (!empty($store['logo_thumb'])) { ?>
    <div class="form-group">
      <div class="col-md-1 col-md-offset-2">
        <img class="img-responsive" src="<?php echo $this->e($store['logo_thumb']); ?>">
      </div>
      <div class="col-md-2">
        <div class="checkbox">
          <label><input type="checkbox" name="store[delete_logo]" value="1"> <?php echo $this->text('Delete'); ?></label>
        </div>
      </div>
    </div>
    <?php } ?>
    <div class="form-group<?php echo $this->error('favicon', ' has-error'); ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('Favicon'); ?></label>
      <div class="col-md-4">
        <?php if ($this->access('file_upload')) { ?>
          <input type="file" name="favicon" accept="image/*" class="form-control">
        <?php } ?>
        <input type="hidden" name="store[data][favicon]" value="<?php echo isset($store['data']['favicon']) ? $this->e($store['data']['favicon']) : ''; ?>">
        <div class="help-block">
          <?php echo $this->error('favicon'); ?>
          <div class="text-muted">
            <?php echo $this->text('Favicon is a small image that represents your site. Appearance of the image is controlled by the current theme'); ?>
          </div>
        </div>
      </div>
    </div>
    <?php if (isset($store['favicon_thumb'])) { ?>
    <div class="form-group">
      <div class="col-md-1 col-md-offset-2">
        <img class="img-responsive" src="<?php echo $this->e($store['favicon_thumb']); ?>">
      </div>
      <div class="col-md-2">
        <div class="checkbox">
          <label><input type="checkbox" name="store[delete_favicon]" value="1"> <?php echo $this->text('Delete'); ?></label>
        </div>
      </div>
    </div>
    <?php } ?>
  </fieldset>
  <div class="form-group">
    <div class="col-md-10 col-md-offset-2">
      <div class="btn-toolbar">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Are you sure? It cannot be undone!'));">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/settings/store'); ?>" class="btn btn-default cancel">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('store_add') || $this->access('store_edit')) { ?>
          <button class="btn btn-default save" name="save" value="1"><i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?></button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>