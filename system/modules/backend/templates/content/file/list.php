<?php if ($files || $filtering) {
    ?>
<form method="post" id="files" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token;
    ?>">
  <div class="row">
    <div class="col-md-6">
      <?php if ($this->access('file_delete') && $files) {
    ?>
      <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <?php echo $this->text('With selected');
    ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <li>
            <a data-action="delete" href="#">
            <?php echo $this->text('Delete only from database');
    ?>
            </a>
          </li>
          <li>
            <a data-action="delete_both" href="#">
            <?php echo $this->text('Delete both from database and disk');
    ?>
            </a>
          </li>
        </ul>
      </div>
      <?php 
}
    ?>
    </div>
    <div class="col-md-6 text-right">
      <?php if ($this->access('file_add')) {
    ?>
      <a class="btn btn-success" href="<?php echo $this->url('admin/content/file/add');
    ?>">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add');
    ?>
      </a>
      <?php 
}
    ?>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <table class="table table-responsive margin-top-20 files">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all" value="1"></th>
            <th>
              <a href="<?php echo $sort_title;
    ?>">
                <?php echo $this->text('Title');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_mime_type;
    ?>">
                <?php echo $this->text('MIME type');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_path;
    ?>">
                <?php echo $this->text('Path');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_created;
    ?>">
                <?php echo $this->text('Created');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th></th>
          </tr>
          <tr class="filters active">
            <th></th>
            <th><input class="form-control" name="title" value="<?php echo $filter_title;
    ?>" placeholder="<?php echo $this->text('Any');
    ?>"></th>
            <th><input class="form-control" name="mime_type" value="<?php echo $filter_mime_type;
    ?>" placeholder="<?php echo $this->text('Any');
    ?>"></th>
            <th><input class="form-control" name="path" value="<?php echo $filter_path;
    ?>" placeholder="<?php echo $this->text('Any');
    ?>"></th>
            <th></th>
            <th>
              <button type="button" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter');
    ?>">
                <i class="fa fa-refresh"></i>
              </button>
              <button type="button" class="btn btn-default filter" title="<?php echo $this->text('Filter');
    ?>">
                <i class="fa fa-search"></i>
              </button>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php if ($filtering && !$files) {
    ?>
          <tr>
            <td colspan="6"><?php echo $this->text('No results');
    ?></td>
          </tr>
          <?php 
}
    ?>
          <?php foreach ($files as $id => $file) {
    ?>
          <tr>
            <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id;
    ?>"></td>
            <td class="middle"><?php echo $this->escape($this->truncate($file['title'], 30));
    ?></td>
            <td class="middle"><?php echo $this->escape($this->truncate($file['mime_type']));
    ?></td>
            <td class="middle">
              <?php if (!empty($file['url'])) {
    ?>
              <a href="<?php echo $this->escape($file['url']);
    ?>" target="_blank">
              <?php echo $this->escape($this->truncate($file['path']));
    ?>
              </a>
              <?php 
} else {
    ?>
              <span class="text-danger"><?php echo $this->text('Missing');
    ?></span>
              <?php 
}
    ?>
            </td>
            <td class="middle"><?php echo $this->date($file['created']);
    ?></td>
            <td class="middle">
              <?php if ($this->access('file_edit')) {
    ?>
                <a class="btn btn-default" title="<?php echo $this->text('Edit');
    ?>" href="<?php echo $this->url("admin/content/file/edit/$id");
    ?>">
                  <i class="fa fa-edit"></i>
                </a>
              <?php 
}
    ?>
            </td>
          </tr>
          <?php 
}
    ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
        <?php echo $pager;
    ?>
    </div>
  </div>
</form>
<?php 
} else {
    ?>
<div class="rows">
  <div class="col-md-12">
    <?php echo $this->text('You have no recorded files yet');
    ?>
    <?php if ($this->access('file_add')) {
    ?>
    <a href="<?php echo $this->url('admin/content/file/add');
    ?>"><?php echo $this->text('Add');
    ?></a>
    <?php 
}
    ?>
  </div>
</div>
<?php 
} ?>