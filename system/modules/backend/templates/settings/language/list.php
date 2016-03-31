<?php if ($this->access('language_add')) {
    ?>
<div class="row">
  <div class="col-md-6 col-md-offset-6 text-right">
    <div class="btn-toolbar">
      <a class="btn btn-success add" href="<?php echo $this->url("admin/settings/language/add");
    ?>">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add');
    ?>
      </a>
    </div>
  </div>
</div>
<?php 
} ?>
<div class="row">
  <div class="col-md-12">
    <table class="table table-responsive margin-top-20 languages">
      <thead>
        <tr>
          <th><?php echo $this->text('Name'); ?></th>
          <th><?php echo $this->text('Native name'); ?></th>
          <th><?php echo $this->text('Code'); ?></th>
          <th><?php echo $this->text('Enabled'); ?></th>
          <th><?php echo $this->text('Weight'); ?></th>
          <th><?php echo $this->text('Action'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($languages as $code => $language) {
    ?>
        <tr data-code="<?php echo $code;
    ?>">
          <td class="middle">
            <?php echo $this->escape($language['name']);
    ?>
            <?php if (!empty($language['default'])) {
    ?>
            (<?php echo mb_strtolower($this->text('Default'));
    ?>)
            <?php 
}
    ?>
          </td>
          </td>
          <td class="middle"><?php echo $this->escape($language['native_name']);
    ?></td>
          <td class="middle"><?php echo $this->escape($code);
    ?></td>
          <td class="middle">
            <?php if (empty($language['status'])) {
    ?>
            <i class="fa fa-square-o"></i>
            <?php 
} else {
    ?>
            <i class="fa fa-check-square-o"></i>
            <?php 
}
    ?>
          </td>
          <td class="middle"><?php echo $this->escape($language['weight']);
    ?></td>
          <td class="middle">
            <?php if ($this->access('language_edit')) {
    ?>
            <a title="<?php echo $this->text('Edit');
    ?>" href="<?php echo $this->url("admin/settings/language/edit/$code");
    ?>" class="btn btn-default edit">
              <i class="fa fa-edit"></i>
            </a>
            <a title="<?php echo $this->text('Refresh translations');
    ?>" href="<?php echo $this->url(false, array('refresh' => $code));
    ?>" class="btn btn-default refresh">
              <i class="fa fa-refresh"></i>
            </a>
            <?php 
}
    ?>
          </td>
        </tr>
        <?php 
} ?>
      </tbody>
    </table>
  </div>
</div>