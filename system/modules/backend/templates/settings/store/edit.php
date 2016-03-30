<form method="post" enctype="multipart/form-data" id="edit-store" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-12 text-right">
      <div class="btn-toolbar">
        <?php if (!$is_default && $this->access('store_delete') && $can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/settings/store'); ?>" class="btn btn-default cancel"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('store_add') || $this->access('store_edit')) { ?>
        <button class="btn btn-primary save" name="save" value="1"><i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?></button>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <ul class="nav nav-tabs">
        <li class="active"><a href="#data" data-toggle="tab"><?php echo $this->text('Data'); ?></a></li>
        <li><a href="#description" data-toggle="tab"><?php echo $this->text('Description'); ?></a></li>
        <li><a href="#contact" data-toggle="tab"><?php echo $this->text('Contact'); ?></a></li>
        <li><a href="#design" data-toggle="tab"><?php echo $this->text('Design'); ?></a></li>
        <li><a href="#option" data-toggle="tab"><?php echo $this->text('Options'); ?></a></li>
        <li><a href="#image" data-toggle="tab"><?php echo $this->text('Images'); ?></a></li>
        <li><a href="#ga" data-toggle="tab"><?php echo $this->text('Google Analytics'); ?></a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane active" id="data">
          <div class="form-group">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Only enabled stores are available for customers and search engines'); ?>">
              <?php echo $this->text('Status'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default<?php echo empty($store['status']) ? '' : ' active'; ?>">
                  <input name="store[status]" type="radio" autocomplete="off" value="1"<?php echo empty($store['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
                </label>
                <label class="btn btn-default<?php echo empty($store['status']) ? ' active' : ''; ?>">
                  <input name="store[status]" type="radio" autocomplete="off" value="0"<?php echo empty($store['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
                </label>
              </div>
            </div>
          </div>
          <?php if (!isset($store['store_id']) || $store['store_id']) { ?>
          <div class="form-group required<?php echo isset($form_errors['name']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Store name for administrators'); ?>">
              <?php echo $this->text('Name'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <input maxlength="255" name="store[name]" class="form-control" value="<?php echo isset($store['name']) ? $this->escape($store['name']) : ''; ?>" required>
              <?php if (isset($form_errors['name'])) { ?>
              <div class="help-block"><?php echo $form_errors['name']; ?></div>
              <?php } ?>
            </div>
          </div>
          <?php } ?>
          <?php if (!$is_default) { ?>
          <div class="form-group">
            <label class="col-md-2 control-label"><?php echo $this->text('Scheme'); ?></label>
            <div class="col-md-2">
              <select name="store[scheme]" class="form-control">
                <option value="http://"<?php echo (isset($store['scheme']) && ($store['scheme'] === 'http://')) ? ' selected' : ''; ?>>
                    <?php echo $this->text('http://'); ?>
                </option>
                <option value="https://"<?php echo (isset($store['scheme']) && ($store['scheme'] === 'https://')) ? ' selected' : ''; ?>>
                    <?php echo $this->text('https://'); ?>
                </option>
              </select>
            </div>
          </div>
          <div class="form-group required<?php echo isset($form_errors['domain']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Do not add "http://". Example: domain.com'); ?>">
              <?php echo $this->text('Domain'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <input maxlength="255" name="store[domain]" class="form-control" value="<?php echo isset($store['domain']) ? $this->escape($store['domain']) : ''; ?>" required>
              <?php if (isset($form_errors['domain'])) { ?>
              <div class="help-block"><?php echo $form_errors['domain']; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php echo isset($form_errors['basepath']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('An optional forder name if the store not installed in the domain root directory'); ?>">
              <?php echo $this->text('Base path'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <input maxlength="50" name="store[basepath]" class="form-control" value="<?php echo isset($store['basepath']) ? $this->escape($store['basepath']) : ''; ?>">
              <?php if (isset($form_errors['basepath'])) { ?>
              <div class="help-block"><?php echo $form_errors['basepath']; ?></div>
              <?php } ?>
            </div>
          </div>
          <?php } ?>
          <div class="form-group">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Name of company that owns this store'); ?>">
              <?php echo $this->text('Owner'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <input maxlength="32" name="store[data][owner]" class="form-control" value="<?php echo isset($store['data']['owner']) ? $this->escape($store['data']['owner']) : ''; ?>">
            </div>
          </div>
        </div>
        <div class="tab-pane" id="description">
          <div class="required form-group<?php echo isset($form_errors['data']['title']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Default site name for customers'); ?>">
              <?php echo $this->text('Title'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <input type="text" maxlength="70" name="store[data][title]" class="form-control" value="<?php echo (isset($store['data']['title'])) ? $this->escape($store['data']['title']) : ''; ?>">
              <?php if (isset($form_errors['data']['title'])) { ?>
              <div class="help-block"><?php echo $form_errors['data']['title']; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php echo isset($form_errors['data']['meta_title']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Default meta title tag, no more than 60 characters'); ?>">
              <?php echo $this->text('Meta title'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <input type="text" maxlength="60" name="store[data][meta_title]" class="form-control" value="<?php echo (isset($store['data']['meta_title'])) ? $this->escape($store['data']['meta_title']) : ''; ?>">
              <?php if (isset($form_errors['data']['meta_title'])) { ?>
              <div class="help-block"><?php echo $form_errors['data']['meta_title']; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Default meta description tag, no more than 160 characters'); ?>">
              <?php echo $this->text('Meta description'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <textarea maxlength="160" class="form-control" name="store[data][meta_description]"><?php echo (isset($store['data']['meta_description'])) ? $this->escape($store['data']['meta_description']) : ''; ?></textarea>
            </div>
          </div>
          <?php if ($languages) { ?>
          <div class="form-group">
            <div class="col-md-6 col-md-offset-2">
              <a data-toggle="collapse" href="#translations">
                <?php echo $this->text('Translations'); ?> <span class="caret"></span>
              </a>
            </div>
          </div>
          <div id="translations" class="collapse translations<?php echo isset($form_errors) ? ' in' : ''; ?>">
            <?php foreach ($languages as $code => $info) { ?>
            <div class="form-group<?php echo isset($form_errors['data']['translation'][$code]['title']) ? ' has-error' : ''; ?>">
              <label class="col-md-2 control-label">
              <?php echo $this->text('Title %language', array('%language' => $info['native_name'])); ?>
              </label>
              <div class="col-md-4">
                <input type="text" maxlength="70" name="store[data][translation][<?php echo $code; ?>][title]" class="form-control" id="title-<?php echo $code; ?>" value="<?php echo (isset($store['data']['translation'][$code]['title'])) ? $this->escape($store['data']['translation'][$code]['title']) : ''; ?>">
                <?php if (isset($form_errors['data']['translation'][$code]['title'])) { ?>
                <div class="help-block"><?php echo $form_errors['data']['translation'][$code]['title']; ?></div>
                <?php } ?>
              </div>
            </div>
            <div class="form-group<?php echo isset($form_errors['data']['translation'][$code]['meta_title']) ? ' has-error' : ''; ?>">
              <label class="col-md-2 control-label">
              <?php echo $this->text('Meta title %language', array('%language' => $info['native_name'])); ?>
              </label>
              <div class="col-md-4">
                <input type="text" maxlength="60" name="store[data][translation][<?php echo $code; ?>][meta_title]" class="form-control" value="<?php echo (isset($store['data']['translation'][$code]['meta_title'])) ? $this->escape($store['data']['translation'][$code]['meta_title']) : ''; ?>">
                <?php if (isset($form_errors['data']['translation'][$code]['meta_title'])) { ?>
                <div class="help-block"><?php echo $form_errors['data']['translation'][$code]['meta_title']; ?></div>
                <?php } ?>
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
        <div class="tab-pane" id="contact">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label class="col-md-3 control-label">
                  <span class="hint" title="<?php echo $this->text('Physical address of the store'); ?>">
                  <?php echo $this->text('Address'); ?>
                  </span>
                </label>
                <div class="col-md-6">
                  <textarea name="store[data][address]" class="form-control"><?php echo isset($store['data']['address']) ? $this->escape($store['data']['address']) : ''; ?></textarea>
                </div>
              </div>
              <div class="form-group<?php echo isset($form_errors['data']['map']) ? ' has-error' : ''; ?>">
                <label class="col-md-3 control-label">
                  <span class="hint" title="<?php echo $this->text('Latitude and longitude of the store, one value per line'); ?>">
                  <?php echo $this->text('Map'); ?>
                  </span>
                </label>
                <div class="col-md-6">
                  <textarea name="store[data][map]" class="form-control"><?php echo empty($store['data']['map']) ? '' : $this->escape($store['data']['map']); ?></textarea>
                  <?php if (isset($form_errors['data']['map'])) { ?>
                  <div class="help-block"><?php echo $form_errors['data']['map']; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group<?php echo isset($form_errors['email']) ? ' has-error' : ''; ?>">
                <label class="col-md-3 control-label">
                  <span class="hint" title="<?php echo $this->text('List of store e-mails, one per line. The very first address will be main and used for notifications'); ?>">
                  <?php echo $this->text('E-mail'); ?>
                  </span>
                </label>
                <div class="col-md-6">
                  <textarea name="store[data][email]" class="form-control"><?php echo!empty($store['data']['email']) ? $this->escape($store['data']['email']) : ''; ?></textarea>
                  <?php if (isset($form_errors['email'])) { ?>
                  <div class="help-block"><?php echo $form_errors['email']; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-md-3 control-label">
                  <span class="hint" title="<?php echo $this->text('List of store phone numbers, one per line'); ?>">
                  <?php echo $this->text('Phone'); ?>
                  </span>
                </label>
                <div class="col-md-6">
                  <textarea name="store[data][phone]" class="form-control"><?php echo!empty($store['data']['phone']) ? $this->escape($store['data']['phone']) : ''; ?></textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="col-md-3 control-label">
                  <span class="hint" title="<?php echo $this->text('List of store fax numbers, one per line'); ?>">
                  <?php echo $this->text('Fax'); ?>
                  </span>
                </label>
                <div class="col-md-6">
                  <textarea name="store[data][fax]" class="form-control"><?php echo!empty($store['data']['fax']) ? $this->escape($store['data']['fax']) : ''; ?></textarea>
                </div>
              </div>
              <div class="form-group<?php echo isset($form_errors['data']['hours']) ? ' has-error' : ''; ?>">
                <label class="col-md-3 control-label">
                  <span class="hint" title="<?php echo $this->text('Opening hours for the store, one day per line, starting from Monday. Separate values by dash, eg "09:00 AM - 05:00 PM". Enter single dash for day-off'); ?>">
                  <?php echo $this->text('Opening hours'); ?>
                  </span>
                </label>
                <div class="col-md-6">
                  <textarea name="store[data][hours]" class="form-control"><?php echo!empty($store['data']['hours']) ? $this->escape($store['data']['hours']) : ''; ?></textarea>
                  <?php if (isset($form_errors['data']['hours'])) { ?>
                  <div class="help-block"><?php echo $form_errors['data']['hours']; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group<?php echo isset($form_errors['data']['social']) ? ' has-error' : ''; ?>">
                <label class="col-md-3 control-label">
                  <span class="hint" title="<?php echo $this->text('List of social network pages, one per line'); ?>">
                  <?php echo $this->text('Social networks'); ?>
                  </span>
                </label>
                <div class="col-md-6">
                  <textarea name="store[data][social]" class="form-control" placeholder="http://facebook.com/yourstore"><?php echo!empty($store['data']['social']) ? $this->escape($store['data']['social']) : ''; ?></textarea>
                  <?php if (isset($form_errors['data']['social'])) { ?>
                  <div class="help-block"><?php echo $form_errors['data']['social']; ?></div>
                  <?php } ?>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="embed-responsive embed-responsive-4by3">
                <div id="map-container" class="embed-responsive-item"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane" id="design">
          <div class="form-group">
            <label class="col-md-2 control-label"><span class="hint" title="<?php echo $this->text('Theme module that presents front-end of the store to all devices'); ?>"><?php echo $this->text('Theme'); ?></span></label>
            <div class="col-md-4">
              <select name="store[data][theme]" class="form-control">
                <?php foreach ($themes as $theme_id => $theme) { ?>
                <option value="<?php echo $theme_id; ?>"<?php echo (isset($store['data']['theme']) && ($store['data']['theme'] == $theme_id)) ? ' selected' : ''; ?>><?php echo $this->escape($theme['name']); ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-2 control-label"><span class="hint" title="<?php echo $this->text('Theme module that presents front-end of the store to all mobile devices'); ?>"><?php echo $this->text('Mobile theme'); ?></span></label>
            <div class="col-md-4">
              <select name="store[data][theme_mobile]" class="form-control">
                <?php foreach ($themes as $theme_id => $theme) { ?>
                <option value="<?php echo $theme_id; ?>"<?php echo (isset($store['data']['theme_mobile']) && ($store['data']['theme_mobile'] == $theme_id)) ? ' selected' : ''; ?>><?php echo $this->escape($theme['name']); ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-2 control-label"><span class="hint" title="<?php echo $this->text('Theme module that presents front-end of the store to tablet devices'); ?>"><?php echo $this->text('Tablet theme'); ?></span></label>
            <div class="col-md-4">
              <select name="store[data][theme_tablet]" class="form-control">
                <?php foreach ($themes as $theme_id => $theme) { ?>
                <option value="<?php echo $theme_id; ?>"<?php echo (isset($store['data']['theme_tablet']) && ($store['data']['theme_tablet'] == $theme_id)) ? ' selected' : ''; ?>><?php echo $this->escape($theme['name']); ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
        </div>
        <div class="tab-pane" id="option">
          <div class="form-group">
            <label class="col-md-2 control-label"><span class="hint" title="<?php echo $this->text('If not selected, customers must log in before checkout'); ?>"><?php echo $this->text('Anonymous checkout'); ?></span></label>
            <div class="col-md-4">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default<?php echo empty($store['data']['anonymous_checkout']) ? '' : ' active'; ?>">
                  <input name="store[data][anonymous_checkout]" type="radio" autocomplete="off" value="1"<?php echo empty($store['data']['anonymous_checkout']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
                </label>
                <label class="btn btn-default<?php echo empty($store['data']['anonymous_checkout']) ? ' active' : ''; ?>">
                  <input name="store[data][anonymous_checkout]" type="radio" autocomplete="off" value="0"<?php echo empty($store['data']['anonymous_checkout']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
                </label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-2 control-label"><span class="hint" title="<?php echo $this->text('Prepend this string to all invoice numbers associated with this store'); ?>"><?php echo $this->text('Invoice prefix'); ?></span></label>
            <div class="col-md-4">
              <input name="store[data][invoice_prefix]" class="form-control" value="<?php echo isset($store['data']['invoice_prefix']) ? $store['data']['invoice_prefix'] : ''; ?>">
            </div>
          </div>
        </div>
        <div class="tab-pane" id="image">
          <div class="form-group<?php echo isset($form_errors['logo']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label"><?php echo $this->text('Logo'); ?></label>
            <div class="col-md-4">
              <?php if($this->access('file_upload')) { ?>
              <input type="file" name="logo" accept="image/*" class="form-control">
              <?php } ?>
              <input type="hidden" name="store[data][logo]" value="<?php echo isset($store['data']['logo']) ? $this->escape($store['data']['logo']) : ''; ?>">
              <?php if (isset($form_errors['logo'])) { ?>
              <div class="help-block"><?php echo $form_errors['logo']; ?></div>
              <?php } ?>
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
          <div class="form-group<?php echo isset($form_errors['favicon']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label"><span class="hint" title="<?php echo $this->text('Favicon is a small icon that browser displays in tabs and bookmarks'); ?>"><?php echo $this->text('Favicon'); ?></span></label>
            <div class="col-md-4">
              <?php if($this->access('file_upload')) { ?>
              <input type="file" name="favicon" accept="image/*" class="form-control">
              <?php } ?>
              <input type="hidden" name="store[data][favicon]" value="<?php echo isset($store['data']['favicon']) ? $this->escape($store['data']['favicon']) : ''; ?>">
              <?php if (isset($form_errors['favicon'])) { ?>
              <div class="help-block"><?php echo $form_errors['favicon']; ?></div>
              <?php } ?>
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
        <div class="tab-pane" id="ga">
          <div class="form-group">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('An ID of your Google Analytics account. Numeric value'); ?>">
              <?php echo $this->text('Account ID'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <input name="store[data][ga_account]" class="form-control" value="<?php echo isset($store['data']['ga_account']) ? $this->escape($store['data']['ga_account']) : ''; ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('To get the property ID you must register this site in your Google Analytics account. It looks something like UA-10876-2'); ?>">
              <?php echo $this->text('Property ID'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <input name="store[data][ga_property]" class="form-control" value="<?php echo isset($store['data']['ga_property']) ? $this->escape($store['data']['ga_property']) : ''; ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('The view determines which data from your property appears in the reports. Numeric value'); ?>">
              <?php echo $this->text('View ID'); ?>
              </span>
            </label>
            <div class="col-md-4">
              <input name="store[data][ga_view]" class="form-control" value="<?php echo isset($store['data']['ga_view']) ? $this->escape($store['data']['ga_view']) : ''; ?>">
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-6 col-md-offset-2">
              <div class="help-block"><a target="_blank" href="https://support.google.com/analytics/answer/1102152"><?php echo $this->text('More detailed information'); ?> <i class="fa fa-external-link"></i></a></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>