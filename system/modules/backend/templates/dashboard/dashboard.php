<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if($no_enabled_stores && $this->access('store_edit')) { ?>
<div class="alert alert-warning">
  <?php echo $this->text('All your stores are disabled and cannot be accessed by customers. You can enable them <a href="@url">here</a> by editing "Status" option', array('@url' => $this->url('admin/settings/store'))); ?>
</div>
<?php } ?>
<?php if(empty($intro)) { ?>
<?php if($this->access('dashboard_edit')) { ?>
<p>
  <a href="<?php echo $this->url('admin/settings/dashboard'); ?>"><?php echo $this->text('Customize dashboard'); ?></a>
</p>
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
