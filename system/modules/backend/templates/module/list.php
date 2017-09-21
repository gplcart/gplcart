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
<?php if (!empty($modules) || $_filtering) { ?>
<?php if ($_filtering && empty($modules)) { ?>
<?php echo $this->text('No results'); ?>
<?php } ?>
<?php if (!empty($modules)) { ?>
<div class="table-responsive">
  <table class="table table-hover modules">
    <thead>
      <tr>
        <th><a href="<?php echo $sort_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_version; ?>"><?php echo $this->text('Version'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_type; ?>"><?php echo $this->text('Type'); ?> <i class="fa fa-sort"></i></a></th>
        <th><?php echo $this->text('Dependencies'); ?></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($modules as $module_id => $info) { ?>
      <tr class="module-<?php echo $module_id; ?><?php echo empty($info['errors']) ? '' : ' bg-danger'; ?>">
        <td class="middle">
          <?php echo $this->e($info['id']); ?>
        </td>
        <td>
          <div class="name">
            <a href="#" onclick="return false;" data-toggle="collapse" data-target="#module-details-<?php echo $module_id; ?>">
              <?php echo $this->truncate($this->e($info['name'])); ?>
            </a>
          </div>
        </td>
        <td class="middle">
          <?php echo empty($info['version']) ? $this->text('Unknown') : $this->e($info['version']); ?>
        </td>
        <td class="middle">
          <?php echo $this->text(ucfirst($info['type'])); ?>
        </td>
        <td>
          <?php if (empty($info['requires']) && empty($info['required_by'])) { ?>
          <?php echo $this->text('No'); ?>
          <?php } else { ?>
          <a data-toggle="collapse" href="#module-details-<?php echo $module_id; ?>">
            <?php echo $this->text('Yes'); ?>
          </a>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if($info['type'] !== 'installer') { ?>
          <ul class="list-inline">
            <?php if (isset($info['status'])) { ?>
            <?php if ($info['status']) { ?>
            <?php if ($this->access('module_disable') && empty($info['lock'])) { ?>
            <li>
              <a href="<?php echo $this->url(false, array('action' => 'disable', 'module_id' => $module_id, 'token' => $_token)); ?>">
                <span class="text-danger"><?php echo $this->lower($this->text('Disable')); ?></span>
              </a>
            </li>
            <?php } ?>
            <?php } else { ?>
            <?php if ($this->access('module_enable') && empty($info['lock'])) { ?>
            <li>
              <a href="<?php echo $this->url(false, array('action' => 'enable', 'module_id' => $module_id, 'token' => $_token)); ?>">
                  <?php echo $this->lower($this->text('Enable')); ?>
              </a>
            </li>
            <?php } ?>
            <?php if ($this->access('module_uninstall') && empty($info['lock'])) { ?>
            <li>
              <a onclick="return confirm('<?php echo $this->text('Are you sure? This action may permanently delete some data managed by this module'); ?>');" href="<?php echo $this->url(false, array('action' => 'uninstall', 'module_id' => $module_id, 'token' => $_token)); ?>">
                <?php echo $this->lower($this->text('Uninstall')); ?>
              </a>
            </li>
            <?php } ?>
            <?php } ?>
            <?php } else { ?>
            <?php if ($this->access('module_install')) { ?>
            <li>
              <a href="<?php echo $this->url(false, array('action' => 'install', 'module_id' => $module_id, 'token' => $_token)); ?>">
                <?php echo $this->lower($this->text('Install and enable')); ?>
              </a>
            </li>
            <?php } ?>
            <?php } ?>
            <?php if ($this->access('module_edit') && !empty($info['status']) && !empty($info['configure'])) { ?>
            <li>
              <a href="<?php echo $this->url($info['configure']); ?>">
                <?php echo $this->lower($this->text('Configure')); ?>
              </a>
            </li>
            <?php } ?>
          </ul>
          <?php } ?>
        </td>
      </tr>
      <tr class="collapse active" id="module-details-<?php echo $module_id; ?>">
        <td colspan="6">
          <?php if (!empty($info['author'])) { ?>
          <div class="author">
            <b><?php echo $this->text('Author'); ?></b>: <?php echo $this->e($this->truncate($info['author'], 100)); ?>
          </div>
          <?php } ?>
          <?php if (!empty($info['description'])) { ?>
          <div class="description">
            <b><?php echo $this->text('Description'); ?></b>: <?php echo $this->filter($this->truncate($info['description'], 100)); ?>
          </div>
          <?php } ?>
          <?php if (isset($info['weight'])) { ?>
          <div class="weight">
            <b><?php echo $this->text('Weight'); ?></b>: <?php echo $this->e($info['weight']); ?>
          </div>
          <?php } ?>
          <?php if (!empty($info['hooks'])) { ?>
          <div class="hooks">
            <b><?php echo $this->text('Implements hooks'); ?></b>: <?php echo $this->e($this->truncate(implode(', ', $info['hooks']), 100)); ?>
          </div>
          <?php } ?>
          <?php if (isset($info['php'])) { ?>
          <div class="weight">
            <b>PHP</b>: <?php echo $this->e($info['php']); ?>
          </div>
          <?php } ?>
          <?php if (!empty($info['requires'])) { ?>
          <div class="requires">
            <b><?php echo $this->text('Requires'); ?>:</b>
            <p>
              <?php foreach ($info['requires'] as $requires_id => $version) { ?>
              <?php if (isset($available_modules[$requires_id]['name'])) { ?>
              <span class="label label-default"><?php echo $this->text($available_modules[$requires_id]['name']); ?> <?php echo $this->e($version); ?></span>
              <?php } else { ?>
              <span class="label label-danger"><?php echo $this->e($requires_id); ?> (<?php echo $this->text('invalid'); ?>)</span>
              <?php } ?>
              <?php } ?>
            </p>
          </div>
          <?php } ?>
          <?php if (!empty($info['required_by'])) { ?>
          <div class="required-by">
            <b><?php echo $this->text('Required by'); ?>:</b>
            <p>
              <?php foreach ($info['required_by'] as $required_by_id => $version) { ?>
              <?php if (isset($available_modules[$required_by_id]['name'])) { ?>
              <span class="label label-default"><?php echo $this->text($available_modules[$required_by_id]['name']); ?> <?php echo $this->e($version); ?></span>
              <?php } else { ?>
              <span class="label label-danger"><?php echo $this->e($required_by_id); ?> (<?php echo $this->text('invalid'); ?>)</span>
              <?php } ?>
              <?php } ?>
            </p>
          </div>
          <?php } ?>
          <?php if (!empty($info['errors'])) { ?>
          <div class="errors">
            <b><?php echo $this->text('Error'); ?></b>:
            <ul class="list-unstyled">
              <?php foreach ($info['errors'] as $error) { ?>
              <li>
              <?php if(empty($error[1])) { ?>
              <?php echo $this->text($error[0]); ?>
              <?php } else { ?>
              <?php echo $this->text($error[0], $error[1]); ?>
              <?php } ?>
              </li>
              <?php } ?>
            </ul>
          </div>
          <?php } ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
<?php if (!empty($_pager)) { ?>
<?php echo $_pager; ?>
<?php } ?>
<?php } ?>
<?php } else { ?>
<div class="row">
  <div class="col-md-12"><?php echo $this->text('No modules'); ?></div>
</div>
<?php } ?>