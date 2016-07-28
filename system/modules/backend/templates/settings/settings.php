<form method="post" enctype="multipart/form-data" id="common-settings" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6 col-md-offset-6 text-right">
      <button class="btn btn-primary" name="save" value="1">
        <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
      </button>
    </div>
  </div>
  <fieldset class="gapi">
    <legend class="gapi"><?php echo $this->text('Google API'); ?></legend>
    <div class="form-group<?php echo $this->error('gapi_email', ' has-error'); ?>">
      <label class="col-md-2 control-label">
        <span class="hint" title="<?php echo $this->text('Service account (e-mail) from Google Developers Console. This is used for Google Analytics'); ?>">
          <?php echo $this->text('Google API service e-mail'); ?>
        </span>
      </label>
      <div class="col-md-4">
        <input name="settings[gapi_email]" class="form-control" value="<?php echo $this->escape($settings['gapi_email']); ?>">
        <?php if ($this->error('gapi_email', true)) { ?>
        <div class="help-block"><?php echo $this->error('gapi_email'); ?></div>
        <?php } ?>
      </div>
    </div>
    <div class="form-group<?php echo $this->error('gapi_certificate', ' has-error'); ?>">
      <label class="col-md-2 control-label">
        <span class="hint" title="<?php echo $this->text('Upload your .p12 certificate file you got from Google Developers Console. This is used for Google Analytics'); ?>">
          <?php echo $this->text('Google API certificate'); ?>
        </span>
      </label>
      <div class="col-md-4">
        <input type="file" accept=".p12" name="gapi_certificate" class="form-control">
        <input type="hidden" name="settings[gapi_certificate]" value="<?php echo $this->escape($settings['gapi_certificate']); ?>">
        <div class="help-block">
        <?php if ($gapi_certificate) { ?>
        <?php echo $gapi_certificate; ?>
        <?php } ?>
        <?php if ($this->error('gapi_certificate', true)) { ?>
        <?php echo $this->error('gapi_certificate'); ?>
        <?php } ?>
        </div>
      </div>
    </div>
  </fieldset>
  <fieldset class="email">
    <legend class="email"><?php echo $this->text('E-mail'); ?></legend>
    <div class="form-group">
      <label class="col-md-2 control-label">
        <span class="hint" title="<?php echo $this->text('Select a sending method. mail() is very limited but does not require any configuration'); ?>">
          <?php echo $this->text('Mailer'); ?>
        </span>
      </label>
      <div class="col-md-2">
        <select  name="settings[email_method]" class="form-control">
          <option value="mail"<?php echo ($settings['email_method'] == 'mail') ? ' selected' : ''; ?>>
          <?php echo $this->text('mail()'); ?>
          </option>
          <option value="smtp"<?php echo ($settings['email_method'] == 'smtp') ? ' selected' : ''; ?>>
          <?php echo $this->text('SMTP'); ?>
          </option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label">
        <span class="hint" title="<?php echo $this->text('Log in using an authentication mechanism supported by the SMTP server'); ?>">
          <?php echo $this->text('SMTP authentication'); ?>
        </span>
      </label>
      <div class="col-md-4">
        <div class="btn-group" data-toggle="buttons">
          <label class="btn btn-default<?php echo empty($settings['smtp_auth']) ? '' : ' active'; ?>">
            <input name="settings[smtp_auth]" type="radio" autocomplete="off" value="1"<?php echo empty($settings['smtp_auth']) ? '' : ' checked'; ?>>
            <?php echo $this->text('Enabled'); ?>
          </label>
          <label class="btn btn-default<?php echo empty($settings['smtp_auth']) ? ' active' : ''; ?>">
            <input name="settings[smtp_auth]" type="radio" autocomplete="off" value="0"<?php echo empty($settings['smtp_auth']) ? ' checked' : ''; ?>>
            <?php echo $this->text('Disabled'); ?>
          </label>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label">
        <span class="hint" title="<?php echo $this->text('Prefix to the SMTP servier'); ?>">
          <?php echo $this->text('SMTP encryption'); ?>
        </span>
      </label>
      <div class="col-md-2">
        <select  name="settings[smtp_secure]" class="form-control">
          <option value="tls"<?php echo ($settings['smtp_secure'] == 'tls') ? ' selected' : ''; ?>>
          <?php echo $this->text('TLS'); ?>
          </option>
          <option value="ssl"<?php echo ($settings['smtp_secure'] == 'ssl') ? ' selected' : ''; ?>>
          <?php echo $this->text('SSL'); ?>
          </option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label">
        <span class="hint" title="<?php echo $this->text('Specify main (first) and backup SMTP servers, one per line'); ?>">
          <?php echo $this->text('SMTP hosts'); ?>
        </span>
      </label>
      <div class="col-md-4">
        <textarea name="settings[smtp_host]" class="form-control"><?php echo $this->escape($settings['smtp_host']); ?></textarea>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label">
        <span class="hint" title="<?php echo $this->text('SMTP login name'); ?>">
          <?php echo $this->text('SMTP user'); ?>
        </span>
      </label>
      <div class="col-md-4">
        <input name="settings[smtp_username]" class="form-control" value="<?php echo $this->escape($settings['smtp_username']); ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label">
        <span class="hint" title="<?php echo $this->text('Password for authorization on SMTP server'); ?>">
          <?php echo $this->text('SMTP password'); ?>
        </span>
      </label>
      <div class="col-md-4">
        <input name="settings[smtp_password]" type="password" class="form-control" value="<?php echo $this->escape($settings['smtp_password']); ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label">
        <span class="hint" title="<?php echo $this->text('Number of SMTP port'); ?>">
          <?php echo $this->text('SMTP port'); ?>
        </span>
      </label>
      <div class="col-md-4">
        <input name="settings[smtp_port]" class="form-control" value="<?php echo $this->escape($settings['smtp_port']); ?>">
      </div>
    </div>
  </fieldset>
  <fieldset class="misc">
    <legend class="misc"><?php echo $this->text('Miscellaneous'); ?></legend>
    <div class="form-group">
      <label class="col-md-2 control-label">
        <span class="hint" title="<?php echo $this->text('Cron key is used to run scheduled operations from outside of the site. Leave the field empty to generate a random string'); ?>">
          <?php echo $this->text('Cron key'); ?>
        </span>
      </label>
      <div class="col-md-4">
        <input name="settings[cron_key]" maxlength="255" class="form-control" value="<?php echo $settings['cron_key']; ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label">
        <span class="hint" title="<?php echo $this->text('You must disable error reporting on production site for security reason'); ?>">
          <?php echo $this->text('Display errors'); ?>
        </span>
      </label>
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
      </div>
    </div>
  </fieldset>
</form>
