<?php if ($this->access('image_style_add')) { ?>
<div class="row">
  <div class="col-md-6 col-md-offset-6 text-right">
    <div class="btn-toolbar">
      <a href="<?php echo $this->url("admin/settings/imagestyle/add"); ?>" class="btn btn-success add">
      <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
    </div>
  </div>
</div>
<?php } ?>
<div class="row">
  <div class="col-md-12">
    <table class="table table-responsive margin-top-20 image-styles">
      <thead>
        <tr>
          <th><?php echo $this->text('ID'); ?></th>
          <th><?php echo $this->text('Name'); ?></th>
          <th><?php echo $this->text('Source'); ?></th>
          <th><?php echo $this->text('Enabled'); ?></th>
          <th><?php echo $this->text('Action'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($styles as $id => $style) { ?>
        <tr>
          <td class="middle"><?php echo $id; ?></td>
          <td class="middle"><?php echo $this->escape($style['name']); ?></td>
          <td class="middle"><?php echo empty($style['in_code']) ? $this->text('In database') : $this->text('In code'); ?></td>
          <td class="middle"><?php echo empty($style['status']) ? '<i class="fa fa-square-o"></i>' : '<i class="fa fa-check-square-o"></i>'; ?></td>
          <td class="col-md-2 middle">
            <?php if ($this->access('image_style_edit')) { ?>
            <a title="<?php echo $this->text('Edit'); ?>" href="<?php echo $this->url("admin/settings/imagestyle/edit/$id"); ?>" class="btn btn-default edit"><i class="fa fa-edit"></i></a>
            <?php } ?>
            <a title="<?php echo $this->text('Clear cache'); ?>" href="<?php echo $this->url(false, array('clear' => $id)); ?>" class="btn btn-default clear"><i class="fa fa-refresh"></i></a>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>