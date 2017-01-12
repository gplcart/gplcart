<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
    <?php if ($this->access('image_style_add')) { ?>
      <div class="panel-heading clearfix">
        <div class="btn-toolbar pull-right">
          <a href="<?php echo $this->url("admin/settings/imagestyle/add"); ?>" class="btn btn-default add">
            <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
          </a>
        </div>
      </div>
  <?php } ?>
  <div class="panel-body table-responsive">
    <table class="table image-styles">
      <thead>
        <tr>
          <th><?php echo $this->text('ID'); ?></th>
          <th><?php echo $this->text('Name'); ?></th>
          <th><?php echo $this->text('Default'); ?></th>
          <th><?php echo $this->text('Enabled'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($styles as $id => $style) { ?>
        <tr>
          <td class="middle"><?php echo $id; ?></td>
          <td class="middle"><?php echo $this->escape($style['name']); ?></td>
          <td class="middle">
            <?php if (empty($style['default'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (empty($style['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="col-md-2 middle">
            <ul class="list-inline">
              <li>
                <a href="<?php echo $this->url('', array('clear' => $id)); ?>" class="clear">
                  <?php echo $this->text('clear cache'); ?>
                </a>
              </li>
              <?php if ($this->access('image_style_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/settings/imagestyle/edit/$id"); ?>" class="edit">
                  <?php echo mb_strtolower($this->text('Edit')); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>