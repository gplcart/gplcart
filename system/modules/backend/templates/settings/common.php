<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" enctype="multipart/form-data" id="common-settings" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Performance'); ?></div>
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Compress JS'); ?></label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($settings['compress_js']) ? '' : ' active'; ?>">
              <input name="settings[compress_js]" type="radio" autocomplete="off" value="1"<?php echo empty($settings['compress_js']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($settings['compress_js']) ? ' active' : ''; ?>">
              <input name="settings[compress_js]" type="radio" autocomplete="off" value="0"<?php echo empty($settings['compress_js']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
            <?php echo $this->text('If enabled, JS files will be merged into several big files. It reduces number of HTTP queries and improves site loading speed'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Compress CSS'); ?></label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($settings['compress_css']) ? '' : ' active'; ?>">
              <input name="settings[compress_css]" type="radio" autocomplete="off" value="1"<?php echo empty($settings['compress_css']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($settings['compress_css']) ? ' active' : ''; ?>">
              <input name="settings[compress_css]" type="radio" autocomplete="off" value="0"<?php echo empty($settings['compress_css']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
            <?php echo $this->text('If enabled, CSS files will be minified and merged into one big file. It reduces number of HTTP queries and improves site loading speed'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2">
          <button class="btn btn-default" name="delete_cached_assets" value="1">
            <?php echo $this->text('Delete cached assets'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('E-mail'); ?></div>
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Mailer'); ?></label>
        <div class="col-md-4">
          <select  name="settings[mailer]" class="form-control">
            <?php foreach($mailers as $id => $mailer) { ?>
            <option value="<?php echo $id; ?>"<?php echo ($settings['mailer'] == $id) ? ' selected' : ''; ?>><?php echo $this->escape($mailer['name']); ?></option>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select a mailer to send store E-mails. Default is PHP mail() function. Mailers are provided by modules, check out their settings'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Miscellaneous'); ?></div>
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Timezone'); ?></label>
        <div class="col-md-4">
          <select  name="settings[timezone]" class="form-control">
            <?php foreach($timezones as $timezone_id => $timezone_name) { ?>
            <option value="<?php echo $timezone_id; ?>"<?php echo ($settings['timezone'] == $timezone_id) ? ' selected' : ''; ?>>
            <?php echo $this->escape($timezone_name); ?>
            </option>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select your sitewide time zone. This setting can affect price rules and other important things'); ?>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('gapi_browser_key', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Google API browser key'); ?></label>
        <div class="col-md-4">
          <input name="settings[gapi_browser_key]" class="form-control" value="<?php echo $this->escape($settings['gapi_browser_key']); ?>">
          <div class="help-block">
            <?php echo $this->error('gapi_browser_key'); ?>
            <div class="text-muted">
              <?php echo $this->text('A browser key from Google Developers Console. Used for standard API like Google Maps etc'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Cron key'); ?></label>
        <div class="col-md-4">
          <input name="settings[cron_key]" maxlength="255" class="form-control" value="<?php echo $settings['cron_key']; ?>">
          <div class="help-block">
            <div class="text-muted">
              <?php echo $this->text('The key is used to run scheduled operations from outside of the site. Leave empty to generate a random key'); ?>
            </div>
            <?php if (!empty($settings['cron_key']) && $this->access('cron')) { ?>
            <a target="_blank" href="<?php echo $this->url('cron', array('key' => $settings['cron_key'])); ?>">
              <?php echo $this->text('Run cron'); ?>
            </a>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Display errors'); ?></label>
        <div class="col-md-4">
          <select  name="settings[error_level]" class="form-control">
            <option value="0"<?php echo ($settings['error_level'] == 0) ? ' selected' : ''; ?>>
            <?php echo $this->text('None'); ?>
            </option>
            <option value="1"<?php echo ($settings['error_level'] == 1) ? ' selected' : ''; ?>>
            <?php echo $this->text('Errors, notices, warnings'); ?>
            </option>
            <option value="2"<?php echo ($settings['error_level'] == 2) ? ' selected' : ''; ?>>
            <?php echo $this->text('All'); ?>
            </option>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select which PHP errors are reported. You must disable reporting on production for security reason'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Live error reporting'); ?></label>
        <div class="col-md-4">
          <select  name="settings[error_live_report]" class="form-control">
            <option value="0"<?php echo ($settings['error_live_report'] == 0) ? ' selected' : ''; ?>>
            <?php echo $this->text('Disabled'); ?>
            </option>
            <option value="1"<?php echo ($settings['error_live_report'] == 1) ? ' selected' : ''; ?>>
            <?php echo $this->text('Enabled for admin'); ?>
            </option>
            <option value="2"<?php echo ($settings['error_live_report'] == 2) ? ' selected' : ''; ?>>
            <?php echo $this->text('Enabled for everyone'); ?>
            </option>
          </select>
          <div class="help-block">
            <?php echo $this->text('Inform about current PHP errors on every page. Should be disabled on production'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-6 col-md-offset-2">
          <button class="btn btn-default" name="save" value="1">
            <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>
