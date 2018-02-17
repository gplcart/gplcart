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
<div class="table-responsive">
  <table class="table table-hover modules">
    <thead>
      <tr>
        <th><a href="<?php echo $sort_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_version; ?>"><?php echo $this->text('Version'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_type; ?>"><?php echo $this->text('Type'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_has_dependencies; ?>"><?php echo $this->text('Dependencies'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_created; ?>"><?php echo $this->text('Installed'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_modified; ?>"><?php echo $this->text('Updated'); ?> <i class="fa fa-sort"></i></a></th>
        <th></th>
      </tr>
      <tr class="filters active hidden-no-js">
        <th><input class="form-control" name="id" value="<?php echo $filter_id; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th><input class="form-control" name="name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th><input class="form-control" name="version" value="<?php echo $filter_version; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th>
          <select name="type" class="form-control">
            <option value=""><?php echo $this->text('Any'); ?></option>
            <?php foreach ($types as $type => $type_name) { ?>
            <option value="<?php echo $this->e($type); ?>"<?php echo $filter_type == $type ? ' selected' : '' ?>>
              <?php echo $this->e($type_name); ?>
            </option>
            <?php } ?>
          </select>
        </th>
        <th>
          <select class="form-control" name="has_dependencies">
            <option value=""><?php echo $this->text('Any'); ?></option>
            <option value="1"<?php echo $filter_has_dependencies === '1' ? ' selected' : ''; ?>>
              <?php echo $this->text('Yes'); ?>
            </option>
            <option value="0"<?php echo $filter_has_dependencies === '0' ? ' selected' : ''; ?>>
              <?php echo $this->text('No'); ?>
            </option>
          </select>
        </th>
        <th></th>
        <th></th>
        <th>
          <a href="<?php echo $this->url($_path); ?>" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
            <i class="fa fa-refresh"></i>
          </a>
          <button class="btn btn-default filter" title="<?php echo $this->text('Filter'); ?>">
            <i class="fa fa-search"></i>
          </button>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if ($_filtering && empty($modules)) { ?>
      <tr>
        <td colspan="8">
          <?php echo $this->text('No results'); ?>
          <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
        </td>
      </tr>
      <?php } ?>
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
          <?php echo $this->e($types[$info['type']]); ?>
        </td>
        <td>
          <?php if ($info['has_dependencies']) { ?>
          <a data-toggle="collapse" href="#module-details-<?php echo $module_id; ?>">
            <?php echo $this->text('Yes'); ?>
          </a>
          <?php } else { ?>
          <?php echo $this->text('No'); ?>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if(empty($info['created'])) { ?>
          --
          <?php } else { ?>
          <?php echo $this->date($info['created']); ?>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if(isset($info['modified']) && isset($info['created']) && $info['modified'] != $info['created']) { ?>
          <?php echo $this->date($info['modified']); ?>
          <?php } else { ?>
          --
          <?php } ?>
        </td>
        <td class="middle">
          <?php if($info['type'] !== 'installer') { ?>
          <ul class="list-inline">
            <?php if (isset($info['status'])) { ?>
            <?php if ($info['status']) { ?>
            <?php if ($this->access('module_disable') && empty($info['lock']) && empty($info['required_by'])) { ?>
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
        <td colspan="8">
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
            <?php foreach ($info['requires'] as $requires_id => $version) { ?>
            <?php if (isset($available_modules[$requires_id]['name'])) { ?>
            <?php echo $this->text($available_modules[$requires_id]['name']); ?> <?php echo $this->e($version); ?>
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->e($requires_id); ?> (<?php echo $this->text('invalid'); ?>)</span>
            <?php } ?>
            <?php } ?>
          </div>
          <?php } ?>
          <?php if (!empty($info['required_by'])) { ?>
          <div class="required-by">
            <b><?php echo $this->text('Required by'); ?>:</b>
            <?php foreach ($info['required_by'] as $required_by_id => $version) { ?>
            <?php if (isset($available_modules[$required_by_id]['name'])) { ?>
            <?php echo $this->text($available_modules[$required_by_id]['name']); ?> <?php echo $this->e($version); ?>
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->e($required_by_id); ?> (<?php echo $this->text('invalid'); ?>)</span>
            <?php } ?>
            <?php } ?>
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
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>
<?php } ?>
