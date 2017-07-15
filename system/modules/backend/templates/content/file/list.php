<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($files) || $_filtering) { ?>
<form method="post" id="files" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <div class="btn-group pull-left">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
           <span class="caret"></span>
        </button>
        <?php $access_actions = false; ?>
        <?php if ($this->access('file_delete')) { ?>
        <?php $access_actions = true; ?>
        <ul class="dropdown-menu">
          <li>
            <a data-action="delete" data-action-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>" href="#">
              <?php echo $this->text('Delete from database and disk'); ?>
            </a>
          </li>
        </ul>
        <?php } ?>
      </div>
      <?php if ($this->access('file_add') && $this->access('file_upload')) { ?>
      <a class="btn btn-default pull-right" href="<?php echo $this->url('admin/content/file/add'); ?>">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
      <?php } ?>
    </div>
    <div class="panel-body table-responsive">
      <table class="table table-condensed files">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all" value="1"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
            <th>
              <a href="<?php echo $sort_file_id; ?>">
                <?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_title; ?>">
                <?php echo $this->text('Title'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_mime_type; ?>">
                <?php echo $this->text('MIME type'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_path; ?>">
                <?php echo $this->text('Path'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_created; ?>">
                <?php echo $this->text('Created'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th></th>
          </tr>
          <tr class="filters active">
            <th></th>
            <th></th>
            <th><input class="form-control" name="title" value="<?php echo $filter_title; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
            <th><input class="form-control" name="mime_type" value="<?php echo $filter_mime_type; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
            <th><input class="form-control" name="path" value="<?php echo $filter_path; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
            <th></th>
            <th>
              <button type="button" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
                <i class="fa fa-refresh"></i>
              </button>
              <button type="button" class="btn btn-default filter" title="<?php echo $this->text('Filter'); ?>">
                <i class="fa fa-search"></i>
              </button>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php if ($_filtering && empty($files)) { ?>
          <tr>
            <td colspan="6">
              <?php echo $this->text('No results'); ?>
              <a class="clear-filter" href="#"><?php echo $this->text('Reset'); ?></a>
            </td>
          </tr>
          <?php } else { ?>
          <?php foreach ($files as $id => $file) { ?>
          <tr>
            <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>></td>
            <td class="middle"><?php echo $id; ?></td>
            <td class="middle"><?php echo $this->e($this->truncate($file['title'], 30)); ?></td>
            <td class="middle"><?php echo $this->e($this->truncate($file['mime_type'])); ?></td>
            <td class="middle">
              <?php if (!empty($file['url'])) { ?>
              <?php echo $this->e($this->truncate($file['path'], 50)); ?>
              <?php } else { ?>
              <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
              <?php } ?>
            </td>
            <td class="middle"><?php echo $this->date($file['created']); ?></td>
            <td class="middle">
              <ul class="list-inline">
                <?php if (!empty($file['url'])) { ?>
                <li>
                  <a href="<?php echo $this->url('', array('download' => $id)); ?>">
                    <?php echo $this->lower($this->text('Download')); ?>
                  </a>
                </li>
                <?php } ?>
                <?php if ($this->access('file_edit')) { ?>
                <li>
                  <a href="<?php echo $this->url("admin/content/file/edit/$id"); ?>">
                    <?php echo $this->lower($this->text('Edit')); ?>
                  </a>
                </li>
                <?php } ?>
              </ul>
            </td>
          </tr>
          <?php } ?>
          <?php } ?>
        </tbody>
      </table>
      <?php echo $_pager; ?>
    </div>
  </div>
</form>
<?php } else { ?>
<div class="rows">
  <div class="col-md-12">
    <?php echo $this->text('There are no items yet'); ?>
    <?php if ($this->access('file_add') && $this->access('file_upload')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/content/file/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>