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
<?php if(empty($intro)) { ?>
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
