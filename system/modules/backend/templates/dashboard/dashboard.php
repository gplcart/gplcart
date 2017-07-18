<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if(empty($intro)) { ?>
<?php if($this->access('dashboard_edit')) { ?>
<p class="clearfix">
  <a class="pull-right" href="<?php echo $this->url('admin/dashboard'); ?>"><?php echo $this->text('Customize dashboard'); ?></a>
<p>
<?php } ?>
<div class="row">
  <?php foreach ($dashboard as $panels) { ?>
  <div class="col-md-<?php echo 12 / $columns; ?>">
    <?php foreach ($panels as $panel) { ?>
    <?php echo $panel['rendered']; ?>
    <?php } ?>
  </div>
  <?php } ?>
</div>
<?php } else { ?>
<?php echo $intro; ?>
<?php } ?>
