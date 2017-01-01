<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($modules)) { ?>
<div class="panel panel-default">
  <div class="panel-heading clearfix">
    <div class="btn-toolbar pull-right">
      <?php if ($this->access('module_upload')) { ?>
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
  <div class="panel-body table-responsive">
    <table class="table modules">
      <thead>
        <tr class="active">
          <th><?php echo $this->text('Name'); ?></th>
          <th><?php echo $this->text('Version'); ?></th>
          <th><?php echo $this->text('Core'); ?></th>
          <th><?php echo $this->text('Type'); ?></th>
          <th><?php echo $this->text('Status'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($modules as $module_id => $info) { ?>
        <tr>
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
            <?php echo $this->escape($info['core']); ?>
          </td>
          <td class="middle">
            <?php echo $this->escape($info['type_name']); ?>
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
          </td>
          <td class="middle">
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
                <a href="<?php echo $this->url(false, array('action' => 'disable', 'module_id' => $module_id, 'token' => $token)); ?>">
                  <?php echo mb_strtolower($this->text('Disable')); ?>
                </a>
              </li>
              <?php } ?>
              <?php } else { ?>
              <?php if ($this->access('module_enable')) { ?>
              <li>
                <a href="<?php echo $this->url(false, array('action' => 'enable', 'module_id' => $module_id, 'token' => $token)); ?>">
                  <?php echo mb_strtolower($this->text('Enable')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('module_uninstall') && empty($info['always_enabled'])) { ?>
              <li>
                <a href="<?php echo $this->url(false, array('action' => 'uninstall', 'module_id' => $module_id, 'token' => $token)); ?>">
                  <?php echo mb_strtolower($this->text('Uninstall')); ?>
                </a>
              </li>
              <?php } ?>
              <?php } ?>
              <?php } else { ?>
              <?php if ($this->access('module_install')) { ?>
              <li>
                <a href="<?php echo $this->url(false, array('action' => 'install', 'module_id' => $module_id, 'token' => $token)); ?>">
                  <?php echo mb_strtolower($this->text('Install and enable')); ?>
                </a>
              </li>
              <?php } ?>
              <li>
                <a href="<?php echo $this->url(false, array('action' => 'delete', 'module_id' => $module_id, 'token' => $token)); ?>" onclick="return confirm(GplCart.text('Are you sure you want to remove this module from disk? It cannot be undone!'));">
                  <?php echo mb_strtolower($this->text('Delete')); ?>
                </a>
              </li>
              <?php } ?>
              
              <?php if ($this->access('backup_add')) { ?>
              <li>
                <a href="<?php echo $this->url(false, array('action' => 'backup', 'module_id' => $module_id, 'token' => $token)); ?>">
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
          </td>
        </tr>
        <tr class="collapse active" id="module-details-<?php echo $module_id; ?>">
          <td colspan="7">
            <?php if ($info['author']) { ?>
            <b><?php echo $this->text('Author'); ?></b>: <?php echo $this->escape($info['author']); ?><br>
            <?php } ?>
            <?php if ($info['description']) { ?>
            <b><?php echo $this->text('Description'); ?></b>: <?php echo $this->xss($info['description']); ?><br>
            <?php } ?>
            <?php if ($info['dependencies']) { ?>
            <b><?php echo $this->text('Dependencies'); ?></b>: <?php echo $this->escape(implode(',', $info['dependencies'])); ?><br>
            <?php } ?>
            <?php if (isset($info['weight'])) { ?>
            <b><?php echo $this->text('Weight'); ?></b>: <?php echo $this->escape($info['weight']); ?>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12"><?php echo $this->text('No modules'); ?></div>
</div>
<?php } ?>