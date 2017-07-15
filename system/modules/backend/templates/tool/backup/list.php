<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($backups) || $_filtering) { ?>
<div class="panel panel-default">
  <?php if ($this->access('backup_delete')) { ?>
  <div class="panel-heading clearfix">
    <div class="btn-group pull-left">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
         <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <li>
          <a data-action="delete" data-action-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>" href="#">
            <?php echo $this->text('Delete'); ?>
          </a>
        </li>
      </ul>
    </div>
  </div>
  <?php } ?>
  <div class="panel-body table-responsive">
    <table class="table table-condensed backups">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"></th>
          <th><a href="<?php echo $sort_backup_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_type; ?>"><?php echo $this->text('Type'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_version; ?>"><?php echo $this->text('Version'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_module_id; ?>"><?php echo $this->text('Module ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_user_id; ?>"><?php echo $this->text('User'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_created; ?>"><?php echo $this->text('Created'); ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($backups) && $_filtering) { ?>
        <tr>
          <td colspan="9">
            <?php echo $this->text('No results'); ?>
            <a class="clear-filter" href="#"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } else { ?>
        <?php foreach ($backups as $id => $backup) { ?>
        <tr>
          <td class="middle">
            <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id; ?>">
          </td>
          <td class="middle"><?php echo $this->e($id); ?></td>
          <td class="middle"><?php echo $this->e($backup['name']); ?></td>
          <td class="middle">
            <?php if (isset($handlers[$backup['type']]['name'])) { ?>
            <?php echo $this->e($handlers[$backup['type']]['name']); ?>
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (empty($backup['version'])) { ?>
            <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
            <?php } else { ?>
            <?php echo $this->e($backup['version']); ?>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (empty($backup['module_id'])) { ?>
            <span class="text-danger"><?php echo $this->text('None'); ?></span>
            <?php } else { ?>
            <?php echo $this->e($backup['module_id']); ?>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (empty($backup['user_name'])) { ?>
            <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
            <?php } else { ?>
            <?php echo $this->e($backup['user_name']); ?>
            <?php } ?>
          </td>
          <td class="middle">
            <?php echo $this->date($backup['created']); ?>
          </td>
          <td class="middle">
            <ul class="list-inline">
              <?php if($this->access('backup_download')) { ?>
              <a href="<?php echo $this->url('', array('download' => $id)); ?>">
                <?php echo $this->lower($this->text('Download')); ?>
              </a>
              <?php } ?>
            </ul>
          </td>
        </tr>
        <?php } ?>
        <?php } ?>
      </tbody>
    </table>
    <?php if(!empty($_pager)) { ?>
    <?php echo $_pager; ?>
    <?php } ?>
  </div>
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('There are no items yet'); ?>
  </div>
</div>
<?php } ?>

