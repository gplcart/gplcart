<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<body class="install">
  <div class="container-fluid">
    <form method="post" class="form-horizontal">
      <div class="row">
        <div class="col-md-12">
          <h1 class="h4"><?php echo $this->text('Welcome to GPL Cart'); ?></h1>
          <?php if (!empty($_languages) && count($_languages) > 1) { ?>
          <div class="select-language clearfix">
            <span class="pull-left"><?php echo $this->text('Select a language'); ?>:&nbsp;&nbsp;</span>
            <ul class="list-inline languages pull-left">
              <?php foreach ($_languages as $code => $name) { ?>
              <li class="<?php echo $code === $language ? 'active' : ''; ?>">
                <a href="<?php echo $this->url('', array('lang' => $code)); ?>">
                  <?php echo isset($name[1]) ? $this->e($name[1]) : $this->e($name[0]); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
          </div>
          <?php } ?>
        </div>
      </div>
      <?php if (!empty($_messages)) { ?>
      <div class="row">
        <div class="col-md-12">
          <?php foreach ($_messages as $type => $strings) { ?>
          <div class="alert alert-<?php echo $this->e($type); ?> alert-dismissible fade in">
            <button type="button" class="close" data-dismiss="alert">
              <span aria-hidden="true">&times;</span>
            </button>
            <?php foreach ($strings as $string) { ?>
            <?php echo $this->filter($string); ?>
            <?php } ?>
          </div>
          <?php } ?>
        </div>
      </div>
      <?php } ?>
      <div class="row">
        <?php if ($severity !== 'danger') { ?>
        <div class="col-md-8">
          <div class="panel panel-default">
            <div class="panel-body">
              <?php if ($this->error('database.connect', true)) { ?>
              <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">
                  <span aria-hidden="true">&times;</span>
                </button>
                <?php echo $this->error('database.connect'); ?>
              </div>
              <?php } ?>
              <div class="required form-group<?php echo $this->error('database.name', ' has-error'); ?>">
                <label class="col-md-3 control-label"><?php echo $this->text('Database name'); ?></label>
                <div class="col-md-4">
                  <input name="settings[database][name]" class="form-control" value="<?php echo isset($settings['database']['name']) ? $this->e($settings['database']['name']) : ''; ?>">
                  <div class="help-block">
                    <?php echo $this->error('database.name'); ?>
                  </div>
                </div>
                <div class="col-md-5"><?php echo $this->text('A name of the database you want to connect to'); ?></div>
              </div>
              <div class="required form-group<?php echo $this->error('database.user', ' has-error'); ?>">
                <label class="col-md-3 control-label"><?php echo $this->text('Database user'); ?></label>
                <div class="col-md-4">
                  <input name="settings[database][user]" class="form-control" value="<?php echo isset($settings['database']['user']) ? $this->e($settings['database']['user']) : 'root'; ?>">
                  <div class="help-block">
                    <?php echo $this->error('database.user'); ?>
                  </div>
                </div>
                <div class="col-md-5">
                  <?php echo $this->text('An existing username to access the database'); ?>
                </div>
              </div>
              <div class="form-group<?php echo $this->error('database.password', ' has-error'); ?>">
                <label class="col-md-3 control-label"><?php echo $this->text('Database password'); ?></label>
                <div class="col-md-4">
                  <input type="password" name="settings[database][password]" class="form-control" value="<?php echo isset($settings['database']['password']) ? $this->e($settings['database']['password']) : ''; ?>">
                  <div class="help-block">
                    <?php echo $this->error('database.password'); ?>
                  </div>
                </div>
                <div class="col-md-5"><?php echo $this->text('A password to access the database. Can be empty'); ?></div>
              </div>
              <?php if (!$this->error(null, true)) { ?>
              <div class="form-group">
                <div class="col-md-5 col-md-offset-3">
                  <a href="#db-advanced" data-toggle="collapse"><?php echo $this->text('Advanced'); ?> <span class="caret"></span></a>
                </div>
              </div>
              <?php } ?>
              <div id="db-advanced" class="<?php echo $this->error(null, '', 'collapse'); ?>">
                <div class="form-group">
                  <label class="col-md-3 control-label"><?php echo $this->text('Database type'); ?></label>
                  <div class="col-md-4">
                    <select name="settings[database][type]" class="form-control">
                      <option value="mysql"<?php echo isset($settings['database']['type']) && $settings['database']['type'] === 'mysql' ? ' selected' : ''; ?>><?php echo $this->text('mysql'); ?></option>
                      <option value="sqlite"<?php echo isset($settings['database']['type']) && $settings['database']['type'] === 'sqlite' ? ' selected' : ''; ?>><?php echo $this->text('sqlite'); ?></option>
                    </select>
                  </div>
                </div>
                <div class="required form-group<?php echo $this->error('database.port', ' has-error'); ?>">
                  <label class="col-md-3 control-label"><?php echo $this->text('Database port'); ?></label>
                  <div class="col-md-4">
                    <input name="settings[database][port]" class="form-control" value="<?php echo isset($settings['database']['port']) ? $this->e($settings['database']['port']) : '3306'; ?>">
                    <div class="help-block"><?php echo $this->error('database.port'); ?></div>
                  </div>
                </div>
                <div class="required form-group<?php echo $this->error('database.host', ' has-error'); ?>">
                  <label class="col-md-3 control-label"><?php echo $this->text('Database host'); ?></label>
                  <div class="col-md-4">
                    <input name="settings[database][host]" class="form-control" value="<?php echo isset($settings['database']['host']) ? $this->e($settings['database']['host']) : 'localhost'; ?>">
                    <div class="help-block"><?php echo $this->error('database.host'); ?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="panel panel-default">
            <div class="panel-body">
              <div class="required form-group<?php echo $this->error('user.email', ' has-error'); ?>">
                <label class="col-md-3 control-label"><?php echo $this->text('E-mail'); ?></label>
                <div class="col-md-4">
                  <input name="settings[user][email]" class="form-control" value="<?php echo isset($settings['user']['email']) ? $this->e($settings['user']['email']) : ''; ?>">
                  <div class="help-block">
                    <?php echo $this->error('user.email'); ?>
                  </div>
                </div>
                <div class="col-md-5"><?php echo $this->text('An E-mail for superadmin'); ?></div>
              </div>
              <div class="required form-group<?php echo $this->error('user.password', ' has-error'); ?>">
                <label class="col-md-3 control-label"><?php echo $this->text('Password'); ?></label>
                <div class="col-md-4">
                  <input type="password" name="settings[user][password]" class="form-control" value="<?php echo isset($settings['user']['password']) ? $this->e($settings['user']['password']) : ''; ?>">
                  <div class="help-block">
                    <?php echo $this->error('user.password'); ?>
                  </div>
                </div>
                <div class="col-md-5"><?php echo $this->text('A password for superadmin'); ?></div>
              </div>
              <div class="required form-group<?php echo $this->error('store.title', ' has-error'); ?>">
                <label class="col-md-3 control-label"><?php echo $this->text('Store title'); ?></label>
                <div class="col-md-4">
                  <input name="settings[store][title]" class="form-control" value="<?php echo isset($settings['store']['title']) ? $this->e($settings['store']['title']) : 'GPL Cart'; ?>">
                  <div class="help-block">
                    <?php echo $this->error('store.title'); ?>
                  </div>
                </div>
                <div class="col-md-5"><?php echo $this->text('A name of the store'); ?></div>
              </div>
              <div class="form-group">
                <label class="col-md-3 control-label"><?php echo $this->text('Timezone'); ?></label>
                <div class="col-md-4">
                  <select name="settings[store][timezone]" class="form-control">
                    <?php foreach ($timezones as $value => $label) { ?>
                    <option value="<?php echo $this->e($value); ?>"<?php echo isset($settings['store']['timezone']) && $settings['store']['timezone'] == $value ? ' selected' : ''; ?>><?php echo $this->e($label); ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-md-5"><?php echo $this->text('Choose your local timezone'); ?></div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12 text-right">
              <button class="btn btn btn-default" name="install" value="1"><?php echo $this->text('Install'); ?></button>
            </div>
          </div>
        </div>
        <?php } ?>
        <div class="<?php echo $severity === 'danger' ? 'col-md-12' : 'col-md-4'; ?>">
          <div class="panel panel-default">
            <div class="panel-body">
              <table class="table-condensed requirements">
                <tbody>
                  <?php foreach ($requirements as $section => $items) { ?>
                  <?php foreach ($items as $name => $info) { ?>
                  <tr>
                    <td><?php echo $this->text($info['message']); ?></td>
                    <td>
                      <?php if ($info['status']) { ?>
                      <i class="fa fa-check-square-o"></i>
                      <?php } else { ?>
                      <i class="fa fa-square-o"></i>
                      <?php if ($info['severity'] === 'warning') { ?>
                      <i class="fa fa-exclamation-triangle" title="<?php echo $this->text('Non-critical issue'); ?>"></i>
                      <?php } else if ($info['severity'] === 'danger') { ?>
                      <i class="fa fa-exclamation-triangle" title="<?php echo $this->text('Critical issue'); ?>"></i>
                      <?php } else { ?>
                      <i class="fa fa-exclamation-triangle"></i>
                      <?php } ?>
                      <?php } ?>
                    </td>
                  </tr>
                <?php } ?>
                <?php } ?>
              </tbody>
            </table>
            </div>
          </div>
          <?php if ($severity === 'danger') { ?>
          <p><b><?php echo $this->text('Please fix all critical errors in your environment before continue'); ?></b></p>
          <?php } ?>
        </div>
      </div>
    </form>
  </div>
</body>

