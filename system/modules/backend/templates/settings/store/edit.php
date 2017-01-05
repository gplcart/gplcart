<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" enctype="multipart/form-data" id="edit-store" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Data'); ?></div>
    <div class="panel-body">
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
            <?php echo $this->text('Only enabled stores are available for customers and search engines'); ?>
          </div>
        </div>
      </div>
      <?php if (!isset($store['store_id']) || $store['store_id']) { ?>
      <div class="form-group required<?php echo $this->error('name', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Name'); ?>
        </label>
        <div class="col-md-4">
          <input maxlength="255" name="store[name]" class="form-control" value="<?php echo isset($store['name']) ? $this->escape($store['name']) : ''; ?>">
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
          <input maxlength="255" name="store[domain]" class="form-control" value="<?php echo isset($store['domain']) ? $this->escape($store['domain']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('domain'); ?>
            <div class="text-muted">
              <?php echo $this->text('Required. A domain name by which this store can be accessed, e.g domain.com or subdomain.domain.com'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('basepath', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Base path'); ?></label>
        <div class="col-md-4">
          <input maxlength="50" name="store[basepath]" class="form-control" value="<?php echo isset($store['basepath']) ? $this->escape($store['basepath']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('basepath'); ?>
            <div class="text-muted">
              <?php echo $this->text('An optional path if the store is not installed in the domain root. Used to constract absolute store URL'); ?>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>
      <div class="form-group<?php echo $this->error('data.owner', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Owner'); ?></label>
        <div class="col-md-4">
          <input maxlength="32" name="store[data][owner]" class="form-control" value="<?php echo isset($store['data']['owner']) ? $this->escape($store['data']['owner']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('data.owner'); ?>
            <div class="text-muted">
              <?php echo $this->text('A name of the company that owns this store'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Description'); ?></div>
    <div class="panel-body">
      <div class="required form-group<?php echo $this->error('data.title', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Title'); ?></label>
        <div class="col-md-4">
          <input type="text" maxlength="70" name="store[data][title]" class="form-control" value="<?php echo (isset($store['data']['title'])) ? $this->escape($store['data']['title']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('data.title'); ?>
            <div class="text-muted"><?php echo $this->text('Required. This name is displayed to frontend users as default site name'); ?></div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('data.meta_title', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Meta title'); ?></label>
        <div class="col-md-4">
          <input type="text" maxlength="60" name="store[data][meta_title]" class="form-control" value="<?php echo (isset($store['data']['meta_title'])) ? $this->escape($store['data']['meta_title']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('data.meta_title'); ?>
            <div class="text-muted">
              <?php echo $this->text('An optional text to be placed between %tags tags. Important for SEO', array('%tags' => '<title>')); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('data.meta_description', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Meta description'); ?></label>
        <div class="col-md-4">
          <textarea maxlength="160" class="form-control" name="store[data][meta_description]"><?php echo (isset($store['data']['meta_description'])) ? $this->escape($store['data']['meta_description']) : ''; ?></textarea>
          <div class="help-block">
            <?php echo $this->error('data.meta_description'); ?>
            <?php echo $this->text('An optional text to be used in meta description tag. The tag is commonly used on search engine result pages (SERPs) to display preview snippets for a given page. Important for SEO'); ?>
          </div>
        </div>
      </div>
      <?php if (!empty($languages)) { ?>
      <div class="form-group">
        <div class="col-md-6 col-md-offset-2">
          <a data-toggle="collapse" href="#translations">
            <?php echo $this->text('Translations'); ?> <span class="caret"></span>
          </a>
        </div>
      </div>
      <div id="translations" class="collapse translations<?php echo $this->error(null, ' in'); ?>">
        <?php foreach ($languages as $code => $info) { ?>
        <div class="form-group<?php echo $this->error("data.translation.$code.title", ' has-error'); ?>">
          <label class="col-md-2 control-label">
            <?php echo $this->text('Title %language', array('%language' => $info['native_name'])); ?>
          </label>
          <div class="col-md-4">
            <input type="text" maxlength="70" name="store[data][translation][<?php echo $code; ?>][title]" class="form-control" id="title-<?php echo $code; ?>" value="<?php echo (isset($store['data']['translation'][$code]['title'])) ? $this->escape($store['data']['translation'][$code]['title']) : ''; ?>">
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
            <input type="text" maxlength="60" name="store[data][translation][<?php echo $code; ?>][meta_title]" class="form-control" value="<?php echo (isset($store['data']['translation'][$code]['meta_title'])) ? $this->escape($store['data']['translation'][$code]['meta_title']) : ''; ?>">
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
            <textarea maxlength="160" id="description-<?php echo $code; ?>" class="form-control" name="store[data][translation][<?php echo $code; ?>][meta_description]"><?php echo (isset($store['data']['translation'][$code]['meta_description'])) ? $this->escape($store['data']['translation'][$code]['meta_description']) : ''; ?></textarea>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Contact'); ?></div>
    <div class="panel-body">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group<?php echo $this->error('data.address', ' has-error'); ?>">
            <label class="col-md-4 control-label"><?php echo $this->text('Address'); ?></label>
            <div class="col-md-8">
              <textarea name="store[data][address]" class="form-control"><?php echo isset($store['data']['address']) ? $this->escape($store['data']['address']) : ''; ?></textarea>
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
              <textarea name="store[data][map]" class="form-control"><?php echo empty($store['data']['map']) ? '' : $this->escape($store['data']['map']); ?></textarea>
              <div class="help-block">
                <?php echo $this->error('data.map'); ?>
                <div class="text-muted">
                  <?php echo $this->text('Latitude and longitude of the store, one value per line'); ?>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group<?php echo $this->error('data.email', ' has-error'); ?>">
            <label class="col-md-4 control-label"><?php echo $this->text('E-mail'); ?></label>
            <div class="col-md-8">
              <textarea name="store[data][email]" class="form-control"><?php echo!empty($store['data']['email']) ? $this->escape($store['data']['email']) : ''; ?></textarea>
              <div class="help-block">
                <?php echo $this->error('data.map'); ?>
                <div class="text-muted">
                  <?php echo $this->text('A list of e-mails, one per line. The very first address will be main and used for notifications'); ?>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group<?php echo $this->error('data.phone', ' has-error'); ?>">
            <label class="col-md-4 control-label"><?php echo $this->text('Phone'); ?></label>
            <div class="col-md-8">
              <textarea name="store[data][phone]" class="form-control"><?php echo!empty($store['data']['phone']) ? $this->escape($store['data']['phone']) : ''; ?></textarea>
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
              <textarea name="store[data][fax]" class="form-control"><?php echo !empty($store['data']['fax']) ? $this->escape($store['data']['fax']) : ''; ?></textarea>
              <div class="help-block">
                <?php echo $this->error('data.fax'); ?>
                <div class="text-muted">
                  <?php echo $this->text('A list of fax numbers, one per line'); ?>
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
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Design'); ?></div>
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('data.theme', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Theme'); ?></label>
        <div class="col-md-4">
          <select name="store[data][theme]" class="form-control">
            <?php foreach ($themes as $theme_id => $theme) { ?>
            <option value="<?php echo $theme_id; ?>"<?php echo (isset($store['data']['theme']) && ($store['data']['theme'] == $theme_id)) ? ' selected' : ''; ?>><?php echo $this->escape($theme['name']); ?></option>
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
      <div class="form-group<?php echo $this->error('data.theme_mobile', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Mobile theme'); ?></label>
        <div class="col-md-4">
          <select name="store[data][theme_mobile]" class="form-control">
            <?php foreach ($themes as $theme_id => $theme) { ?>
            <option value="<?php echo $theme_id; ?>"<?php echo (isset($store['data']['theme_mobile']) && ($store['data']['theme_mobile'] == $theme_id)) ? ' selected' : ''; ?>><?php echo $this->escape($theme['name']); ?></option>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->error('data.theme_mobile'); ?>
            <div class="text-muted">
              <?php echo $this->text('Select a theme module used to display front-end of the store to all mobile devices'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('data.theme_tablet', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Tablet theme'); ?></label>
        <div class="col-md-4">
          <select name="store[data][theme_tablet]" class="form-control">
            <?php foreach ($themes as $theme_id => $theme) { ?>
            <option value="<?php echo $theme_id; ?>"<?php echo (isset($store['data']['theme_tablet']) && ($store['data']['theme_tablet'] == $theme_id)) ? ' selected' : ''; ?>><?php echo $this->escape($theme['name']); ?></option>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->error('data.theme_tablet'); ?>
            <div class="text-muted">
              <?php echo $this->text('Select a theme module used to display front-end of the store to tablet devices'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Options'); ?></div>
    <div class="panel-body">
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
        <label class="col-md-2 control-label"><?php echo $this->text('Invoice prefix'); ?></label>
        <div class="col-md-4">
          <input name="store[data][invoice_prefix]" class="form-control" value="<?php echo isset($store['data']['invoice_prefix']) ? $store['data']['invoice_prefix'] : ''; ?>">
          <div class="help-block">
            <?php echo $this->text('Prepend this string to all invoice numbers associated with this store'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php if(isset($store['store_id'])) { ?>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Collections'); ?></div>
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Banners'); ?></label>
        <div class="col-md-4">
          <select name="store[data][collection_banner]" class="form-control">
          <option value="0"><?php echo $this->text('Disabled'); ?></option>
          <?php if(!empty($collections['file'])) { ?>
          <?php foreach($collections['file'] as $collection_id => $collection) { ?>
          <?php if(isset($store['data']['collection_banner']) && $store['data']['collection_banner'] == $collection_id) { ?>
          <option value="<?php echo $collection_id; ?>" selected><?php echo $this->escape($collection['title']); ?></option>
          <?php } else { ?>
          <option value="<?php echo $collection_id; ?>"><?php echo $this->escape($collection['title']); ?></option>
          <?php } ?>
          <?php } ?>
          <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select a <a href="@href">collection</a> to be used for banner slideshow on the front page', array('@href' => $this->url('admin/content/collection'))); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Featured products'); ?></label>
        <div class="col-md-4">
          <select name="store[data][collection_featured]" class="form-control">
          <option value="0"><?php echo $this->text('Disabled'); ?></option>
          <?php if(!empty($collections['product'])) { ?>
          <?php foreach($collections['product'] as $collection_id => $collection) { ?>
          <?php if(isset($store['data']['collection_featured']) && $store['data']['collection_featured'] == $collection_id) { ?>
          <option value="<?php echo $collection_id; ?>" selected><?php echo $this->escape($collection['title']); ?></option>
          <?php } else { ?>
          <option value="<?php echo $collection_id; ?>"><?php echo $this->escape($collection['title']); ?></option>
          <?php } ?>
          <?php } ?>
          <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select a <a href="@href">collection</a> to be used for list of featured products on the front page', array('@href' => $this->url('admin/content/collection'))); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Pages'); ?></label>
        <div class="col-md-4">
          <select name="store[data][collection_page]" class="form-control">
          <option value="0"><?php echo $this->text('Disabled'); ?></option>
          <?php if(!empty($collections['page'])) { ?>
          <?php foreach($collections['page'] as $collection_id => $collection) { ?>
          <?php if(isset($store['data']['collection_page']) && $store['data']['collection_page'] == $collection_id) { ?>
          <option value="<?php echo $collection_id; ?>" selected><?php echo $this->escape($collection['title']); ?></option>
          <?php } else { ?>
          <option value="<?php echo $collection_id; ?>"><?php echo $this->escape($collection['title']); ?></option>
          <?php } ?>
          <?php } ?>
          <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select a <a href="@href">collection</a> to be used for list of articles/news on the front page', array('@href' => $this->url('admin/content/collection'))); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Images'); ?></div>
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('logo', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Logo'); ?></label>
        <div class="col-md-4">
          <?php if ($this->access('file_upload')) { ?>
          <input type="file" name="logo" accept="image/*" class="form-control">
          <?php } ?>
          <input type="hidden" name="store[data][logo]" value="<?php echo isset($store['data']['logo']) ? $this->escape($store['data']['logo']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('logo'); ?>
            <div class="text-muted"><?php echo $this->text('Upload an image. Appearance of the image is controlled by the current theme'); ?></div>
          </div>
        </div>
      </div>
      <?php if (!empty($store['logo_thumb'])) { ?>
      <div class="form-group">
        <div class="col-md-1 col-md-offset-2">
          <img class="img-responsive" src="<?php echo $this->escape($store['logo_thumb']); ?>">
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
          <input type="hidden" name="store[data][favicon]" value="<?php echo isset($store['data']['favicon']) ? $this->escape($store['data']['favicon']) : ''; ?>">
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
          <img class="img-responsive" src="<?php echo $this->escape($store['favicon_thumb']); ?>">
        </div>
        <div class="col-md-2">
          <div class="checkbox">
            <label><input type="checkbox" name="store[delete_favicon]" value="1"> <?php echo $this->text('Delete'); ?></label>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <?php if ($can_delete) { ?>
          <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Delete? It cannot be undone!'));">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-10">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url('admin/settings/store'); ?>" class="btn btn-default cancel">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('store_add') || $this->access('store_edit')) { ?>
            <button class="btn btn-default save" name="save" value="1"><i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?></button>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>