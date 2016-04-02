<?php if ($bookmark_list || $filtering) {
    ?>
<form method="post" id="bookmarks">
  <input type="hidden" name="token" value="<?php echo $token;
    ?>">
  <div class="row">
    <div class="col-md-6">
      <?php if ($this->access('bookmark_delete') && $bookmark_list) {
    ?>
      <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <?php echo $this->text('With selected');
    ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <li>
            <a data-action="delete" href="#">
                <?php echo $this->text('Delete');
    ?>
            </a>
          </li>
        </ul>
      </div>
      <?php 
}
    ?>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <table class="table margin-top-20 bookmarks">
        <thead>
          <tr>
            <th>
              <input type="checkbox" id="select-all" value="1">
            </th>
            <th>
              <a href="<?php echo $sort_title;
    ?>">
                <?php echo $this->text('Title');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_user_id;
    ?>">
                <?php echo $this->text('Author');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_id_value;
    ?>">
                <?php echo $this->text('Type');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_id_value;
    ?>">
                <?php echo $this->text('Value');
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
            <th>
              <input class="form-control" name="title" value="<?php echo $filter_title;
    ?>" placeholder="<?php echo $this->text('Any');
    ?>">
              <input type="hidden" name="id_value" value="<?php echo $filter_id_value;
    ?>">
            </th>
            <th>
              <input class="form-control" name="user" value="<?php echo $user;
    ?>" placeholder="<?php echo $this->text('Any');
    ?>">
              <input type="hidden" name="user_id" value="<?php echo $filter_user_id;
    ?>">
            </th>
            <th>
              <select name="type" class="form-control">
                <option value="any"><?php echo $this->text('Any');
    ?></option>
                <option value="url"<?php echo ($filter_type == 'url') ? ' selected' : '' ?>>
                  <?php echo $this->text('Url');
    ?>
                </option>
                <option value="product"<?php echo ($filter_type == 'product') ? ' selected' : '' ?>>
                  <?php echo $this->text('Product');
    ?>
                </option>
              </select>
            </th>
            <th></th>
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
          <?php if ($filtering && !$bookmark_list) {
    ?>
          <tr>
            <td colspan="6"><?php echo $this->text('No results');
    ?></td>
          </tr>
          <?php 
}
    ?>
          <?php foreach ($bookmark_list as $id => $bookmark) {
    ?>
          <tr>
            <td class="middle">
              <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id;
    ?>">
            </td>
            <td class="middle">
              <?php if ($bookmark['title']) {
    ?>
              <a target="_blank" href="<?php echo $this->escape($bookmark['url']);
    ?>">
                <?php echo $this->escape($bookmark['title']);
    ?>
              </a>
              <?php 
} else {
    ?>
              --
              <?php 
}
    ?>
            </td>
            <td class="middle">
              <?php echo $bookmark['email'];
    ?>
            </td>
            <td class="middle">
              <?php echo empty($bookmark['id_key']) ? $this->text('Url') : $this->text('Product');
    ?>
            </td>
            <td class="middle">
              <?php if (empty($bookmark['id_value'])) {
    ?>
              --
              <?php 
} else {
    ?>
              <a target="_blank" href="<?php echo $this->escape($bookmark['url']);
    ?>">
                <?php echo $this->escape($bookmark['id_value']);
    ?>
              </a>
              <?php 
}
    ?>
            </td>
            <td class="middle">
              <?php echo $this->date($bookmark['created']);
    ?>
            </td>
            <td></td>
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
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no bookmarks yet');
    ?>
  </div>
</div>
<?php 
} ?>
