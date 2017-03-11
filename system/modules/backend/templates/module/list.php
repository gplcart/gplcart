<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($modules) || $filtering) { ?>
<div class="panel panel-default">
  <?php if ($this->access('module_upload') || $this->access('marketplace')) { ?>
  <div class="panel-heading clearfix">
    <div class="btn-toolbar pull-right">
      <?php if ($this->access('module_upload') && $this->access('file_upload') && $this->access('module_install')) { ?>
      <a class="btn btn-default" href="<?php echo $this->url('admin/module/upload'); ?>">
        <?php echo $this->text('Upload'); ?>
      </a>
      <?php } ?>
      <?php if ($this->access('marketplace')) { ?>
      <a class="btn btn-default" href="<?php echo $this->url('admin/module/marketplace'); ?>">
        <?php echo $this->text('Marketplace'); ?>
      </a>
      <?php } ?>
    </div>
  </div>
  <?php } ?>
  <div class="panel-body table-responsive">
    <?php if ($filtering && empty($modules)) { ?>
    <?php echo $this->text('No results'); ?>
    <?php } ?>
    <?php if (!empty($modules)) { ?>
    <table class="table table-condensed modules">
      <thead>
        <tr>
          <th><a href="<?php echo $sort_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_version; ?>"><?php echo $this->text('Version'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_type; ?>"><?php echo $this->text('Type'); ?> <i class="fa fa-sort"></i></a></th>
          <th><?php echo $this->text('Dependencies'); ?></th>
          <th><?php echo $this->text('Status'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($modules as $module_id => $info) { ?>
        <tr class="module-<?php echo $module_id; ?><?php echo empty($info['errors']) ? '' : ' bg-danger'; ?>">
          <td class="middle">
            <?php echo $this->escape($info['id']); ?>
          </td>
          <td>
            <div class="name">
              <a href="#" onclick="return false;" data-toggle="collapse" data-target="#module-details-<?php echo $module_id; ?>">
                <?php echo $this->truncate($this->escape($info['name'])); ?>
              </a>
            </div>
          </td>
          <td class="middle">
            <?php echo $info['version'] ? $this->escape($info['version']) : $this->text('Unknown'); ?>
          </td>
          <td class="middle">
            <?php echo $this->escape($info['type_name']); ?>
          </td>
          <td>
            <?php if(empty($info['requires']) && empty($info['required_by'])) { ?>
            <?php echo $this->text('No'); ?>
            <?php } else { ?>
            <a data-toggle="collapse" href="#module-details-<?php echo $module_id; ?>">
              <?php echo $this->text('Yes'); ?>
            </a>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (isset($info['status'])) { ?>
            <?php if ($info['status']) { ?>
            <span class="text-success"><?php echo $this->text('Enabled'); ?></span>
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->text('Disabled'); ?></span>
            <?php } ?>
            <?php } else { ?>
            <span class="text-warning"><?php echo $this->text('Not installed'); ?></span>
            <?php } ?>
            <?php if(!empty($info['errors'])) { ?>
            <a data-toggle="collapse" href="#module-details-<?php echo $module_id; ?>">
              <?php echo $this->text('Error'); ?>
            </a>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if($info['type'] != 'installer') { ?>
            <ul class="list-inline">
              <?php if ($info['type'] === 'theme' && $this->access('editor')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/tool/editor/$module_id"); ?>">
                  <?php echo mb_strtolower($this->text('Edit')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if (isset($info['status'])) { ?>
              <?php if ($info['status']) { ?>
              <?php if ($this->access('module_disable') && empty($info['always_enabled'])) { ?>
              <li>
                <a href="<?php echo $this->url(false, array('action' => 'disable', 'module_id' => $module_id)); ?>">
                  <?php echo mb_strtolower($this->text('Disable')); ?>
                </a>
              </li>
              <?php } ?>
              <?php } else { ?>
              <?php if ($this->access('module_enable')) { ?>
              <li>
                <a href="<?php echo $this->url(false, array('action' => 'enable', 'module_id' => $module_id)); ?>">
                  <?php echo mb_strtolower($this->text('Enable')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('module_uninstall') && empty($info['always_enabled'])) { ?>
              <li>
                <a href="<?php echo $this->url(false, array('action' => 'uninstall', 'module_id' => $module_id)); ?>">
                  <?php echo mb_strtolower($this->text('Uninstall')); ?>
                </a>
              </li>
              <?php } ?>
              <?php } ?>
              <?php } else { ?>
              <?php if ($this->access('module_install')) { ?>
              <li>
                <a href="<?php echo $this->url(false, array('action' => 'install', 'module_id' => $module_id)); ?>">
                  <?php echo mb_strtolower($this->text('Install and enable')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if($this->access('module_delete')) { ?>
              <li>
                <a href="<?php echo $this->url(false, array('action' => 'delete', 'module_id' => $module_id)); ?>" onclick="return confirm(GplCart.text('Are you sure you want to remove this module from disk? It cannot be undone!'));">
                  <?php echo mb_strtolower($this->text('Delete')); ?>
                </a>
              </li>
              <?php } ?>
              <?php } ?>
              <?php if ($this->access('module_backup')) { ?>
              <li>
                <a href="<?php echo $this->url(false, array('action' => 'backup', 'module_id' => $module_id)); ?>">
                  <?php echo mb_strtolower($this->text('Backup')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if (isset($info['status']) && $info['configure'] && $this->access('module_edit')) { ?>
              <li>
                <a href="<?php echo $this->url($info['configure']); ?>">
                  <?php echo mb_strtolower($this->text('Configure')); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
            <?php } ?>
          </td>
        </tr>
        <tr class="collapse active" id="module-details-<?php echo $module_id; ?>">
          <td colspan="7">
            <?php if ($info['author']) { ?>
            <div class="author">
              <b><?php echo $this->text('Author'); ?></b>: <?php echo $this->escape($this->truncate($info['author'], 100)); ?>
            </div>
            <?php } ?>
            <?php if ($info['description']) { ?>
            <div class="description">
              <b><?php echo $this->text('Description'); ?></b>: <?php echo $this->xss($this->truncate($info['description'], 100)); ?>
            </div>
            <?php } ?>
            <?php if (isset($info['weight'])) { ?>
            <div class="weight">
              <b><?php echo $this->text('Weight'); ?></b>: <?php echo $this->escape($info['weight']); ?>
            </div>
            <?php } ?>
            <?php if (!empty($info['hooks'])) { ?>
            <div class="hooks">
              <b><?php echo $this->text('Implements hooks'); ?></b>: <?php echo $this->escape($this->truncate(implode(', ', $info['hooks']), 100)); ?>
            </div>
            <?php } ?>
            <?php if (!empty($info['requires'])) { ?>
            <div class="requires">
              <b><?php echo $this->text('Requires'); ?>:</b>
              <p>
                <?php foreach ($info['requires'] as $requires_id => $version) { ?>
                <?php if (isset($modules[$requires_id]['name'])) { ?>
                <span class="label label-default"><?php echo $this->text($modules[$requires_id]['name']); ?><?php echo $this->escape($version); ?></span>
                <?php } else { ?>
                <span class="label label-danger"><?php echo $this->escape($requires_id); ?> (<?php echo $this->text('invalid'); ?>)</span>
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
                <?php if (isset($modules[$required_by_id]['name'])) { ?>
                <span class="label label-default"><?php echo $this->text($modules[$required_by_id]['name']); ?></span>
                <?php } else { ?>
                <span class="label label-danger"><?php echo $this->escape($required_by_id); ?> (<?php echo $this->text('invalid'); ?>)</span>
                <?php } ?>
                <?php } ?>
              </p>
            </div>
            <?php } ?>
            <?php if (!empty($info['errors'])) { ?>
            <div class="errors">
              <b><?php echo $this->text('Error'); ?></b>:
              <ul class="list-unstyled">
              <?php foreach($info['errors'] as $error){ ?>
                <li><?php echo $this->text($error[0], $error[1]); ?></li>
              <?php } ?>
              </ul>
            </div>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <?php } ?>
    <?php if (!empty($pager)) { ?>
    <div class="panel-footer"><?php echo $pager; ?></div>
    <?php } ?>
  </div>
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12"><?php echo $this->text('No modules'); ?></div>
</div>
<?php } ?>