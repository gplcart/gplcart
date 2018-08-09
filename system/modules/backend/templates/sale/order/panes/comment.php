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
<?php if (!empty($order['comment'])) { ?>
<div class="card">
  <div class="card-header"><?php echo $this->text('Order comments'); ?></div>
  <div class="card-body">
    <?php echo $this->e($order['comment']); ?>
  </div>
</div>
<?php } ?>