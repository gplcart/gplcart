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
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Timezone'); ?></label>
    <div class="col-md-4">
      <select  name="settings[timezone]" class="form-control">
        <?php foreach ($timezones as $timezone_id => $timezone_name) { ?>
        <option value="<?php echo $timezone_id; ?>"<?php echo ($settings['timezone'] == $timezone_id) ? ' selected' : ''; ?>>
          <?php echo $this->e($timezone_name); ?>
        </option>
        <?php } ?>
      </select>
      <div class="help-block">
        <?php echo $this->text('Select your sitewide time zone. This setting can affect price rules and other important things'); ?>
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
      </div>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Track errors'); ?></label>
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
        <?php echo $this->text('Select which PHP errors should be tracked by the logging system'); ?>
      </div>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('CLI access'); ?></label>
    <div class="col-md-4">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo empty($settings['cli_status']) ? '' : ' active'; ?>">
          <input name="settings[cli_status]" type="radio" autocomplete="off" value="1"<?php echo empty($settings['cli_status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-default<?php echo empty($settings['cli_status']) ? ' active' : ''; ?>">
          <input name="settings[cli_status]" type="radio" autocomplete="off" value="0"<?php echo empty($settings['cli_status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <div class="help-block">
        <?php echo $this->text('If disabled, then all CLI commands made through standard entry point will be declined'); ?>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-6 col-md-offset-2">
      <div class="btn-toolbar">
        <button class="btn btn-default" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
      </div>
    </div>
  </div>
</form>
