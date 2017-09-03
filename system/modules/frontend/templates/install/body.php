<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\frontend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<body class="install">
  <div class="container">
    <h2><?php echo $this->e($_page_title); ?> <small>v. <?php echo gplcart_version(); ?></small></h2>
    <form method="post">
      <?php if (!empty($languages) && count($languages) > 1) { ?>
      <div class="select-language clearfix">
        <?php echo $this->text('Select a language'); ?>:
        <?php foreach ($languages as $code => $data) { ?>
        <?php $language_query = ($code === 'en') ? array() : array('lang' => $code); ?>
        <a class="<?php echo $code === $language ? ' active' : ''; ?>" href="<?php echo $this->url('', $language_query); ?>">
          <?php echo $this->e($data['native_name']); ?>
        </a>
        <?php } ?>
      </div>
      <?php } ?>
      <?php if (!empty($_messages)) { ?>
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
      <?php } ?>
      <?php if ($severity === 'danger' || $severity === 'warning') { ?>
      <div class="alert alert-<?php echo $severity; ?> alert-dismissible fade in">
        <?php echo $this->text('There are some issues in your environment:'); ?>
        <ol>
          <?php foreach ($requirements as $section => $items) { ?>
          <?php foreach ($items as $info) { ?>
          <?php if (empty($info['status'])) { ?>
          <li>
            <?php echo $this->text($info['message']); ?> - <?php echo $this->text('No'); ?>
            <?php if ($info['severity'] === 'warning') { ?>
            (<?php echo $this->text('non-critical issue'); ?>)
            <?php } else if ($info['severity'] === 'danger') { ?>
            (<?php echo $this->text('critical issue'); ?>)
            <?php } else { ?>
            (<?php echo $this->text('error'); ?>)
            <?php } ?>
          </li>
          <?php } ?>
          <?php } ?>
          <?php } ?>
        </ol>
      </div>
      <?php } ?>
      <?php if ($this->error('database.connect', true)) { ?>
      <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">
          <span aria-hidden="true">&times;</span>
        </button>
        <?php echo $this->error('database.connect'); ?>
      </div>
      <?php } ?>
      <?php if ($severity === 'danger') { ?>
      <p><?php echo $this->text('Please fix all critical errors in your environment before continue'); ?></p>
      <?php } else { ?>
      <?php if(count($handlers) > 1) { ?>
      <table class="table table-striped">
        <caption><?php echo $this->text('Installation profiles'); ?></caption>
        <tbody>
          <tr class="required<?php echo $this->error('installer', ' has-error'); ?>">
            <td class="middle col-md-6">
              <div class="name"><?php echo $this->text('Installer'); ?></div>
              <div class="text-muted description"><?php echo $this->text('This distribution can be installed in several ways. Select an installer that fits your needs'); ?></div>
            </td>
            <td class="middle col-md-6">
              <?php foreach ($handlers as $id => $handler) { ?>
              <div class="radio">
                <label>
                  <input type="radio" name="settings[installer]" value="<?php echo $this->e($id); ?>"<?php echo isset($settings['installer']) && $settings['installer'] == $id ? ' checked' : ''; ?>>
                  <?php echo $this->e($handler['title']); ?>
                </label>
                <?php if(!empty($handler['description'])) { ?>
                <div class="help-block"><?php echo $this->filter($handler['description']); ?></div>
                <?php } ?>
              </div>
              <?php } ?>
              <?php echo $this->error('installer'); ?>
            </td>
          </tr>
        </tbody>
      </table>
      <?php } ?>
      <table class="table table-striped">
        <caption><?php echo $this->text('Database'); ?></caption>
        <tbody>
          <tr class="required<?php echo $this->error('database.name', ' has-error'); ?>">
            <td class="col-md-6">
              <div class="name"><?php echo $this->text('Database name'); ?></div>
              <div class="text-muted description"><?php echo $this->text('A name of the database you want to connect to'); ?></div>
            </td>
            <td class="middle col-md-6">
              <input name="settings[database][name]" class="form-control" value="<?php echo isset($settings['database']['name']) ? $this->e($settings['database']['name']) : ''; ?>">
              <?php echo $this->error('database.name'); ?>
            </td>
          </tr>
          <tr class="required<?php echo $this->error('database.user', ' has-error'); ?>">
            <td>
              <div class="name"><?php echo $this->text('Database user'); ?></div>
              <div class="text-muted description">
                <?php echo $this->text('An existing username to access the database'); ?>
              </div>
            </td>
            <td>
              <input name="settings[database][user]" class="form-control" value="<?php echo isset($settings['database']['user']) ? $this->e($settings['database']['user']) : 'root'; ?>">
              <?php echo $this->error('database.user'); ?>
            </td>
          </tr>
          <tr class="<?php echo $this->error('database.password', ' has-error'); ?>">
            <td>
              <div class="name"><?php echo $this->text('Database password'); ?></div>
              <div class="text-muted description">
                <?php echo $this->text('A password to access the database. Can be empty'); ?>
              </div>
            </td>
            <td>
              <input type="password" name="settings[database][password]" class="form-control" value="<?php echo isset($settings['database']['password']) ? $this->e($settings['database']['password']) : ''; ?>">
              <?php echo $this->error('database.password'); ?>
            </td>
          </tr>
        </tbody>
      </table>
      <table class="table user table-striped">
        <caption><?php echo $this->text('Site'); ?></caption>
        <tbody>
          <tr class="required<?php echo $this->error('user.email', ' has-error'); ?>">
            <td class="col-md-6">
              <div class="name"><?php echo $this->text('E-mail'); ?></div>
              <div class="text-muted description"><?php echo $this->text('An E-mail for superadmin'); ?></div>
            </td>
            <td class="col-md-6">
              <input name="settings[user][email]" class="form-control" value="<?php echo isset($settings['user']['email']) ? $this->e($settings['user']['email']) : ''; ?>">
              <?php echo $this->error('user.email'); ?>
            </td>
          </tr>
          <tr class="required<?php echo $this->error('user.password', ' has-error'); ?>">
            <td>
              <div class="name"><?php echo $this->text('Password'); ?></div>
              <div class="text-muted description"><?php echo $this->text('A password for superadmin'); ?></div>
            </td>
            <td>
              <input type="password" name="settings[user][password]" class="form-control" value="<?php echo isset($settings['user']['password']) ? $this->e($settings['user']['password']) : ''; ?>">
              <?php echo $this->error('user.password'); ?>
            </td>
          </tr>
          <tr class="required<?php echo $this->error('store.title', ' has-error'); ?>">
            <td>
              <div class="name"><?php echo $this->text('Store title'); ?></div>
              <div class="text-muted description"><?php echo $this->text('A name of the store'); ?></div>
            </td>
            <td>
              <input name="settings[store][title]" class="form-control" value="<?php echo isset($settings['store']['title']) ? $this->e($settings['store']['title']) : 'GPL Cart'; ?>">
              <?php echo $this->error('store.title'); ?>
            </td>
          </tr>
          <tr>
            <td>
              <div class="name"><?php echo $this->text('Timezone'); ?></div>
              <div class="text-muted description"><?php echo $this->text('Choose your local timezone'); ?></div>
            </td>
            <td>
              <select name="settings[store][timezone]" class="form-control">
                <?php foreach ($timezones as $value => $label) { ?>
                <option value="<?php echo $this->e($value); ?>"<?php echo isset($settings['store']['timezone']) && $settings['store']['timezone'] == $value ? ' selected' : ''; ?>><?php echo $this->e($label); ?></option>
                <?php } ?>
              </select>
              <?php echo $this->error('store.timezone'); ?>
            </td>
          </tr>
        </tbody>
      </table>
      <p><a href="#advanced-db" data-toggle="collapse"><?php echo $this->text('Advanced database settings'); ?> <span class="caret"></span></a></p>
      <div id="advanced-db" class="<?php echo $this->error(null, '', 'collapse'); ?>">
        <table class="table advanced-db table-striped">
          <tbody>
            <tr class="<?php echo $this->error('database.type', ' has-error'); ?>">
              <td class="col-md-6">
                <div class="name"><?php echo $this->text('Database type'); ?></div>
                <div class="text-muted description"><?php echo $this->text('Select your database type from the list of supported PDO drivers'); ?></div>
              </td>
              <td class="col-md-6">
                <select name="settings[database][type]" class="form-control">
                  <option value="mysql"<?php echo isset($settings['database']['type']) && $settings['database']['type'] === 'mysql' ? ' selected' : ''; ?>><?php echo $this->text('mysql'); ?></option>
                  <option value="sqlite"<?php echo isset($settings['database']['type']) && $settings['database']['type'] === 'sqlite' ? ' selected' : ''; ?>><?php echo $this->text('sqlite'); ?></option>
                </select>
                <?php echo $this->error('database.type'); ?>
              </td>
            </tr>
            <tr class="required<?php echo $this->error('database.port', ' has-error'); ?>">
              <td>
                <div class="name"><?php echo $this->text('Database port'); ?></div>
                <div class="text-muted description"><?php echo $this->text('The port number to use for the connection. The default port number is 3306'); ?></div>
              </td>
              <td>
                <input name="settings[database][port]" class="form-control" value="<?php echo isset($settings['database']['port']) ? $this->e($settings['database']['port']) : '3306'; ?>">
                <?php echo $this->error('database.port'); ?>
              </td>
            </tr>
            <tr class="required<?php echo $this->error('database.host', ' has-error'); ?>">
              <td>
                <div class="name"><?php echo $this->text('Database host'); ?></div>
                <div class="text-muted description"><?php echo $this->text('The default host name is localhost. If your database should be connected remotely specify here its IP or domain'); ?></div>
              </td>
              <td>
                <input name="settings[database][host]" class="form-control" value="<?php echo isset($settings['database']['host']) ? $this->e($settings['database']['host']) : 'localhost'; ?>">
                <?php echo $this->error('database.host'); ?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="actions">
        <button class="btn btn-default" name="install" value="1"><?php echo $this->text('Install'); ?></button>
      </div>
      <?php } ?>
    </form>
  </div>
</body>

