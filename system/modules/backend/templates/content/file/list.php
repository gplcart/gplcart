<?php if (!empty($files) || $filtering) { ?>
<form method="post" id="files" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <?php if ($this->access('file_delete') && !empty($files)) { ?>
      <div class="btn-group pull-left">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <?php echo $this->text('With selected'); ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <li>
            <a data-action="delete" href="#">
              <?php echo $this->text('Delete from database and disk'); ?>
            </a>
          </li>
        </ul>
      </div>
      <?php } ?> 
    </div>
    <div class="panel-body table-responsive">
      <table class="table files">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all" value="1"></th>
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
          <?php if ($filtering && empty($files)) { ?>
          <tr>
            <td colspan="6">
              <?php echo $this->text('No results'); ?>
              <a class="clear-filter" href="#"><?php echo $this->text('Reset'); ?></a>
            </td>
          </tr>
          <?php } else { ?>
          <?php foreach ($files as $id => $file) { ?>
          <tr>
            <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id; ?>"></td>
            <td class="middle"><?php echo $this->escape($this->truncate($file['title'], 30)); ?></td>
            <td class="middle"><?php echo $this->escape($this->truncate($file['mime_type'])); ?></td>
            <td class="middle">
              <?php if (!empty($file['url'])) { ?>
              <?php echo $this->escape($this->truncate($file['path'])); ?>
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
                    <?php echo strtolower($this->text('Download')); ?>
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
    </div>
    <?php if (!empty($pager)) { ?>
    <div class="panel-footer text-right"><?php echo $pager; ?></div>
    <?php } ?>
  </div>
</form>
<?php } else { ?>
<div class="rows">
  <div class="col-md-12">
    <?php echo $this->text('You have no recorded files yet'); ?>
  </div>
</div>
<?php } ?>