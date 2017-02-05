<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($order['comment'])) { ?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Order comments'); ?></div>
  <div class="panel-body">
    <?php echo $this->escape($order['comment']); ?>
  </div>
</div>
<?php } ?>